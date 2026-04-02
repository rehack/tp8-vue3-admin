<?php

namespace app\api\controller\bll;

use think\facade\Db;

class TreatRecord extends BaseController
{
    /**
     * 获取治疗记录列表 - 优化版
     */
    public function getlist()
    {
        $page = max(1, (int) input('get.page/d', 1));
        $limit = max(1, min(500, (int) input('get.limit/d', 20)));
        $offset = ($page - 1) * $limit;

        $treatRecordNo = input('get.treat_record_no/s', '');
        $customerId = input('get.customer_id/s', '');
        $customerName = input('get.customer_name/s', '');
        $customerPhone = input('get.customer_phone/s', '');
        $treatDeptId = input('get.treat_dept_id/s', '');
        $status = input('get.status/s', '');
        $verifyStatus = input('get.verify_status/s', '');
        $startDate = input('get.start_date/s', '');
        $endDate = input('get.end_date/s', '');

        // 构建基础条件
        $where = [
            ['t.del_flag', '=', 0]
        ];

        // 精确条件优先处理
        if ($treatRecordNo !== '') {
            $where[] = ['t.treat_record_no', '=', $treatRecordNo];
        }
        if ($treatDeptId !== '') {
            $where[] = ['t.treat_dept_id', '=', $treatDeptId];
        }
        if ($status !== '') {
            $where[] = ['t.finish_flag', '=', $status];
        }
        if ($verifyStatus !== '') {
            $where[] = ['t.verify_status', '=', $verifyStatus];
        }
        if ($startDate !== '') {
            $where[] = ['t.treatment_time', '>=', $startDate];
        }
        if ($endDate !== '') {
            $where[] = ['t.treatment_time', '<=', $endDate . ' 23:59:59'];
        }

        // 优先处理客户ID - 使用索引
        if ($customerId !== '') {
            $where[] = ['t.customer_id', '=', $customerId];
            return $this->queryWithCustomerId($where, $offset, $limit, $page);
        }

        // 姓名或手机号查询 - 使用子查询优化
        if ($customerName !== '' || $customerPhone !== '') {
            return $this->queryWithCustomerInfo($customerName, $customerPhone, $where, $offset, $limit, $page);
        }

        // 无客户条件查询
        return $this->queryDefault($where, $offset, $limit, $page);
    }

