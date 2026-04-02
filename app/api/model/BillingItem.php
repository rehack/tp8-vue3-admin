<?php

namespace app\api\model;

use think\Model;

class BillingItem extends Model
{
    // 设置表名
    protected $name = 'billing_item';
    
    // 设置主键
    protected $pk = 'id';
    
    // 默认时间戳字段
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_date';
    protected $updateTime = 'update_date';
    
    // 隐藏字段
    protected $hidden = [];
    
    // 只读字段
    protected $readonly = [];
    
    // 追加属性
    protected $append = [
        'del_flag_text',
        'billing_status_text',
        'billing_item_type_text',
        'billing_item_type_category_text',
    ];
    
    /**
     * 获取订单状态文本
     */
    public function getBillingStatusTextAttr($value, $data)
    {
        $status = [
            0 => '待缴费',
            1 => '已缴费',
            2 => '已作废',
            3 => '欠费',
            4 => '已完成',
        ];
        return $status[$data['billing_status']] ?? '未知';
    }
    
    /**
     * 获取订单项类型文本
     */
    public function getBillingItemTypeTextAttr($value, $data)
    {
        $types = [
            1 => '项目',
            2 => '套餐',
            3 => '商品',
            4 => '次卡',
            5 => '年卡',
        ];
        return $types[$data['billing_item_type']] ?? '未知';
    }
    
    /**
     * 获取订单项分类文本
     */
    public function getBillingItemTypeCategoryTextAttr($value, $data)
    {
        $categories = [
            1 => '普通项目',
            2 => '套餐项目',
            3 => '商品项目',
            4 => '次卡项目',
            5 => '年卡项目',
        ];
        return $categories[$data['billing_item_type_category']] ?? '未知';
    }
    
    /**
     * 获取删除标志文本
     */
    public function getDelFlagTextAttr($value, $data)
    {
        return $data['del_flag'] ? '已删除' : '正常';
    }
    
    /**
     * 获取客户信息
     */
    public function customer()
    {
        return $this->belongsTo('Customer', 'customer_id', 'id');
    }
    
    /**
     * 获取订单信息
     */
    public function billing()
    {
        return $this->belongsTo('Billing', 'billing_id', 'id');
    }
    
    /**
     * 获取开单人信息
     */
    public function billingUser()
    {
        return $this->belongsTo('SysUsers', 'billing_user_id', 'id');
    }
    
    /**
     * 获取科室信息
     */
    public function department()
    {
        return $this->belongsTo('Department', 'department_id', 'id');
    }
    
    /**
     * 获取医生信息
     */
    public function doctor()
    {
        return $this->belongsTo('SysUsers', 'doctor_id', 'id');
    }
    
    /**
     * 获取订单项详情
     */
    public function billingItemDetail()
    {
        return $this->hasOne('BillingItemDetail', 'billing_item_id', 'id');
    }
    
    /**
     * 获取治疗记录项目列表
     */
    public function treatRecordItems()
    {
        return $this->hasMany('TreatRecordItem', 'billing_item_id', 'id');
    }
    
    /**
     * 格式化金额字段
     */
    public function getUnitPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getTotalPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getTotalPerformPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getReceivedMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getDeductGivenBalanceAttr($value)
    {
        return (float) $value;
    }
    
    public function getDeductCardMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getAlreadyRefundMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getDiscountPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getFreeDetailPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getDiscountDetailPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getCourseDetailPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getResidueNumAttr($value)
    {
        return (float) $value;
    }
    
    public function getDeductOtherVirtualMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getDeductScoreMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getDeductBalanceAttr($value)
    {
        return (float) $value;
    }
    
    public function getDeductDepositAttr($value)
    {
        return (float) $value;
    }
    
    public function getRefundBalanceAttr($value)
    {
        return (float) $value;
    }
    
    public function getRefundDepositAttr($value)
    {
        return (float) $value;
    }
    
    public function getDeductReplaceMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getRefundReplaceMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getRealReceiveMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getRefundGivenBalanceAttr($value)
    {
        return (float) $value;
    }
    
    public function getRefundScoreMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getDeductEquityMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getDeductGivenEquityMoneyAttr($value)
    {
        return (float) $value;
    }
}
