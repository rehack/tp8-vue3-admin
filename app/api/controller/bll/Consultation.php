<?php

namespace app\api\controller\bll;

use think\facade\Db;

class Consultation extends BaseController
{
    /**
     * 获取咨询列表
     */
    public function getlist()
    {
        $page = max(1, (int) input('get.page/d', 1));
        $limit = max(1, min(500, (int) input('get.limit/d', 100)));
        $offset = ($page - 1) * $limit;

        $customerVipNum = input('get.customer_vip_num/s', '');
        $status = input('get.status/s', '');
        $type = input('get.type/s', '');
        $customerName = input('get.customer_name/s', '');
        $customerPhone = input('get.customer_phone/s', '');

        // 构建基础查询条件
        $where = [
            ['c.del_flag', '=', 0]
        ];

        // 如果有会员号，先查询对应的客户ID
        $customerId = '';
        if ($customerVipNum !== '') {
            $customerInfo = Db::name('customer_detail_view')
                ->where('vip_num', $customerVipNum)
                ->find();
            if (!$customerInfo) {
                return json(['total' => 0, 'data' => []]);
            }
            $customerId = $customerInfo['id'];
            $where[] = ['c.customer_id', '=', $customerId];
        }
        
        if ($status !== '') {
            $where[] = ['c.status', '=', $status];
        }
        if ($type !== '') {
            $where[] = ['c.type', '=', $type];
        }

        // 需要关联查询的条件
        $needJoinCustomer = false;
        $phoneRaw = '';
        
        if ($customerName !== '') {
            $needJoinCustomer = true;
            $where[] = ['cu.name', '=', $customerName];
        }
        if ($customerPhone !== '') {
            $needJoinCustomer = true;
            $cleanPhone = preg_replace('/[^0-9]/', '', $customerPhone);
            if ($cleanPhone !== '') {
                $phoneRaw = "cu.main_phone = '$cleanPhone'";
            }
        }

        // 如果有客户ID，需要join客户表获取客户信息
        if ($customerId !== '' && !$needJoinCustomer && $phoneRaw === '') {
            // 需要join客户表获取客户姓名、电话、会员号
            $idsQuery = Db::name('consultation')
                ->alias('c')
                ->where($where)
                ->field('c.id')
                ->order('c.create_date', 'desc');
            
            // 获取总数（带缓存）
            $cacheKey = 'consultation_count_by_customer_' . md5($customerId);
            $total = cache($cacheKey);
            if ($total === false) {
                $total = $idsQuery->count();
                cache($cacheKey, $total, 60);
            }
            
            $ids = $idsQuery->limit($offset, $limit)->column('id');
            
            if (empty($ids)) {
                return json(['total' => $total, 'data' => []]);
            }
            
            // 根据IDs查询详情 - 需要join客户表获取客户信息
            $list = Db::name('consultation')
                ->alias('c')
                ->leftJoin('customer_detail_view cu', 'c.customer_id = cu.id')
                ->leftJoin('sys_user u', 'c.user_id = u.id')
                ->whereIn('c.id', $ids)
                ->field('c.*, cu.name as customer_name, cu.main_phone as customer_phone, cu.vip_num as customer_vip_num, u.name as user_name')
                ->order('c.create_date', 'asc')
                ->select();
                
        } else {
            // 需要join客户表的情况
            $listQuery = Db::name('consultation')->alias('c');
            
            if ($needJoinCustomer) {
                $listQuery->leftJoin('customer_detail_view cu', 'c.customer_id = cu.id');
            }
            
            $listQuery->where($where);
            
            if ($phoneRaw !== '') {
                $listQuery->whereRaw($phoneRaw);
            }
            
            // 先查ID再查详情
            $ids = $listQuery->page($page, $limit)->column('c.id');
            
            // 重新构建查询获取总数
            $countQuery = Db::name('consultation')->alias('c');
            if ($needJoinCustomer) {
                $countQuery->leftJoin('customer_detail_view cu', 'c.customer_id = cu.id');
            }
            $countQuery->where($where);
            if ($phoneRaw !== '') {
                $countQuery->whereRaw($phoneRaw);
            }
            $total = $countQuery->count();
            
            if (!empty($ids)) {
                $list = Db::name('consultation')
                    ->alias('c')
                    ->leftJoin('customer_detail_view cu', 'c.customer_id = cu.id')
                    ->leftJoin('sys_user u', 'c.user_id = u.id')
                    ->whereIn('c.id', $ids)
                    ->field('c.*, cu.name as customer_name, cu.main_phone as customer_phone, cu.vip_num as customer_vip_num, u.name as user_name')
                    ->select();
            } else {
                $list = [];
            }
        }

        // 对手机号进行脱敏处理
        if (!empty($list)) {
            $list = $list->toArray();
            foreach ($list as &$row) {
                if (!empty($row['customer_phone']) && strlen($row['customer_phone']) >= 11) {
                    $row['customer_phone'] = substr($row['customer_phone'], 0, 3) . '****' . substr($row['customer_phone'], 7);
                }
            }
            unset($row);
        }

        return json([
            'total' => $total ?? 0,
            'data' => $list ?? []
        ]);
    }