    /**
     * 通过客户ID查询 - 性能最好
     */
    private function queryWithCustomerId($where, $offset, $limit, $page)
    {
        // 生成缓存键
        $cacheKey = 'treat_record_count_' . md5(serialize($where));
        
        // 查询ID列表（带缓存）
        $idsQuery = Db::name('treat_record')
            ->alias('t')
            ->where($where)
            ->field('t.id')
            ->order('t.treatment_time', 'desc')
            ->order('t.create_date', 'desc');
        
        // 获取总数（带缓存）- 修复：使用 is_null 判断，因为缓存 0 时返回 0 而不是 false
        $total = cache($cacheKey);
        if (!is_null($total)) {
            $total = (int) $total;
        } else {
            $total = $idsQuery->count();
            cache($cacheKey, $total, 60);
        }
        
        // 分页查询IDs
        $ids = $idsQuery->limit($offset, $limit)->column('id');
        
        if (empty($ids)) {
            return json(['total' => $total, 'data' => []]);
        }
        
        // 根据IDs查询详情 - join客户表获取客户信息
        $list = Db::name('treat_record')
            ->alias('t')
            ->leftJoin('customer_detail_view c', 't.customer_id = c.id')
            ->leftJoin('sys_dept d', 't.treat_dept_id = d.id')
            ->whereIn('t.id', $ids)
            ->field('
                t.id,
                t.treat_record_no,
                t.treatment_time,
                t.treat_dept_id,
                t.treat_dept_name,
                t.treat_content,
                t.deal_with,
                t.multiple_flag,
                t.finish_flag,
                t.deduct_flag,
                t.verify_status,
                t.anesthesia_name,
                t.create_date,
                t.update_date,
                c.id as customer_id,
                c.name as customer_name,
                c.main_phone as customer_phone,
                d.name as dept_name
            ')
            ->select();

        return $this->formatList($list, $total);
    }

    /**
     * 通过姓名/手机号查询 - 使用子查询优化
     */
    private function queryWithCustomerInfo($customerName, $customerPhone, $where, $offset, $limit, $page)
    {
        // 先从客户表中获取符合条件的客户ID
        $customerWhere = [];
        if ($customerName !== '') {
            $customerWhere[] = ['name', '=', $customerName];
        }
        if ($customerPhone !== '') {
            $cleanPhone = preg_replace('/[^0-9]/', '', $customerPhone);
            if ($cleanPhone !== '') {
                $customerWhere[] = ['main_phone', '=', $cleanPhone];
            }
        }

        // 查询客户ID列表
        $customerIds = Db::name('customer_detail_view')
            ->where($customerWhere)
            ->column('id');

        if (empty($customerIds)) {
            return json(['total' => 0, 'data' => []]);
        }

        // 添加客户ID条件
        $where[] = ['t.customer_id', 'in', $customerIds];

        // 生成缓存键
        $cacheKey = 'treat_record_count_by_customer_' . md5(serialize($where));
        
        // 获取总数（带缓存）- 修复：使用 is_null 判断
        $total = cache($cacheKey);
        if (!is_null($total)) {
            $total = (int) $total;
        } else {
            // 单独查询总数，不用同一个query对象
            $total = Db::name('treat_record')
                ->alias('t')
                ->where($where)
                ->count();
            cache($cacheKey, $total, 60);
        }
        
        // 分页查询IDs - 创建新的查询对象
        $ids = Db::name('treat_record')
            ->alias('t')
            ->where($where)
            ->field('t.id')
            ->order('t.treatment_time', 'desc')
            ->order('t.create_date', 'desc')
            ->limit($offset, $limit)
            ->column('id');
        
        if (empty($ids)) {
            return json(['total' => $total, 'data' => []]);
        }
        
        // 根据IDs查询详情
        $list = Db::name('treat_record')
            ->alias('t')
            ->leftJoin('customer_detail_view c', 't.customer_id = c.id')
            ->leftJoin('sys_dept d', 't.treat_dept_id = d.id')
            ->whereIn('t.id', $ids)
            ->field('
                t.id,
                t.treat_record_no,
                t.treatment_time,
                t.treat_dept_id,
                t.treat_dept_name,
                t.treat_content,
                t.deal_with,
                t.multiple_flag,
                t.finish_flag,
                t.deduct_flag,
                t.verify_status,
                t.anesthesia_name,
                t.create_date,
                t.update_date,
                c.id as customer_id,
                c.name as customer_name,
                c.main_phone as customer_phone,
                d.name as dept_name
            ')
            ->select();

        return $this->formatList($list, $total);
    }

    /**
     * 默认查询（无条件或只有日期等条件）
     */
    private function queryDefault($where, $offset, $limit, $page)
    {
        // 生成缓存键
        $cacheKey = 'treat_record_count_default_' . md5(serialize($where));
        
        // 获取总数（带缓存）- 修复：使用 is_null 判断
        $total = cache($cacheKey);
        if (!is_null($total)) {
            $total = (int) $total;
        } else {
            // 单独查询总数
            $total = Db::name('treat_record')
                ->alias('t')
                ->where($where)
                ->count();
            cache($cacheKey, $total, 30);
        }
        
        // 分页查询IDs - 创建新的查询对象
        $ids = Db::name('treat_record')
            ->alias('t')
            ->where($where)
            ->field('t.id')
            ->order('t.treatment_time', 'desc')
            ->order('t.create_date', 'desc')
            ->limit($offset, $limit)
            ->column('id');
        
        if (empty($ids)) {
            return json(['total' => $total, 'data' => []]);
        }
        
        // 根据IDs查询详情
        $list = Db::name('treat_record')
            ->alias('t')
            ->leftJoin('customer_detail_view c', 't.customer_id = c.id')
            ->leftJoin('sys_dept d', 't.treat_dept_id = d.id')
            ->whereIn('t.id', $ids)
            ->field('
                t.id,
                t.treat_record_no,
                t.treatment_time,
                t.treat_dept_id,
                t.treat_dept_name,
                t.treat_content,
                t.deal_with,
                t.multiple_flag,
                t.finish_flag,
                t.deduct_flag,
                t.verify_status,
                t.anesthesia_name,
                t.create_date,
                t.update_date,
                c.id as customer_id,
                c.name as customer_name,
                c.main_phone as customer_phone,
                d.name as dept_name
            ')
            ->select();

        return $this->formatList($list, $total);
    }

    /**
     * 格式化列表数据
     */
    private function formatList($list, $total)
    {
        if (!empty($list)) {
            $list = $list->toArray();
            foreach ($list as &$item) {
                // 布尔字段转换
                $item['multiple_flag'] = (bool) $item['multiple_flag'];
                $item['finish_flag'] = (bool) $item['finish_flag'];
                $item['deduct_flag'] = (bool) $item['deduct_flag'];
                
                // 手机号脱敏处理
                if (!empty($item['customer_phone']) && strlen($item['customer_phone']) >= 11) {
                    $item['customer_phone'] = substr($item['customer_phone'], 0, 3) . '****' . substr($item['customer_phone'], 7);
                }
            }
            unset($item);
        }

        return json([
            'total' => $total ?? 0,
            'data' => $list ?? []
        ]);
    }

    /**
     * 获取划扣记录列表
     */
    public function getDeductRecordList()
    {
        $page = max(1, (int) input('get.page/d', 1));
        $limit = max(1, min(500, (int) input('get.limit/d', 20)));
        $offset = ($page - 1) * $limit;

        $treatRecordId = input('get.treat_record_id/s', '');
        $startDate = input('get.start_date/s', '');
        $endDate = input('get.end_date/s', '');
        $deductUserName = input('get.deduct_user_name/s', '');
        $deductDeptId = input('get.deduct_dept_id/s', '');

        $where = [
            ['del_flag', '=', 0]
        ];

        if ($treatRecordId !== '') {
            $where[] = ['treat_record_id', '=', $treatRecordId];
        }
        if ($startDate !== '') {
            $where[] = ['deduct_time', '>=', $startDate];
        }
        if ($endDate !== '') {
            $where[] = ['deduct_time', '<=', $endDate . ' 23:59:59'];
        }
        if ($deductUserName !== '') {
            $where[] = ['deduct_user_name', 'like', '%' . $deductUserName . '%'];
        }
        if ($deductDeptId !== '') {
            $where[] = ['deduct_dept_id', '=', $deductDeptId];
        }

        // 生成缓存键
        $cacheKey = 'deduct_record_count_' . md5(serialize($where));
        
        // 获取总数
        $total = cache($cacheKey);
        if ($total === false) {
            $total = Db::name('treat_deduct_record')
                ->where($where)
                ->count();
            cache($cacheKey, $total, 60);
        }

        // 分页查询
        $list = Db::name('treat_deduct_record')
            ->where($where)
            ->field('
                id,
                treat_record_id,
                deduct_record_no,
                deduct_time,
                deduct_dept_id,
                deduct_dept_name,
                deduct_user_id,
                deduct_user_name,
                deduct_remark,
                create_date
            ')
            ->order('deduct_time', 'desc')
            ->order('create_date', 'desc')
            ->limit($offset, $limit)
            ->select();

        if (!empty($list)) {
            $list = $list->toArray();
            foreach ($list as &$item) {
                // 如果有治疗记录ID，获取治疗记录号
                if (!empty($item['treat_record_id'])) {
                    $treatRecord = Db::name('treat_record')
                        ->where('id', $item['treat_record_id'])
                        ->field('treat_record_no')
                        ->find();
                    $item['treat_record_no'] = $treatRecord['treat_record_no'] ?? '';
                }
            }
            unset($item);
        }

        return json([
            'code' => 200,
            'msg' => 'ok',
            'total' => $total,
            'data' => $list ?? []
        ]);
    }

    /**
     * 获取订单项治疗进度详情
     */
    public function getBillingItemTreatProgress()
    {
        $billingItemId = input('get.billing_item_id/s', '');
        if (!$billingItemId) {
            return json(['code' => 400, 'msg' => '缺少订单项id', 'data' => null]);
        }

        // 使用缓存
        $cacheKey = 'treat_record_detail_' . md5($id);
        $cacheData = cache($cacheKey);
        
        if ($cacheData !== false) {
            return json(['code' => 200, 'msg' => 'ok', 'data' => $cacheData]);
        }

        try {
            // 先查询治疗记录主表
            $row = Db::name('treat_record')
                ->where('id', $id)
                ->where('del_flag', 0)
                ->find();

            if (!$row) {
                return json(['code' => 404, 'msg' => '治疗记录未找到', 'data' => null]);
            }

            // 单独查询客户信息
            if (!empty($row['customer_id'])) {
                $customer = Db::name('customer_detail_view')
                    ->where('id', $row['customer_id'])
                    ->find();
                if ($customer) {
                    $row['customer_name'] = $customer['name'] ?? '';
                    $row['customer_phone'] = $customer['main_phone'] ?? '';
                    $row['customer_sex'] = $customer['sex'] ?? '';
                    $row['customer_age'] = $customer['age'] ?? '';
                }
            }

            // 单独查询科室信息
            if (!empty($row['treat_dept_id'])) {
                $dept = Db::name('sys_dept')
                    ->where('id', $row['treat_dept_id'])
                    ->find();
                $row['dept_name'] = $dept['name'] ?? '';
            }
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '查询错误: ' . $e->getMessage(), 'data' => null]);
        }

        // 处理JSON字段
        $jsonFields = ['injection_site', 'deal_with_tooth_pos', 'dynamic_form_setting', 'dynamic_form_value', 'search_key', 'sign_info'];
        foreach ($jsonFields as $field) {
            if (isset($row[$field]) && !empty($row[$field])) {
                $decoded = json_decode($row[$field], true);
                if ($decoded !== null) {
                    $row[$field] = $decoded;
                }
            } else {
                $row[$field] = null;
            }
        }

        // 处理布尔字段
        $boolFields = ['multiple_flag', 'finish_flag', 'open_flag', 'deduct_flag', 'verify_flag'];
        foreach ($boolFields as $field) {
            if (isset($row[$field])) {
                $row[$field] = (bool) $row[$field];
            }
        }

        // 缓存详情数据
        cache($cacheKey, $row, 300);

        return json(['code' => 200, 'msg' => 'ok', 'data' => $row]);
    }

    /**
     * 获取治疗记录项目列表
     */
    public function getTreatRecordItemList()
    {
        $page = max(1, (int) input('get.page/d', 1));
        $limit = max(1, min(500, (int) input('get.limit/d', 100)));
        $offset = ($page - 1) * $limit;

        $treatRecordId = input('get.treat_record_id/s', '');
        $billingId = input('get.billing_id/s', '');
        $billingItemId = input('get.billing_item_id/s', '');
        $customerId = input('get.customer_id/s', '');

        if (!$treatRecordId && !$billingId && !$billingItemId && !$customerId) {
            return json(['code' => 400, 'msg' => '缺少查询条件', 'data' => null]);
        }

        $where = [
            ['tri.del_flag', '=', 0]
        ];

        if ($treatRecordId !== '') {
            $where[] = ['tri.treat_record_id', '=', $treatRecordId];
        }
        if ($billingId !== '') {
            $where[] = ['tri.billing_id', '=', $billingId];
        }
        if ($billingItemId !== '') {
            $where[] = ['tri.billing_item_id', '=', $billingItemId];
        }
        if ($customerId !== '') {
            $where[] = ['tri.customer_id', '=', $customerId];
        }

        // 优化：使用ID分页
        $idsQuery = Db::name('treat_record_item')
            ->alias('tri')
            ->where($where)
            ->field('tri.id')
            ->order('tri.create_date', 'desc');
        
        // 总数（带缓存）
        $cacheKeyCount = 'tri_count_' . md5(serialize($where));
        $total = cache($cacheKeyCount);
        if ($total === false) {
            $total = $idsQuery->count();
            cache($cacheKeyCount, $total, 60);
        }
        
        $ids = $idsQuery->limit($offset, $limit)->column('id');
        
        if (empty($ids)) {
            return json(['code' => 200, 'msg' => 'ok', 'total' => $total, 'data' => []]);
        }

        // 列表 - 减少JOIN
        $list = Db::name('treat_record_item')
            ->alias('tri')
            ->leftJoin('billing_item bi', 'tri.billing_item_id = bi.id')
            ->leftJoin('billing b', 'tri.billing_id = b.id')
            ->leftJoin('treat_record tr', 'tri.treat_record_id = tr.id')
            ->whereIn('tri.id', $ids)
            ->field('
                tri.id,
                tri.treat_record_id,
                tri.billing_id,
                tri.billing_item_id,
                tri.billing_item_name,
                tri.treat_count,
                tri.first_deduct_flag,
                tri.project_confirmation_flag,
                tri.perform_price,
                tri.perform_given_balance,
                tri.perform_card_money,
                tri.perform_other_virtual_money,
                tri.perform_score_money,
                tri.manual_num,
                tri.perform_given_equity,
                tri.create_date,
                bi.billing_item_name as item_name,
                bi.billing_item_type as item_type,
                b.billing_no,
                tr.treat_record_no,
                tr.treatment_time
            ')
            ->select();

        // 处理字段
        if (!empty($list)) {
            $list = $list->toArray();
            foreach ($list as &$item) {
                $item['first_deduct_flag'] = (bool) $item['first_deduct_flag'];
                $item['project_confirmation_flag'] = (bool) $item['project_confirmation_flag'];
                
                // 金额字段转换
                $item['perform_price'] = (float) $item['perform_price'];
                $item['perform_given_balance'] = (float) $item['perform_given_balance'];
                $item['perform_card_money'] = (float) $item['perform_card_money'];
                $item['perform_other_virtual_money'] = (float) $item['perform_other_virtual_money'];
                $item['perform_score_money'] = (float) $item['perform_score_money'];
                $item['perform_given_equity'] = (float) $item['perform_given_equity'];
            }
            unset($item);
        }

        return json([
            'code' => 200,
            'msg' => 'ok',
            'total' => $total,
            'data' => $list
        ]);
    }

    /**
     * 获取治疗记录项目详情
     */
    public function getTreatRecordItemDetail()
    {
        $id = input('get.id/s', '');
        if (!$id) {
            return json(['code' => 400, 'msg' => '缺少治疗记录项目id', 'data' => null]);
        }

        $item = Db::name('treat_record_item')
            ->alias('tri')
            ->leftJoin('billing_item bi', 'tri.billing_item_id = bi.id')
            ->leftJoin('billing b', 'tri.billing_id = b.id')
            ->leftJoin('treat_record tr', 'tri.treat_record_id = tr.id')
            ->where('tri.id', $id)
            ->where('tri.del_flag', 0)
            ->field('
                tri.*,
                bi.billing_item_name,
                bi.billing_item_type,
                bi.unit_price,
                bi.number_of_time,
                bi.total_price,
                bi.residue_num,
                bi.finish_flag,
                b.billing_no,
                b.status as billing_status,
                tr.treat_record_no,
                tr.treatment_time,
                tr.treat_dept_name
            ')
            ->find();

        if (!$item) {
            return json(['code' => 404, 'msg' => '治疗记录项目未找到', 'data' => null]);
        }

        // 布尔字段转换
        $item['first_deduct_flag'] = (bool) $item['first_deduct_flag'];
        $item['project_confirmation_flag'] = (bool) $item['project_confirmation_flag'];

        // 金额字段转换
        $decimalFields = ['perform_price', 'perform_given_balance', 'perform_card_money',
            'perform_other_virtual_money', 'perform_score_money', 'perform_given_equity', 
            'change_fair_value', 'unit_price', 'total_price', 'residue_num'];
        foreach ($decimalFields as $field) {
            if (isset($item[$field])) {
                $item[$field] = (float) $item[$field];
            }
        }

        return json(['code' => 200, 'msg' => 'ok', 'data' => $item]);
    }

    /**
     * 获取客户治疗项目列表（所有治疗过的项目）
     */
    public function getCustomerTreatProjectList()
    {
        $page = max(1, (int) input('get.page/d', 1));
        $limit = max(1, min(500, (int) input('get.limit/d', 100)));
        $offset = ($page - 1) * $limit;

        $customerId = input('get.customer_id/s', '');
        $billingItemName = input('get.billing_item_name/s', '');
        $startDate = input('get.start_date/s', '');
        $endDate = input('get.end_date/s', '');

        if (!$customerId) {
            return json(['code' => 400, 'msg' => '缺少客户id', 'data' => null]);
        }

        $where = [
            ['tri.del_flag', '=', 0],
            ['tri.customer_id', '=', $customerId]
        ];

        if ($billingItemName !== '') {
            $where[] = ['bi.billing_item_name', 'like', '%' . $billingItemName . '%'];
        }
        if ($startDate !== '') {
            $where[] = ['tr.treatment_time', '>=', $startDate];
        }
        if ($endDate !== '') {
            $where[] = ['tr.treatment_time', '<=', $endDate . ' 23:59:59'];
        }

        // 总数（带缓存）
        $cacheKeyCount = 'customer_treat_project_count_' . md5($customerId . $billingItemName . $startDate . $endDate);
        $total = cache($cacheKeyCount);
        if ($total === false) {
            $total = Db::name('treat_record_item')
                ->alias('tri')
                ->leftJoin('billing_item bi', 'tri.billing_item_id = bi.id')
                ->leftJoin('treat_record tr', 'tri.treat_record_id = tr.id')
                ->where($where)
                ->count('DISTINCT bi.id');
            cache($cacheKeyCount, $total, 60);
        }

        // 列表 - 按订单项分组统计
        $list = Db::name('treat_record_item')
            ->alias('tri')
            ->leftJoin('billing_item bi', 'tri.billing_item_id = bi.id')
            ->leftJoin('treat_record tr', 'tri.treat_record_id = tr.id')
            ->where($where)
            ->field('
                bi.id as billing_item_id,
                bi.billing_item_name,
                bi.billing_item_type,
                bi.item_data_id,
                bi.package_id,
                bi.package_name,
                bi.unit_price,
                bi.number_of_time,
                bi.total_price,
                bi.residue_num,
                bi.finish_flag,
                COUNT(tri.id) as treat_times,
                SUM(tri.treat_count) as total_treat_count,
                SUM(tri.perform_price) as total_perform_price,
                MAX(tr.treatment_time) as last_treat_time,
                MIN(tr.treatment_time) as first_treat_time
            ')
            ->group('bi.id')
            ->order('last_treat_time', 'desc')
            ->limit($offset, $limit)
            ->select();

        // 处理字段
        if (!empty($list)) {
            $list = $list->toArray();
            foreach ($list as &$item) {
                $item['finish_flag'] = (bool) $item['finish_flag'];
                $item['unit_price'] = (float) $item['unit_price'];
                $item['total_price'] = (float) $item['total_price'];
                $item['residue_num'] = (float) $item['residue_num'];
                $item['total_perform_price'] = (float) $item['total_perform_price'];
            }
            unset($item);
        }

        return json([
            'code' => 200,
            'msg' => 'ok',
            'total' => $total,
            'data' => $list
        ]);
    }

    /**
     * 获取订单项的治疗进度详情
     */
    // public function getBillingItemTreatProgress()
    // {
    //     $billingItemId = input('get.billing_item_id/s', '');
    //     if (!$billingItemId) {
    //         return json(['code' => 400, 'msg' => '缺少订单项id', 'data' => null]);
    //     }

    //     // 缓存键
    //     $cacheKey = 'billing_item_treat_progress_' . $billingItemId;
    //     $cacheData = cache($cacheKey);
        
    //     if ($cacheData !== false) {
    //         return json(['code' => 200, 'msg' => 'ok', 'data' => $cacheData]);
    //     }

    //     // 获取订单项基本信息
    //     $billingItem = Db::name('billing_item')
    //         ->where('id', $billingItemId)
    //         ->where('del_flag', 0)
    //         ->find();

    //     if (!$billingItem) {
    //         return json(['code' => 404, 'msg' => '订单项未找到', 'data' => null]);
    //     }

    //     // 获取该订单项的治疗记录项目列表
    //     $treatRecordItems = Db::name('treat_record_item')
    //         ->where('billing_item_id', $billingItemId)
    //         ->where('del_flag', 0)
    //         ->field('
    //             id, treat_record_id, billing_id, treat_count, first_deduct_flag,
    //             project_confirmation_flag, perform_price, perform_given_balance,
    //             perform_card_money, perform_other_virtual_money, perform_score_money,
    //             manual_num, perform_given_equity, create_date
    //         ')
    //         ->order('create_date', 'desc')
    //         ->select();

    //     // 统计治疗次数
    //     $totalTreatCount = 0;
    //     $totalPerformPrice = 0;
        
    //     if (!empty($treatRecordItems)) {
    //         $treatRecordItems = $treatRecordItems->toArray();
    //         foreach ($treatRecordItems as &$tri) {
    //             $totalTreatCount += $tri['treat_count'];
    //             $totalPerformPrice += $tri['perform_price'];

    //             $tri['first_deduct_flag'] = (bool) $tri['first_deduct_flag'];
    //             $tri['project_confirmation_flag'] = (bool) $tri['project_confirmation_flag'];
    //             $tri['perform_price'] = (float) $tri['perform_price'];
    //             $tri['perform_given_balance'] = (float) $tri['perform_given_balance'];
    //             $tri['perform_card_money'] = (float) $tri['perform_card_money'];
    //             $tri['perform_other_virtual_money'] = (float) $tri['perform_other_virtual_money'];
    //             $tri['perform_score_money'] = (float) $tri['perform_score_money'];
    //             $tri['perform_given_equity'] = (float) $tri['perform_given_equity'];
    //         }
    //         unset($tri);
    //     }

    //     // 构建返回数据
    //     $result = [
    //         'billing_item' => [
    //             'id' => $billingItem['id'],
    //             'billing_item_name' => $billingItem['billing_item_name'],
    //             'billing_item_type' => $billingItem['billing_item_type'],
    //             'number_of_time' => (int) $billingItem['number_of_time'],
    //             'total_price' => (float) $billingItem['total_price'],
    //             'residue_num' => (float) $billingItem['residue_num'],
    //             'finish_flag' => (bool) $billingItem['finish_flag'],
    //             'treat_project_flag' => (bool) $billingItem['treat_project_flag'],
    //         ],
    //         'treat_summary' => [
    //             'total_treat_count' => $totalTreatCount,
    //             'total_record_count' => count($treatRecordItems),
    //             'total_perform_price' => $totalPerformPrice,
    //             'first_treat_time' => $treatRecordItems ? end($treatRecordItems)['create_date'] : null,
    //             'last_treat_time' => $treatRecordItems ? $treatRecordItems[0]['create_date'] : null,
    //         ],
    //         'treat_record_items' => $treatRecordItems
    //     ];

    //     // 缓存5分钟
    //     cache($cacheKey, $result, 300);

    //     return json(['code' => 200, 'msg' => 'ok', 'data' => $result]);
    // }
}
