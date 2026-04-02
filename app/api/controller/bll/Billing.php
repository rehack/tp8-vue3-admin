<?php

namespace app\api\controller\bll;

use think\facade\Db;

class Billing extends BaseController
{
    /**
     * 获取订单列表
     */
    public function getbilllist()
    {
        $page = max(1, (int) input('get.page/d', 1));
        $limit = max(1, min(1000, (int) input('get.limit/d', 20)));
        $offset = ($page - 1) * $limit;

        $billingNo = input('get.billing_no/s', '');
        $customerId = input('get.customer_id/s', '');
        $customerName = input('get.customer_name/s', '');
        $customerPhone = input('get.customer_phone/s', '');

        // 构建基础查询条件（billing表）
        $billingWhere = [
            ['del_flag', '=', 0]
        ];
        
        // 手机号查询 - 直接用子查询方式
        $needMaskPhone = true;
        $customerIds = [];
        
        if ($customerPhone !== '') {
            // 根据手机号查询客户ID
            $needMaskPhone = false;
            $customerIds = Db::name('customer_detail_view')
                ->where('main_phone', 'like', '%' . $customerPhone . '%')
                ->column('id');
            
            // 如果没有找到客户，直接返回空结果
            if (empty($customerIds)) {
                return json([
                    'total' => 0,
                    'data' => []
                ]);
            }
        }
        
        // 客户姓名查询
        if ($customerName !== '') {
            $nameCustomerIds = Db::name('customer_detail_view')
                ->where('name', 'like', '%' . $customerName . '%')
                ->column('id');
            
            if (empty($nameCustomerIds)) {
                return json([
                    'total' => 0,
                    'data' => []
                ]);
            }
            
            // 合并客户ID
            if (!empty($customerIds)) {
                $customerIds = array_intersect($customerIds, $nameCustomerIds);
            } else {
                $customerIds = $nameCustomerIds;
            }
            
            if (empty($customerIds)) {
                return json([
                    'total' => 0,
                    'data' => []
                ]);
            }
        }

        if ($billingNo !== '') {
            $billingWhere[] = ['billing_no', 'like', '%' . $billingNo . '%'];
        }
        if ($customerId !== '') {
            $billingWhere[] = ['customer_id', '=', $customerId];
        }
        if (!empty($customerIds)) {
            $billingWhere[] = ['customer_id', 'in', $customerIds];
        }

        // 使用子查询优化：先获取ID列表，再查询详情
        $billingQuery = Db::name('billing')
            ->where($billingWhere)
            ->field('id')
            ->order('create_date', 'desc');

        // 分页获取ID列表
        $billingIds = $billingQuery->limit($offset, $limit)->column('id');
        
        // 获取总数
        $total = Db::name('billing')
            ->where($billingWhere)
            ->count('id');

        if (empty($billingIds)) {
            return json([
                'total' => $total,
                'data' => []
            ]);
        }

        // 根据ID列表查询订单详情
        $list = Db::name('billing')
            ->alias('b')
            ->leftJoin('customer_detail_view c', 'b.customer_id = c.id')
            ->leftJoin('sys_user u', 'b.billing_user_id = u.id')
            ->whereIn('b.id', $billingIds)
            ->field('
                b.id,
                b.billing_no,
                b.customer_id,
                b.billing_user_id,
                b.received_money,
                b.total_price,
                b.original_price,
                b.discount_price,
                b.deposit_price,
                b.free_price,
                b.status,
                b.approval_status,
                b.create_date,
                b.update_date,
                c.name as customer_name,
                c.main_phone as customer_phone,
                u.name as billing_user_name,
                b.remark,
                b.course_price
            ')
            ->order('b.create_date', 'desc')
            ->select();

        // 对手机号进行脱敏处理 - 必须先转为数组
        if (!empty($list) && $needMaskPhone) {
            $list = $list->toArray();  // 关键：转换为数组
            foreach ($list as &$row) {
                if (!empty($row['customer_phone']) && strlen($row['customer_phone']) >= 11) {
                    $row['customer_phone'] = substr($row['customer_phone'], 0, 3) . '****' . substr($row['customer_phone'], 7);
                }
            }
            unset($row);
        }

        return json([
            'total' => $total,
            'data' => $list
        ]);
    }

    /**
     * 获取订单详情
     */
    public function detail()
    {
        $id = input('get.id/s', '');
        if (!$id) {
            $id = input('get.id/d', 0);
        }
        
        if (!$id) {
            return json(['code' => 400, 'msg' => '缺少订单id', 'data' => null]);
        }

        // 使用缓存
        $cacheKey = 'billing_detail_' . md5($id);
        $cacheData = cache($cacheKey);
        
        if ($cacheData !== false && $cacheData !== null) {
            return json(['code' => 200, 'msg' => 'ok', 'data' => $cacheData]);
        }

        try {
            // 查询订单基本信息
            $billing = Db::name('billing')
                ->where('id', $id)
                ->where('del_flag', 0)
                ->find();
            
            if (!$billing && is_numeric($id)) {
                $billing = Db::name('billing')
                    ->where('id', (string)$id)
                    ->where('del_flag', 0)
                    ->find();
            }

            if (!$billing) {
                return json(['code' => 404, 'msg' => '订单未找到', 'data' => null]);
            }

            // 关联客户信息
            if (!empty($billing['customer_id'])) {
                $customer = Db::name('customer_detail_view')
                    ->where('id', $billing['customer_id'])
                    ->find();
                if ($customer) {
                    $billing['customer_name'] = $customer['name'] ?? '';
                    // 手机号脱敏处理
                    $billing['customer_phone'] = '';
                    if (!empty($customer['main_phone'])) {
                        if (strlen($customer['main_phone']) >= 11) {
                            $billing['customer_phone'] = substr($customer['main_phone'], 0, 3) . '****' . substr($customer['main_phone'], 7);
                        } else {
                            $billing['customer_phone'] = $customer['main_phone'];
                        }
                    }
                    $billing['customer_sex'] = $customer['sex'] ?? '';
                    $billing['customer_age'] = $customer['age'] ?? '';
                }
            }

            // 开单人
            if (!empty($billing['billing_user_id'])) {
                $user = Db::name('sys_user')
                    ->where('id', $billing['billing_user_id'])
                    ->find();
                $billing['billing_user_name'] = $user['name'] ?? '';
            }

            // 赠送科室
            if (!empty($billing['free_department_id'])) {
                $dept = Db::name('sys_dept')
                    ->where('id', $billing['free_department_id'])
                    ->find();
                $billing['free_department_name'] = $dept['name'] ?? '';
            }
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '查询错误: ' . $e->getMessage(), 'data' => null]);
        }