    /**
     * 获取咨询详情
     */
    public function detail()
    {
        $id = input('get.id/s', '');
        $customerId = input('get.customer_id/s', '');
        
        // 支持 id 或 customer_id 查询
        if (!$id && !$customerId) {
            return json(['code' => 400, 'msg' => '缺少咨询id或客户id', 'data' => null]);
        }

        // 如果传了 customer_id，查询该客户的咨询记录
        if ($customerId) {
            $cacheKey = 'consultation_customer_' . md5($customerId);
            $cacheData = cache($cacheKey);
            
            if ($cacheData !== false) {
                return json(['code' => 200, 'msg' => 'ok', 'data' => $cacheData]);
            }
            
            $rows = Db::name('consultation')
                ->alias('c')
                ->leftJoin('customer_detail_view cu', 'c.customer_id = cu.id')
                ->leftJoin('sys_user u', 'c.user_id = u.id')
                ->where('c.customer_id', $customerId)
                ->where('c.del_flag', 0)
                ->field('c.*, cu.name as customer_name, cu.main_phone as customer_phone, cu.vip_num as customer_vip_num, u.name as user_name')
                ->order('c.create_date', 'desc')
                ->select();
                
            if (empty($rows) || $rows->isEmpty()) {
                return json(['code' => 404, 'msg' => '暂无该客户的咨询记录', 'data' => null]);
            }
            
            $rows = $rows->toArray();
            cache($cacheKey, $rows, 300);
            
            return json(['code' => 200, 'msg' => 'ok', 'data' => $rows]);
        }

        // 如果传了 id，直接查询
        $cacheKey = 'consultation_detail_' . md5($id);
        $cacheData = cache($cacheKey);
        
        if ($cacheData !== false) {
            return json(['code' => 200, 'msg' => 'ok', 'data' => $cacheData]);
        }

        $row = Db::name('consultation')
            ->alias('c')
            ->leftJoin('customer_detail_view cu', 'c.customer_id = cu.id')
            ->leftJoin('sys_user u', 'c.user_id = u.id')
            ->where('c.id', $id)
            ->where('c.del_flag', 0)
            ->field('c.*, cu.name as customer_name, cu.main_phone as customer_phone, cu.vip_num as customer_vip_num, u.name as user_name')
            ->find();

        if (!$row) {
            return json(['code' => 404, 'msg' => '咨询记录未找到', 'data' => null]);
        }

        cache($cacheKey, $row, 300);

        return json(['code' => 200, 'msg' => 'ok', 'data' => $row]);
    }

