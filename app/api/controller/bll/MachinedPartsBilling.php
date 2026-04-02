<?php

namespace app\api\controller\bll;

use think\facade\Db;
use app\api\service\BaseToken;

class MachinedPartsBilling extends BaseController
{
    /**
     * 获取加工件订单列表
     */
    public function getlist()
    {
        $page = max(1, (int) input('get.page/d', 1));
        $limit = max(1, min(200, (int) input('get.limit/d', 100)));
        $offset = ($page - 1) * $limit;

        // 查询条件
        $billingNo = input('get.machined_parts_billing_no/s', '');
        $customerId = input('get.customer_id/s', '');
        $customerName = input('get.customer_name/s', '');
        $customerPhone = input('get.customer_phone/s', '');
        $status = input('get.status/s', '');
        $type = input('get.machined_parts_type/s', '');
        $billingUserName = input('get.parts_billing_user_name/s', '');
        $startDate = input('get.start_date/s', '');
        $endDate = input('get.end_date/s', '');

        $where = [
            ['m.del_flag', '=', 0]
        ];

        $needJoinCustomer = false;

        if ($billingNo !== '') {
            $where[] = ['m.machined_parts_billing_no', 'like', '%' . $billingNo . '%'];
        }
        if ($customerId !== '') {
            $where[] = ['m.customer_id', '=', $customerId];
        }
        if ($customerName !== '') {
            $needJoinCustomer = true;
            // $where[] = ['c.name', 'like', $customerName . '%'];
            $where[] = ['c.name', '=', $customerName];
        }
        $needMaskPhone = true; // 默认需要脱敏
        if ($customerPhone !== '') {
            $needMaskPhone = false; // 搜索时不脱敏
            $needJoinCustomer = true;
            $where[] = ['c.main_phone', '=', $customerPhone];
        }
        if ($status !== '') {
            $where[] = ['m.status', '=', $status];
        }
        if ($type !== '') {
            $where[] = ['m.machined_parts_type', '=', $type];
        }
        if ($billingUserName !== '') {
            $where[] = ['m.parts_billing_user_name', 'like', '%' . $billingUserName . '%'];
        }
        if ($startDate !== '') {
            $where[] = ['m.create_date', '>=', $startDate];
        }
        if ($endDate !== '') {
            $where[] = ['m.create_date', '<=', $endDate . ' 23:59:59'];
        }

        // 缓存键
        $cacheKey = 'machined_parts_billing_count_' . md5(json_encode($where) . ($needJoinCustomer ? '1' : '0'));

        // 总数
        $total = cache($cacheKey) ?: null;
        if ($total === null) {
            $countQuery = Db::name('machined_parts_billing')->alias('m');
            if ($needJoinCustomer) {
                $countQuery->leftJoin('customer_detail_view c', 'm.customer_id = c.id');
            }
            if (!empty($where)) {
                $countQuery->where($where);
            }
            $total = $countQuery->count('m.id');
            cache($cacheKey, $total, 60);
        }

        // 列表 - 始终关联customer表以获取客户手机号
        $listQuery = Db::name('machined_parts_billing')->alias('m')
            ->leftJoin('customer_detail_view c', 'm.customer_id = c.id')
            ->leftJoin('sys_user u', 'm.parts_billing_user_id = u.id')
            ->leftJoin('machined_parts mp', 'm.machined_parts_id = mp.id')
            ->leftJoin('machined_manufacturer mf', 'mp.manufacturer_id = mf.id');
        
        if (!empty($where)) {
            $listQuery->where($where);
        }

        $list = $listQuery
            ->field('m.id, m.machined_parts_billing_no, m.customer_id, m.parts_billing_user_id, m.parts_billing_user_name, 
                m.parts_billing_user_phone, m.dept_id, m.wish_return_time, m.urgent_flag, m.on_time_flag, 
                m.billing_id, m.deposit_billing_id, m.remark, m.status, m.machined_parts_id, m.rework_flag, 
                m.tooth_position_json, m.tooth_color_json, m.num, m.single_price, m.total_price, 
                m.residue_num, m.rework_reason_id, m.rework_reason_detail, m.receiver_user_id, 
                m.receiver_user_name, m.file, m.mail_date, m.machined_parts_type, m.dental_implant_id, 
                m.movable_parts_id, m.doctor_id, m.doctor_name, m.create_date, m.update_date,
                c.name as customer_name, c.main_phone as customer_phone, 
                mp.name as machined_parts_name, mp.manufacturer_id,
                mf.name as manufacturer_name, u.name as billing_user_name')
            ->order('m.create_date', 'desc')
            ->limit($offset, $limit)
            ->select();

        // 对手机号进行脱敏处理：中间4位显示为****
        if (!empty($list) && $needMaskPhone) {
            $list = $list->toArray();
            foreach ($list as &$row) {
                if (!empty($row['customer_phone']) && strlen($row['customer_phone']) == 11) {
                    $row['customer_phone'] = substr($row['customer_phone'], 0, 3) . '****' . substr($row['customer_phone'], 7);
                }
            }
        }

        return json([
            'total' => $total,
            'data' => $list
        ]);
    }

    /**
     * 获取加工件订单详情
     */
    public function detail()
    {
        $id = input('get.id/s', '');
        if (!$id) {
            return json(['code' => 400, 'msg' => '缺少订单id', 'data' => null]);
        }

        $row = Db::name('machined_parts_billing')
            ->where('id', $id)
            ->where('del_flag', 0)
            ->find();

        if (!$row) {
            return json(['code' => 404, 'msg' => '订单未找到', 'data' => null]);
        }

        // 关联客户信息
        if ($row['customer_id']) {
            $customer = Db::name('customer_detail_view')
                ->where('id', $row['customer_id'])
                ->find();
            if ($customer) {
                $row['customer_name'] = $customer['name'];
                $row['customer_phone'] = $customer['main_phone'];
            }
        }

        // 关联加工件信息
        if ($row['machined_parts_id']) {
            $parts = Db::name('machined_parts')
                ->where('id', $row['machined_parts_id'])
                ->find();
            if ($parts) {
                $row['machined_parts_name'] = $parts['name'];
            }
        }

        return json(['code' => 200, 'msg' => 'ok', 'data' => $row]);
    }

    /**
     * 保存加工件订单(新增/更新)
     */
    public function save()
    {
        $data = input('post.');

        $id = isset($data['id']) ? trim($data['id']) : '';
        $now = date('Y-m-d H:i:s');

        // 获取当前用户
        try {
            $userId = BaseToken::getCurrentUid();
        } catch (\Exception $e) {
            $userId = 'system';
        }

        // 处理JSON字段
        $toothPositionJson = null;
        if (isset($data['tooth_position_json']) && is_array($data['tooth_position_json'])) {
            $toothPositionJson = json_encode($data['tooth_position_json'], JSON_UNESCAPED_UNICODE);
        }

        $toothColorJson = null;
        if (isset($data['tooth_color_json']) && is_array($data['tooth_color_json'])) {
            $toothColorJson = json_encode($data['tooth_color_json'], JSON_UNESCAPED_UNICODE);
        }

        // 计算总价
        $num = isset($data['num']) ? (int) $data['num'] : 0;
        $singlePrice = isset($data['single_price']) ? (float) $data['single_price'] : 0;
        $totalPrice = $num * $singlePrice;

        $saveData = [
            'customer_id' => isset($data['customer_id']) ? trim($data['customer_id']) : null,
            'parts_billing_user_id' => isset($data['parts_billing_user_id']) ? trim($data['parts_billing_user_id']) : null,
            'parts_billing_user_name' => isset($data['parts_billing_user_name']) ? trim($data['parts_billing_user_name']) : null,
            'parts_billing_user_phone' => isset($data['parts_billing_user_phone']) ? trim($data['parts_billing_user_phone']) : null,
            'dept_id' => isset($data['dept_id']) ? trim($data['dept_id']) : null,
            'wish_return_time' => isset($data['wish_return_time']) ? $data['wish_return_time'] : null,
            'urgent_flag' => isset($data['urgent_flag']) ? ($data['urgent_flag'] ? 1 : 0) : 0,
            'on_time_flag' => isset($data['on_time_flag']) ? ($data['on_time_flag'] ? 1 : 0) : 0,
            'billing_id' => isset($data['billing_id']) ? trim($data['billing_id']) : null,
            'deposit_billing_id' => isset($data['deposit_billing_id']) ? trim($data['deposit_billing_id']) : null,
            'remark' => isset($data['remark']) ? $data['remark'] : null,
            'status' => isset($data['status']) ? (int) $data['status'] : 0,
            'machined_parts_id' => isset($data['machined_parts_id']) ? trim($data['machined_parts_id']) : null,
            'rework_flag' => isset($data['rework_flag']) ? ($data['rework_flag'] ? 1 : 0) : 0,
            'tooth_position_json' => $toothPositionJson,
            'tooth_color_json' => $toothColorJson,
            'num' => $num,
            'single_price' => $singlePrice,
            'total_price' => $totalPrice,
            'residue_num' => isset($data['residue_num']) ? (int) $data['residue_num'] : $num,
            'rework_reason_id' => isset($data['rework_reason_id']) ? trim($data['rework_reason_id']) : null,
            'rework_reason_detail' => isset($data['rework_reason_detail']) ? $data['rework_reason_detail'] : null,
            'receiver_user_id' => isset($data['receiver_user_id']) ? trim($data['receiver_user_id']) : null,
            'receiver_user_name' => isset($data['receiver_user_name']) ? trim($data['receiver_user_name']) : null,
            'file' => isset($data['file']) ? $data['file'] : null,
            'mail_date' => isset($data['mail_date']) ? $data['mail_date'] : null,
            'machined_parts_type' => isset($data['machined_parts_type']) ? (int) $data['machined_parts_type'] : null,
            'dental_implant_id' => isset($data['dental_implant_id']) ? trim($data['dental_implant_id']) : null,
            'movable_parts_id' => isset($data['movable_parts_id']) ? trim($data['movable_parts_id']) : null,
            'doctor_id' => isset($data['doctor_id']) ? trim($data['doctor_id']) : null,
            'doctor_name' => isset($data['doctor_name']) ? trim($data['doctor_name']) : null,
            'update_date' => $now,
            'update_by' => $userId
        ];

        if ($id) {
            // 更新
            $exists = Db::name('machined_parts_billing')->where('id', $id)->where('del_flag', 0)->find();
            if (!$exists) {
                return json(['code' => 404, 'msg' => '更新失败，记录不存在', 'data' => null]);
            }
            Db::name('machined_parts_billing')->where('id', $id)->update($saveData);
            $newRow = Db::name('machined_parts_billing')->where('id', $id)->find();
            return json(['code' => 200, 'msg' => '更新成功', 'data' => $newRow]);
        }

        // 新增
        $saveData['id'] = uuid4(random_bytes(16));
        $saveData['machined_parts_billing_no'] = $this->generateBillingNo();
        $saveData['create_date'] = $now;
        $saveData['create_by'] = $userId;
        $saveData['del_flag'] = 0;
        Db::name('machined_parts_billing')->insert($saveData);

        return json(['code' => 200, 'msg' => '保存成功', 'data' => $saveData]);
    }

    /**
     * 提交订单(状态从0变为1)
     */
    public function submit()
    {
        $id = input('post.id/s', '');
        if (!$id) {
            return json(['code' => 400, 'msg' => '缺少订单id', 'data' => null]);
        }

        // 获取当前用户
        try {
            $userId = BaseToken::getCurrentUid();
        } catch (\Exception $e) {
            $userId = 'system';
        }

        $exists = Db::name('machined_parts_billing')->where('id', $id)->where('del_flag', 0)->find();
        if (!$exists) {
            return json(['code' => 404, 'msg' => '订单不存在', 'data' => null]);
        }

        if ($exists['status'] != 0) {
            return json(['code' => 400, 'msg' => '只有待提交状态的订单才能提交', 'data' => null]);
        }

        Db::name('machined_parts_billing')->where('id', $id)->update([
            'status' => 1, // 制作中
            'update_date' => date('Y-m-d H:i:s'),
            'update_by' => $userId
        ]);

        return json(['code' => 200, 'msg' => '提交成功', 'data' => null]);
    }

    /**
     * 确认收货(状态从1变为2)
     */
    public function receive()
    {
        $id = input('post.id/s', '');
        if (!$id) {
            return json(['code' => 400, 'msg' => '缺少订单id', 'data' => null]);
        }

        // 获取当前用户
        try {
            $userId = BaseToken::getCurrentUid();
        } catch (\Exception $e) {
            $userId = 'system';
        }

        $exists = Db::name('machined_parts_billing')->where('id', $id)->where('del_flag', 0)->find();
        if (!$exists) {
            return json(['code' => 404, 'msg' => '订单不存在', 'data' => null]);
        }

        if ($exists['status'] != 1) {
            return json(['code' => 400, 'msg' => '只有制作中状态的订单才能确认收货', 'data' => null]);
        }

        Db::name('machined_parts_billing')->where('id', $id)->update([
            'status' => 2, // 已收货
            'update_date' => date('Y-m-d H:i:s'),
            'update_by' => $userId
        ]);

        return json(['code' => 200, 'msg' => '确认收货成功', 'data' => null]);
    }

    /**
     * 作废订单(状态变为3)
     */
    public function cancel()
    {
        $id = input('post.id/s', '');
        if (!$id) {
            return json(['code' => 400, 'msg' => '缺少订单id', 'data' => null]);
        }

        // 获取当前用户
        try {
            $userId = BaseToken::getCurrentUid();
        } catch (\Exception $e) {
            $userId = 'system';
        }

        $exists = Db::name('machined_parts_billing')->where('id', $id)->where('del_flag', 0)->find();
        if (!$exists) {
            return json(['code' => 404, 'msg' => '订单不存在', 'data' => null]);
        }

        if (in_array($exists['status'], [3, 4])) {
            return json(['code' => 400, 'msg' => '该订单已无法作废', 'data' => null]);
        }

        Db::name('machined_parts_billing')->where('id', $id)->update([
            'status' => 3, // 作废
            'update_date' => date('Y-m-d H:i:s'),
            'update_by' => $userId
        ]);

        return json(['code' => 200, 'msg' => '作废成功', 'data' => null]);
    }

    /**
     * 使用/部分使用(状态变为4或5)
     */
    public function useParts()
    {
        $data = input('post.');
        $id = isset($data['id']) ? trim($data['id']) : '';
        $useNum = isset($data['use_num']) ? (int) $data['use_num'] : 0;

        if (!$id) {
            return json(['code' => 400, 'msg' => '缺少订单id', 'data' => null]);
        }

        // 获取当前用户
        try {
            $userId = BaseToken::getCurrentUid();
        } catch (\Exception $e) {
            $userId = 'system';
        }

        $exists = Db::name('machined_parts_billing')->where('id', $id)->where('del_flag', 0)->find();
        if (!$exists) {
            return json(['code' => 404, 'msg' => '订单不存在', 'data' => null]);
        }

        if ($exists['status'] != 2) {
            return json(['code' => 400, 'msg' => '只有已收货状态的订单才能使用', 'data' => null]);
        }

        $residueNum = $exists['residue_num'] ?? $exists['num'];
        if ($useNum > $residueNum) {
            return json(['code' => 400, 'msg' => '使用数量不能大于剩余数量', 'data' => null]);
        }

        $newResidueNum = $residueNum - $useNum;
        $newStatus = $newResidueNum > 0 ? 5 : 4; // 部分使用 或 已使用

        Db::name('machined_parts_billing')->where('id', $id)->update([
            'residue_num' => $newResidueNum,
            'status' => $newStatus,
            'update_date' => date('Y-m-d H:i:s'),
            'update_by' => $userId
        ]);

        return json(['code' => 200, 'msg' => '使用成功', 'data' => null]);
    }

    /**
     * 删除订单(逻辑删除)
     */
    public function delete()
    {
        $id = input('post.id/s', '');
        if (!$id) {
            return json(['code' => 400, 'msg' => '缺少订单id', 'data' => null]);
        }

        // 获取当前用户
        try {
            $userId = BaseToken::getCurrentUid();
        } catch (\Exception $e) {
            $userId = 'system';
        }

        $exists = Db::name('machined_parts_billing')->where('id', $id)->where('del_flag', 0)->find();
        if (!$exists) {
            return json(['code' => 404, 'msg' => '删除失败，记录不存在', 'data' => null]);
        }

        Db::name('machined_parts_billing')->where('id', $id)->update([
            'del_flag' => 1,
            'update_date' => date('Y-m-d H:i:s'),
            'update_by' => $userId
        ]);

        return json(['code' => 200, 'msg' => '删除成功', 'data' => null]);
    }

    /**
     * 生成订单编号
     */
    private function generateBillingNo()
    {
        $prefix = 'JJB';
        $date = date('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return $prefix . $date . $random;
    }
}