        // 处理JSON字段
        $jsonFields = ['next_checker', 'check_details', 'approval_setting'];
        foreach ($jsonFields as $field) {
            if (isset($billing[$field]) && $billing[$field]) {
                $billing[$field] = json_decode($billing[$field], true);
            }
        }

        // 处理布尔字段
        $boolFields = ['approval_flag', 'give_flag', 'free_flag', 'print_flag', 'force_approve_flag', 'bill_back_flag', 'order_clear_flag', 'brown_south_error_flag'];
        foreach ($boolFields as $field) {
            if (isset($billing[$field])) {
                $billing[$field] = (bool) $billing[$field];
            }
        }

        // 获取订单关联的赠送订单信息
        if ($billing['give_billing_id']) {
            $giveBilling = Db::name('billing')
                ->where('id', $billing['give_billing_id'])
                ->field('id,billing_no,customer_id')
                ->find();
            $billing['give_billing_info'] = $giveBilling;
        }

        // 获取订单关联的首次赠送订单信息
        if ($billing['give_first_billing_id']) {
            $giveFirstBilling = Db::name('billing')
                ->where('id', $billing['give_first_billing_id'])
                ->field('id,billing_no,customer_id')
                ->find();
            $billing['give_first_billing_info'] = $giveFirstBilling;
        }