    /**
     * 保存咨询记录
     */
    public function save()
    {
        $data = input('post.');
        if (empty($data['customer_id'])) {
            return json(['code' => 400, 'msg' => '缺少客户ID', 'data' => null]);
        }

        $id = isset($data['id']) ? trim($data['id']) : '';
        $now = date('Y-m-d H:i:s');

        $saveData = [
            'customer_id' => $data['customer_id'],
            'user_id' => isset($data['user_id']) ? $data['user_id'] : null,
            'now_pill' => isset($data['now_pill']) ? $data['now_pill'] : null,
            'now_pill_remark' => isset($data['now_pill_remark']) ? $data['now_pill_remark'] : null,
            'belly_state' => isset($data['belly_state']) ? $data['belly_state'] : null,
            'month_state' => isset($data['month_state']) ? $data['month_state'] : null,
            'sleep_state' => isset($data['sleep_state']) ? $data['sleep_state'] : null,
            'diagnosis' => isset($data['diagnosis']) ? $data['diagnosis'] : null,
            'customer_remark' => isset($data['customer_remark']) ? $data['customer_remark'] : null,
            'handle_remark' => isset($data['handle_remark']) ? $data['handle_remark'] : null,
            'undeal_remark' => isset($data['undeal_remark']) ? $data['undeal_remark'] : null,
            'status' => isset($data['status']) ? (int) $data['status'] : 0,
            'type' => isset($data['type']) ? (int) $data['type'] : 1,
            'update_date' => $now,
            'del_flag' => 0
        ];

        try {
            if ($id) {
                $exists = Db::name('consultation')->where('id', $id)->where('del_flag', 0)->find();
                if (!$exists) {
                    return json(['code' => 404, 'msg' => '更新失败，记录不存在', 'data' => null]);
                }

                $saveData['update_date'] = $now;
                Db::name('consultation')->where('id', $id)->update($saveData);

                // 清除缓存
                cache('consultation_detail_' . md5($id), null);
                if (!empty($exists['customer_id'])) {
                    cache('consultation_customer_' . md5($exists['customer_id']), null);
                }

                return json(['code' => 200, 'msg' => '更新成功', 'data' => $id]);
            } else {
                $saveData['id'] = $id ? $id : $this->generateUuid();
                $saveData['create_date'] = $now;
                $saveData['update_date'] = $now;
                Db::name('consultation')->insert($saveData);

                return json(['code' => 200, 'msg' => '添加成功', 'data' => $saveData['id']]);
            }
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '操作失败: ' . $e->getMessage(), 'data' => null]);
        }
    }

    /**
     * 删除咨询记录（软删除）
     */
    public function delete()
    {
        $id = input('get.id/s', '');
        if (!$id) {
            return json(['code' => 400, 'msg' => '缺少咨询记录id', 'data' => null]);
        }

        try {
            $exists = Db::name('consultation')->where('id', $id)->where('del_flag', 0)->find();
            if (!$exists) {
                return json(['code' => 404, 'msg' => '记录不存在', 'data' => null]);
            }

            Db::name('consultation')->where('id', $id)->update([
                'del_flag' => 1,
                'update_date' => date('Y-m-d H:i:s')
            ]);

            // 清除缓存
            cache('consultation_detail_' . md5($id), null);
            if (!empty($exists['customer_id'])) {
                cache('consultation_customer_' . md5($exists['customer_id']), null);
            }

            return json(['code' => 200, 'msg' => '删除成功', 'data' => null]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '删除失败: ' . $e->getMessage(), 'data' => null]);
        }
    }

    /**
     * 获取咨询类型列表
     */
    public function getTypeList()
    {
        return json(['code' => 200, 'msg' => 'ok', 'data' => [
            ['value' => 1, 'label' => '咨询'],
            ['value' => 2, 'label' => '评估'],
            ['value' => 4, 'label' => '复查'],
            ['value' => 5, 'label' => '治疗']
        ]]);
    }

    /**
     * 获取咨询状态列表
     */
    public function getStatusList()
    {
        return json(['code' => 200, 'msg' => 'ok', 'data' => [
            ['value' => 0, 'label' => '未处理'],
            ['value' => 1, 'label' => '已处理'],
            ['value' => 2, 'label' => '已预约'],
            ['value' => 3, 'label' => '已消费'],
            ['value' => 4, 'label' => '已流失']
        ]]);
    }

    /**
     * 生成UUID
     */
    private function generateUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
