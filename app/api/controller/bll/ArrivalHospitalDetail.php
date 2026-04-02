<?php

namespace app\api\controller\bll;

use think\facade\Db;

class ArrivalHospitalDetail extends BaseController
{
    /**
     * 到院明细列表 - 优化版本
     */
    public function getlist()
    {
        $page = max(1, (int) input('get.page/d', 1));
        $limit = max(1, min(1000, (int) input('get.limit/d', 100)));
        $offset = ($page - 1) * $limit;

        // 查询条件
        $name = input('get.name/s', '');
        $phone = input('get.phone/s', '');
        $member_no = input('get.member_no/s', '');
        $startDate = input('get.start_date/s', '');
        $endDate = input('get.end_date/s', '');
        $sourceType = input('get.source_type/s', '');

        $where = [];
        
        // 客户姓名
        if ($name !== '') {
            $where[] = ['customer_name', 'like', $name . '%'];
        }

        // 手机号查询
        if ($phone !== '') {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            if ($cleanPhone !== '') {
                $phoneLen = strlen($cleanPhone);
                if ($phoneLen >= 11) {
                    // 完整手机号，使用精确匹配
                    $where[] = ['main_phone', '=', $cleanPhone];
                } else {
                    // 部分手机号，使用模糊匹配
                    $where[] = ['main_phone', 'like', '%' . $cleanPhone . '%'];
                }
            }
        }

        if ($member_no !== '') {
            $where[] = ['member_no', '=', $member_no];
        }

        // 到院日期范围
        if ($startDate !== '') {
            $where[] = ['arrival_date', '>=', $startDate . ' 00:00:00'];
        }
        if ($endDate !== '') {
            $where[] = ['arrival_date', '<=', $endDate . ' 23:59:59'];
        }

        // 来源类型
        if ($sourceType !== '') {
            $where[] = ['source_type', '=', $sourceType];
        }

        $tableName = 'arrival_hospital_detail_view';
        
        // 构建缓存key
        $cacheKey = 'arrival_hospital_count_' . md5(json_encode($where));
        
        // 缓存COUNT查询结果（1分钟有效）
        $total = cache($cacheKey) ?: null;
        if ($total === null) {
            $total = Db::name($tableName)
                ->where($where)
                ->where('del_flag', 0)
                ->count('id');
            cache($cacheKey, $total, 600);
        }

        // 只查询页面需要的核心字段，减少数据传输
        $list = Db::name($tableName)
            ->where($where)
            ->where('del_flag', 0)
            ->field('id,arrival_date,customer_name,customer_type_name,cus_flow_no,
                member_no,member_level_name,reception_type_name,
                sys_user_name,source_type_name,source_path_name,
                pre_user_name,pre_dept_name,exclusive_service_name,
                age,birthday,first_treat_date,first_cost_date,
                latest_arrival_date,latest_cost_date,
                is_pay,is_refund,total_performance_money,
                billing_act_received_money,deposit_act_received_money,
                stored_act_received_money,total_act_refund_money,
                main_phone,integral')
            ->order('arrival_date', 'desc')
            ->limit($offset, $limit)
            // ->fetchSql()
            ->select();

        // 对手机号进行脱敏处理
        if (!empty($list)) {
            $list = $list->toArray();
            foreach ($list as &$row) {
                if (!empty($row['main_phone']) && strlen($row['main_phone']) >= 11) {
                    $row['main_phone'] = substr($row['main_phone'], 0, 3) . '****' . substr($row['main_phone'], 7);
                }
            }
            unset($row);
        }

        return json([
            'total' => $total,
            'data' => $list
        ]);
    }
}