        // 缓存5分钟
        cache($cacheKey, $billing, 300);

        return json(['code' => 200, 'msg' => 'ok', 'data' => $billing]);
    }

    /**
     * 获取订单项列表
     */
    public function getBillingItemList()
    {
        $page = max(1, (int) input('get.page/d', 1));
        $limit = max(1, min(500, (int) input('get.limit/d', 100)));
        $offset = ($page - 1) * $limit;

        $billingId = input('get.billing_id/s', '');
        $customerId = input('get.customer_id/s', '');
        $billingItemName = input('get.billing_item_name/s', '');
        $billingItemType = input('get.billing_item_type/s', '');
        $billingStatus = input('get.billing_status/s', '');

        if (!$billingId && !$customerId) {
            return json(['code' => 400, 'msg' => '缺少订单id或客户id', 'data' => null]);
        }

        $where = [
            ['bi.del_flag', '=', 0]
        ];

        if ($billingId !== '') {
            $where[] = ['bi.billing_id', '=', $billingId];
        }
        if ($customerId !== '') {
            $where[] = ['bi.customer_id', '=', $customerId];
        }
        if ($billingItemName !== '') {
            $where[] = ['bi.billing_item_name', 'like', '%' . $billingItemName . '%'];
        }
        if ($billingItemType !== '') {
            $where[] = ['bi.billing_item_type', '=', $billingItemType];
        }
        if ($billingStatus !== '') {
            $where[] = ['bi.billing_status', '=', $billingStatus];
        }

        // 总数
        $total = Db::name('billing_item')
            ->alias('bi')
            ->where($where)
            ->count('bi.id');

        // 列表
        $list = Db::name('billing_item')
            ->alias('bi')
            ->leftJoin('sys_dept d', 'bi.department_id = d.id')
            ->where($where)
            ->field('
                bi.id,
                bi.create_date,
                bi.update_date,
                bi.billing_id,
                bi.billing_no,
                bi.billing_date,
                bi.billing_time,
                bi.billing_user_id,
                bi.billing_user_name,
                bi.billing_status,
                bi.billing_item_type,
                bi.billing_item_name,
                bi.unit_price,
                bi.number_of_time,
                bi.total_price,
                bi.total_perform_price,
                bi.received_money,
                bi.deduct_given_balance,
                bi.deduct_card_money,
                bi.already_refund_money,
                bi.discount_price,
                bi.free_detail_price,
                bi.discount_detail_price,
                bi.course_detail_price,
                bi.performance_statistics_flag,
                bi.department_id,
                bi.department_name,
                bi.doctor_id,
                bi.doctor_name,
                bi.package_billing_item_id,
                bi.package_id,
                bi.package_name,
                bi.residue_num,
                bi.near_treat_time,
                bi.finish_flag,
                bi.deduct_flag,
                bi.verification_flag,
                bi.expiry_date,
                bi.treat_project_flag,
                bi.billing_item_type_category,
                bi.report_calcu_enum,
                bi.remark,
                d.name as dept_name
            ')
            ->order('bi.billing_time', 'desc')
            ->order('bi.create_date', 'desc')
            ->limit($offset, $limit)
            ->select();

        // 处理字段 - 先转为数组
        if (!empty($list)) {
            $list = $list->toArray();
            foreach ($list as &$item) {
                $item['performance_statistics_flag'] = (bool) $item['performance_statistics_flag'];
                $item['finish_flag'] = (bool) $item['finish_flag'];
                $item['deduct_flag'] = (bool) $item['deduct_flag'];
                $item['verification_flag'] = (bool) $item['verification_flag'];
                $item['treat_project_flag'] = (bool) $item['treat_project_flag'];

                $item['unit_price'] = (float) $item['unit_price'];
                $item['total_price'] = (float) $item['total_price'];
                $item['total_perform_price'] = (float) $item['total_perform_price'];
                $item['received_money'] = (float) $item['received_money'];
                $item['deduct_given_balance'] = (float) $item['deduct_given_balance'];
                $item['deduct_card_money'] = (float) $item['deduct_card_money'];
                $item['already_refund_money'] = (float) $item['already_refund_money'];
                $item['discount_price'] = (float) $item['discount_price'];
                $item['free_detail_price'] = (float) $item['free_detail_price'];
                $item['discount_detail_price'] = (float) $item['discount_detail_price'];
                $item['course_detail_price'] = (float) $item['course_detail_price'];
                $item['residue_num'] = (float) $item['residue_num'];
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
     * 获取订单项详情
     */
    public function getBillingItemDetail()
    {
        $billingItemId = input('get.billing_item_id/s', '');
        if (!$billingItemId) {
            return json(['code' => 400, 'msg' => '缺少订单项id', 'data' => null]);
        }

        // 缓存键
        $cacheKey = 'billing_item_detail_' . $billingItemId;
        $cacheData = cache($cacheKey);
        
        if ($cacheData !== false) {
            return json(['code' => 200, 'msg' => 'ok', 'data' => $cacheData]);
        }

        // 查询订单项基本信息
        $billingItem = Db::name('billing_item')
            ->alias('bi')
            ->leftJoin('billing b', 'bi.billing_id = b.id')
            ->where('bi.id', $billingItemId)
            ->where('bi.del_flag', 0)
            ->field('
                bi.*,
                b.billing_no as parent_billing_no,
                b.status as parent_billing_status
            ')
            ->find();

        if (!$billingItem) {
            return json(['code' => 404, 'msg' => '订单项未找到', 'data' => null]);
        }

        // 查询订单项详细信息
        $billingItemDetail = Db::name('billing_item_detail')
            ->where('billing_item_id', $billingItemId)
            ->where('del_flag', 0)
            ->find();

        if ($billingItemDetail) {
            $billingItem['billing_item_detail'] = $billingItemDetail;
        }

        // 获取该订单项关联的治疗记录项目
        $treatRecordItems = Db::name('treat_record_item')
            ->where('billing_item_id', $billingItemId)
            ->where('del_flag', 0)
            ->field('
                id, treat_record_id, billing_id, treat_count, first_deduct_flag,
                project_confirmation_flag, perform_price, perform_given_balance,
                perform_card_money, perform_other_virtual_money, perform_score_money,
                manual_num, perform_given_equity, create_date
            ')
            ->select();

        if (!empty($treatRecordItems)) {
            $treatRecordItems = $treatRecordItems->toArray();
            foreach ($treatRecordItems as &$tri) {
                $tri['first_deduct_flag'] = (bool) $tri['first_deduct_flag'];
                $tri['project_confirmation_flag'] = (bool) $tri['project_confirmation_flag'];
                $tri['perform_price'] = (float) $tri['perform_price'];
                $tri['perform_given_balance'] = (float) $tri['perform_given_balance'];
                $tri['perform_card_money'] = (float) $tri['perform_card_money'];
                $tri['perform_other_virtual_money'] = (float) $tri['perform_other_virtual_money'];
                $tri['perform_score_money'] = (float) $tri['perform_score_money'];
                $tri['perform_given_equity'] = (float) $tri['perform_given_equity'];
            }
            unset($tri);
        }

        $billingItem['treat_record_items'] = $treatRecordItems;

        // 布尔字段转换
        $billingItem['performance_statistics_flag'] = (bool) $billingItem['performance_statistics_flag'];
        $billingItem['finish_flag'] = (bool) $billingItem['finish_flag'];
        $billingItem['deduct_flag'] = (bool) $billingItem['deduct_flag'];
        $billingItem['verification_flag'] = (bool) $billingItem['verification_flag'];
        $billingItem['treat_project_flag'] = (bool) $billingItem['treat_project_flag'];

        // 金额字段转换
        $billingItem['unit_price'] = (float) $billingItem['unit_price'];
        $billingItem['total_price'] = (float) $billingItem['total_price'];
        $billingItem['total_perform_price'] = (float) $billingItem['total_perform_price'];
        $billingItem['received_money'] = (float) $billingItem['received_money'];
        $billingItem['residue_num'] = (float) $billingItem['residue_num'];

        // 缓存5分钟
        cache($cacheKey, $billingItem, 300);

        return json(['code' => 200, 'msg' => 'ok', 'data' => $billingItem]);
    }

    /**
     * 获取订单项详情列表
     */
    public function getBillingItemDetailList()
    {
        $page = max(1, (int) input('get.page/d', 1));
        $limit = max(1, min(500, (int) input('get.limit/d', 100)));
        $offset = ($page - 1) * $limit;

        $billingId = input('get.billing_id/s', '');
        $billingItemId = input('get.billing_item_id/s', '');

        if (!$billingId && !$billingItemId) {
            return json(['code' => 400, 'msg' => '缺少订单id或订单项id', 'data' => null]);
        }

        $where = [
            ['bid.del_flag', '=', 0]
        ];

        if ($billingId !== '') {
            $where[] = ['bid.billing_id', '=', $billingId];
        }
        if ($billingItemId !== '') {
            $where[] = ['bid.billing_item_id', '=', $billingItemId];
        }

        // 总数
        $total = Db::name('billing_item_detail')
            ->alias('bid')
            ->where($where)
            ->count('bid.id');

        // 列表
        $list = Db::name('billing_item_detail')
            ->alias('bid')
            ->leftJoin('billing_item bi', 'bid.billing_item_id = bi.id')
            ->where($where)
            ->field('
                bid.*,
                bi.billing_item_name,
                bi.billing_item_type,
                bi.unit_price,
                bi.number_of_time,
                bi.total_price
            ')
            ->order('bid.create_date', 'desc')
            ->limit($offset, $limit)
            ->select();

        // 处理字段 - 先转为数组
        if (!empty($list)) {
            $list = $list->toArray();
            foreach ($list as &$item) {
                $item['can_free_flag'] = (bool) $item['can_free_flag'];
                $item['free_flag'] = (bool) $item['free_flag'];
                $item['can_discount_flag'] = (bool) $item['can_discount_flag'];
                $item['operation_flag'] = (bool) $item['operation_flag'];
                $item['special_flag'] = (bool) $item['special_flag'];
                $item['course_flag'] = (bool) $item['course_flag'];
                $item['required_flag'] = (bool) $item['required_flag'];
                $item['channel_rebate_flag'] = (bool) $item['channel_rebate_flag'];
                $item['pre_treat_data_flag'] = (bool) $item['pre_treat_data_flag'];
                $item['same_project_treat_flag'] = (bool) $item['same_project_treat_flag'];
                $item['can_not_limit_deduct'] = (bool) $item['can_not_limit_deduct'];
                $item['same_project_pay_flag'] = (bool) $item['same_project_pay_flag'];
                $item['copy_flag'] = (bool) $item['copy_flag'];
                $item['fix_flag'] = (bool) $item['fix_flag'];
                $item['mutex_flag'] = (bool) $item['mutex_flag'];
                $item['allow_donation_flag'] = (bool) $item['allow_donation_flag'];
                $item['exe_performance_statistics_flag'] = (bool) $item['exe_performance_statistics_flag'];

                $item['unit_original_price'] = (float) $item['unit_original_price'];
                $item['discount'] = (float) $item['discount'];
                $item['anesthetic_price'] = (float) $item['anesthetic_price'];
                $item['sub_pay_money'] = (float) $item['sub_pay_money'];
                $item['sub_given_balance'] = (float) $item['sub_given_balance'];
                $item['sub_card_money'] = (float) $item['sub_card_money'];
                $item['sub_other_virtual_money'] = (float) $item['sub_other_virtual_money'];
                $item['sub_score_money'] = (float) $item['sub_score_money'];
                $item['one_manual_price'] = (float) $item['one_manual_price'];
                $item['sub_given_equity_money'] = (float) $item['sub_given_equity_money'];
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
}
