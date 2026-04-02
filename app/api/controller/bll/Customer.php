<?php

namespace app\api\controller\bll;

use think\facade\Db;

class Customer extends BaseController
{
    public function getlist()
    {
        $page = max(1, (int) input('get.page/d', 1));
        $limit = max(1, min(1000, (int) input('get.page_size/d', 100)));
        $offset = ($page - 1) * $limit;

        $name = input('get.name/s', '');
        $phone = input('get.phone/s', '');
        $vipNum = input('get.vip_num/s', '');

        // 构建查询条件
        $where = [];
        
        if ($name !== '') {
            $where[] = ['name', 'like', $name . '%'];
        }

        // 手机号查询：支持中间4位模糊匹配（如 152****1652）
        $needMaskPhone = true; // 默认需要脱敏
        if ($phone !== '') {
            $needMaskPhone = false; // 搜索时不脱敏，返回完整号便于确认
            // 提取数字部分
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
            } else if (strpos($phone, '*') !== false) {
                // 包含星号的手机号，如 152****1652
                // 将****替换为4位数字的模糊匹配
                $phonePattern = str_replace('****', '____', $phone);
                $where[] = ['main_phone', 'like', '%' . $phonePattern . '%'];
            }
        }

        // 会员号查询
        if ($vipNum !== '') {
            $where[] = ['vip_num', '=', $vipNum];
        }

        
        $listQuery = Db::name('customer');
        if (!empty($where)) {
            $listQuery->where($where);
        }

        // 缓存COUNT查询结果（1分钟有效）
        $cacheKey = 'customer_count_' . md5(json_encode($where));
        $total = cache($cacheKey); //从缓存中取出的数字是字符串，需要转换为整数，否则前端会解析成字符串，导致分页计算错误，前端分页组件出现异常
        if ($total === null || $total === false) {
            $total = (int) $listQuery->count('id');
            cache($cacheKey, $total, 60);
        } else {
            $total = (int) $total;
        }

        // 直接查询数据
        $list = Db::name('customer')
            ->where($where)
            ->field('id,name,main_phone as phone_number,pre_user_name,source_type,source_path_name,address as area_province,area as area_city,area as area_district,document_id,expand_info,remark,sys_user_name,consul_name,doctor_name,create_date,update_date,cost_amount,act_cost_amount,cost_time,treat_time,latest_arrive_date,real_first_cost_date,first_treat_doctor_id,latest_treat_doctor_id,vip_num,balance')
            ->order('update_date', 'desc')
            ->limit($offset, $limit)
            ->select();

        // 对手机号进行脱敏处理：中间4位显示为****
        if (!empty($list) && $needMaskPhone) {
            $list = $list->toArray();
            foreach ($list as &$row) {
                if (!empty($row['phone_number']) && strlen($row['phone_number']) >= 11) {
                    $row['phone_number'] = substr($row['phone_number'], 0, 3) . '****' . substr($row['phone_number'], 7);
                }
            }
        }
        return json([
            'total' => $total,
            'data' => $list,
            // 'pageSize' => $limit,
            // 'page' => $page,
        ]);
    }
}
