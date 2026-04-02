<?php

namespace app\api\model;

use think\Model;

class CustomerDetailView extends Model
{
    // 设置表名（视图）
    protected $name = 'customer_detail_view';
    
    // 设置主键
    protected $pk = 'id';
    
    // 视图不需要时间戳
    protected $autoWriteTimestamp = false;
    
    // 追加属性
    protected $append = [
        'del_flag_text',
        'type_text',
        'sex_text',
        'sure_type_text',
        'customer_pool_type_text',
        'source_type_text',
        'create_type_text',
        'married_flag_text',
        'fertility_flag_text',
        'allergy_flag_text',
        'plastic_flag_text',
        'vip_grade_text',
        'active_status_text',
        'context_text',
    ];
    
    /**
     * 获取删除标志文本
     */
    public function getDelFlagTextAttr($value, $data)
    {
        return $data['del_flag'] ? '已删除' : '正常';
    }
    
    /**
     * 获取客户类型文本
     */
    public function getTypeTextAttr($value, $data)
    {
        $types = [
            0 => '初诊',
            1 => '复诊',
            2 => '再消费',
        ];
        return $types[$data['type']] ?? '未知';
    }
    
    /**
     * 获取性别文本
     */
    public function getSexTextAttr($value, $data)
    {
        $sex = [
            1 => '男',
            2 => '女',
        ];
        return $sex[$data['sex']] ?? '未知';
    }
    
    /**
     * 获取确认状态文本
     */
    public function getSureTypeTextAttr($value, $data)
    {
        $types = [
            0 => '未确认',
            1 => '已确认',
            2 => '待确认',
        ];
        return $types[$data['sure_type']] ?? '未知';
    }
    
    /**
     * 获取客户池类型文本
     */
    public function getCustomerPoolTypeTextAttr($value, $data)
    {
        $types = [
            1 => '正式池',
            2 => '临时池',
            3 => '待定池',
        ];
        return $types[$data['customer_pool_type']] ?? '未知';
    }
    
    /**
     * 获取来源类型文本
     */
    public function getSourceTypeTextAttr($value, $data)
    {
        $types = [
            0 => '线上',
            1 => '线下',
            2 => '老带新',
            3 => '其他',
        ];
        return $types[$data['source_type']] ?? '未知';
    }
    
    /**
     * 获取创建类型文本
     */
    public function getCreateTypeTextAttr($value, $data)
    {
        $types = [
            0 => '系统创建',
            1 => '手动创建',
            2 => '导入创建',
        ];
        return $types[$data['create_type']] ?? '未知';
    }
    
    /**
     * 获取婚姻状况文本
     */
    public function getMarriedFlagTextAttr($value, $data)
    {
        $flags = [
            0 => '未婚',
            1 => '已婚',
        ];
        return $flags[$data['married_flag']] ?? '未知';
    }
    
    /**
     * 获取生育状况文本
     */
    public function getFertilityFlagTextAttr($value, $data)
    {
        $flags = [
            0 => '未育',
            1 => '已育',
        ];
        return $flags[$data['fertility_flag']] ?? '未知';
    }
    
    /**
     * 获取过敏标识文本
     */
    public function getAllergyFlagTextAttr($value, $data)
    {
        return $data['allergy_flag'] ? '有过敏' : '无过敏';
    }
    
    /**
     * 获取院外治疗标识文本
     */
    public function getPlasticFlagTextAttr($value, $data)
    {
        return $data['plastic_flag'] ? '有院外治疗' : '无院外治疗';
    }
    
    /**
     * 获取VIP等级文本
     */
    public function getVipGradeTextAttr($value, $data)
    {
        $grades = [
            0 => '普通会员',
            1 => '铜卡会员',
            2 => '银卡会员',
            3 => '金卡会员',
            4 => '钻石会员',
        ];
        return $grades[$data['vip_grade']] ?? '普通会员';
    }
    
    /**
     * 获取活跃状态文本
     */
    public function getActiveStatusTextAttr($value, $data)
    {
        $status = [
            0 => '沉默',
            1 => '活跃',
            2 => '流失',
        ];
        return $status[$data['active_status']] ?? '未知';
    }
    
    /**
     * 获取客户阶段文本
     */
    public function getContextTextAttr($value, $data)
    {
        $contexts = [
            0 => '潜在客户',
            1 => '意向客户',
            2 => '成交客户',
            3 => '复诊客户',
            4 => '忠实客户',
        ];
        return $contexts[$data['context']] ?? '未知';
    }
    
    /**
     * 格式化金额字段
     */
    public function getBalanceAttr($value)
    {
        return (float) $value;
    }
    
    public function getCostAmountAttr($value)
    {
        return (float) $value;
    }
    
    public function getActCostAmountAttr($value)
    {
        return (float) $value;
    }
    
    public function getCostThisYearAttr($value)
    {
        return (float) $value;
    }
    
    public function getActCostAmountYearAttr($value)
    {
        return (float) $value;
    }
    
    public function getFirstCostAmountAttr($value)
    {
        return (float) $value;
    }
    
    public function getOtherCostAmountFirstCostYearAttr($value)
    {
        return (float) $value;
    }
    
    public function getTargetPerformanceAttr($value)
    {
        return (float) $value;
    }
    
    public function getRfmAttr($value)
    {
        return (float) $value;
    }
    
    public function getRScoreAttr($value)
    {
        return (float) $value;
    }
    
    public function getFScoreAttr($value)
    {
        return (float) $value;
    }
    
    public function getMScoreAttr($value)
    {
        return (float) $value;
    }
    
    /**
     * 获取电话号码列表（JSON字段）
     */
    public function getPhoneNumberListAttr($value, $data)
    {
        if (isset($data['phone_number'])) {
            return json_decode($data['phone_number'], true) ?? [];
        }
        return [];
    }
    
    /**
     * 获取昵称（JSON字段）
     */
    public function getNickNameListAttr($value, $data)
    {
        if (isset($data['nick_name'])) {
            return json_decode($data['nick_name'], true) ?? [];
        }
        return [];
    }
}
