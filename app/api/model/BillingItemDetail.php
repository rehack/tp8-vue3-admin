<?php

namespace app\api\model;

use think\Model;

class BillingItemDetail extends Model
{
    // 设置表名
    protected $name = 'billing_item_detail';
    
    // 设置主键
    protected $pk = 'id';
    
    // 默认时间戳字段
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_date';
    protected $updateTime = 'update_date';
    
    // 追加属性
    protected $append = [
        'import_type_text',
        'can_free_flag_text',
        'free_flag_text',
        'can_discount_flag_text',
        'operation_flag_text',
        'special_flag_text',
        'course_flag_text',
        'required_flag_text',
        'channel_rebate_flag_text',
        'pre_treat_data_flag_text',
        'same_project_treat_flag_text',
        'can_not_limit_deduct_text',
        'same_project_pay_flag_text',
        'donation_billing_item_id_text',
        'copy_flag_text',
        'fix_flag_text',
        'mutex_flag_text',
        'allow_donation_flag_text',
    ];
    
    /**
     * 获取导入标识文本
     */
    public function getImportTypeTextAttr($value, $data)
    {
        $types = [
            0 => '正常',
            1 => '导入',
        ];
        return $types[$data['import_type']] ?? '未知';
    }
    
    /**
     * 获取是否可赠送文本
     */
    public function getCanFreeFlagTextAttr($value, $data)
    {
        return $data['can_free_flag'] ? '是' : '否';
    }
    
    /**
     * 获取是否已赠送文本
     */
    public function getFreeFlagTextAttr($value, $data)
    {
        return $data['free_flag'] ? '是' : '否';
    }
    
    /**
     * 获取是否可打折文本
     */
    public function getCanDiscountFlagTextAttr($value, $data)
    {
        return $data['can_discount_flag'] ? '是' : '否';
    }
    
    /**
     * 获取是否是手术类文本
     */
    public function getOperationFlagTextAttr($value, $data)
    {
        return $data['operation_flag'] ? '是' : '否';
    }
    
    /**
     * 获取是否是特色项目文本
     */
    public function getSpecialFlagTextAttr($value, $data)
    {
        return $data['special_flag'] ? '是' : '否';
    }
    
    /**
     * 获取是否是补疗程文本
     */
    public function getCourseFlagTextAttr($value, $data)
    {
        return $data['course_flag'] ? '是' : '否';
    }
    
    /**
     * 获取是否是必选项目文本
     */
    public function getRequiredFlagTextAttr($value, $data)
    {
        return $data['required_flag'] ? '是' : '否';
    }
    
    /**
     * 获取是否参与渠道返佣文本
     */
    public function getChannelRebateFlagTextAttr($value, $data)
    {
        return $data['channel_rebate_flag'] ? '是' : '否';
    }
    
    /**
     * 获取未付款下是否已有待治疗数据文本
     */
    public function getPreTreatDataFlagTextAttr($value, $data)
    {
        return $data['pre_treat_data_flag'] ? '是' : '否';
    }
    
    /**
     * 获取是否同项目设置文本
     */
    public function getSameProjectTreatFlagTextAttr($value, $data)
    {
        return $data['same_project_treat_flag'] ? '是' : '否';
    }
    
    /**
     * 获取是否可以无限划扣文本
     */
    public function getCanNotLimitDeductTextAttr($value, $data)
    {
        return $data['can_not_limit_deduct'] ? '是' : '否';
    }
    
    /**
     * 获取是否同项目设置（缴费后套餐有效期）文本
     */
    public function getSameProjectPayFlagTextAttr($value, $data)
    {
        return $data['same_project_pay_flag'] ? '是' : '否';
    }
    
    /**
     * 获取是否已复单文本
     */
    public function getCopyFlagTextAttr($value, $data)
    {
        return $data['copy_flag'] ? '已复单' : '未复单';
    }
    
    /**
     * 获取是否是补录订单项文本
     */
    public function getFixFlagTextAttr($value, $data)
    {
        return $data['fix_flag'] ? '是' : '否';
    }
    
    /**
     * 获取是否是互斥项目文本
     */
    public function getMutexFlagTextAttr($value, $data)
    {
        return $data['mutex_flag'] ? '是' : '否';
    }
    
    /**
     * 获取是否可转赠文本
     */
    public function getAllowDonationFlagTextAttr($value, $data)
    {
        return $data['allow_donation_flag'] ? '是' : '否';
    }
    
    /**
     * 获取订单项明细关联
     */
    public function billingItem()
    {
        return $this->belongsTo('BillingItem', 'billing_item_id', 'id');
    }
    
    /**
     * 获取订单关联
     */
    public function billing()
    {
        return $this->belongsTo('Billing', 'billing_id', 'id');
    }
    
    /**
     * 格式化金额字段
     */
    public function getUnitOriginalPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getDiscountAttr($value)
    {
        return (float) $value;
    }
    
    public function getAnestheticPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getSubPayMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getSubGivenBalanceAttr($value)
    {
        return (float) $value;
    }
    
    public function getSubCardMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getSubOtherVirtualMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getSubScoreMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getOneManualPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getSubGivenEquityMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getFixedFairValueAttr($value)
    {
        return (float) $value;
    }
    
    public function getFairValueAttr($value)
    {
        return (float) $value;
    }
    
    public function getResidualFairValueAttr($value)
    {
        return (float) $value;
    }
    
    public function getDifferenceFairValueAttr($value)
    {
        return (float) $value;
    }
    
    public function getAllocateRatioAttr($value)
    {
        return (float) $value;
    }
    
    public function getOneDeductPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getOverOneDeductPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getOverOneManualPriceAttr($value)
    {
        return (float) $value;
    }
}
