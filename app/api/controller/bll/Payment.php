<?php

namespace app\api\controller\bll;

use think\facade\Db;

class Payment extends BaseController
{
    public function getlist()
    {
        $page = max(1, (int) input('get.page/d', 1));
        $limit = max(1, min(1000, (int) input('get.limit/d', 20)));
        $offset = ($page - 1) * $limit;

        $paymentNo = input('get.payment_no/s', '');
        $billingNo = input('get.billing_no/s', '');
        $customerName = input('get.customer_name/s', '');
        $customerPhone = input('get.customer_phone/s', '');
        $chargeUserName = input('get.charge_user_name/s', '');
        $status = input('get.status/s', '');
        $payMark = input('get.pay_mark/s', '');

        // 优化：统一管理查询条件
        $where = [];
        $phoneRaw = '';
        // 始终关联customer表以获取客户手机号
        $joinCustomer = true;
        $joinChargeUser = false;

        if ($paymentNo !== '') {
            $where[] = ['p.payment_no', 'like', '%' . $paymentNo . '%'];
        }
        if ($billingNo !== '') {
            $where[] = ['p.billing_no', 'like', '%' . $billingNo . '%'];
        }
        if ($customerName !== '') {
            $where[] = ['c.name', 'like', $customerName . '%'];
        }
        $needMaskPhone = true; // 默认需要脱敏
        if ($customerPhone !== '') {
            $needMaskPhone = false; // 搜索时不脱敏
            $cleanPhone = $customerPhone;
            if ($cleanPhone !== '') {
                // 使用精确匹配提高性能
                $phoneRaw = "c.main_phone = '" . addslashes($cleanPhone) . "'";
            }
        }
        if ($chargeUserName !== '') {
            $joinChargeUser = true;
            $where[] = ['cu.name', 'like', $chargeUserName . '%'];
        }
        if ($status !== '') {
            $where[] = ['p.status', '=', $status];
        }
        if ($payMark !== '') {
            $where[] = ['p.pay_mark', '=', $payMark];
        }

        // 缓存键
        $cacheKey = 'payment_count_' . md5(json_encode($where) . $joinCustomer . $joinChargeUser . $phoneRaw);

        // 优化：先计算总数
        $total = cache($cacheKey) ?: null;
        if ($total === null) {
            $countQuery = Db::name('payment')->alias('p');
            if ($joinCustomer) {
                $countQuery->leftJoin('customer_detail_view c', 'p.customer_id = c.id');
            }
            if ($joinChargeUser) {
                $countQuery->leftJoin('sys_user cu', 'p.charge_user_id = cu.id');
            }
            if (!empty($where)) {
                $countQuery->where($where);
            }
            if ($phoneRaw !== '') {
                $countQuery->whereRaw($phoneRaw);
            }
            $total = $countQuery->count('p.id');
            cache($cacheKey, $total, 60);
        }

        // 优化：只执行一次完整查询
        $listQuery = Db::name('payment')->alias('p')
            ->leftJoin('customer_detail_view c', 'p.customer_id = c.id')
            ->leftJoin('sys_user pu', 'p.pay_billing_user_id = pu.id')
            ->leftJoin('sys_user cu', 'p.charge_user_id = cu.id');
        if (!empty($where)) {
            $listQuery->where($where);
        }
        if ($phoneRaw !== '') {
            $listQuery->whereRaw($phoneRaw);
        }
        $list = $listQuery
            ->field('p.id,p.payment_no,p.billing_id,p.billing_no,p.customer_id,p.pay_mark,p.billing_explain,p.charge_user_id,p.charge_time,p.charge_year,p.remark,p.pay_way,p.should_pay_money,p.deposit_money,p.actual_pay_money,p.invoice_flag,p.invoice_money,p.should_refund_money,p.approval_flag,p.step,p.approval_status,p.status,p.flow_no,p.billing_record_id,p.create_date,p.update_date,p.create_by,p.update_by,p.del_flag,p.tenant_id,p.import_type,p.charge_user_name,p.first_charge_flag,p.billing_user_id,p.force_approve_flag,p.data_source,p.print_invoice_flag,p.actual_refund_money,p.invoice_money,p.remark,p.auto_flag,p.actual_recorded_time,p.last_part_print_time,p.cus_flow_no,p.type,p.not_just_billing_flag,p.from_a_flag,p.from_b_update,p.sub_balance_of_cus_asset,c.name as customer_name,c.main_phone as customer_phone,pu.name as pay_billing_user_name,cu.name as charge_user_name')
            ->order('p.create_date', 'desc')
            ->limit($offset, $limit)
            ->select();

        // 对手机号进行脱敏处理：中间4位显示为****
        if (!empty($list) && $needMaskPhone) {
            $list = $list->toArray();
            foreach ($list as &$row) {
                if (!empty($row['customer_phone']) && strlen($row['customer_phone']) >= 11) {
                    $row['customer_phone'] = substr($row['customer_phone'], 0, 3) . '****' . substr($row['customer_phone'], 7);
                }
            }
        }

        return json([
            'total' => $total,
            'data' => $list
        ]);
    }
}
