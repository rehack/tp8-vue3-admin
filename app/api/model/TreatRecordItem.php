<?php

namespace app\api\model;

use think\Model;

class TreatRecordItem extends Model
{
    // 设置表名
    protected $name = 'treat_record_item';
    
    // 设置主键
    protected $pk = 'id';
    
    // 默认时间戳字段
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_date';
    protected $updateTime = 'update_date';
    
    // 追加属性
    protected $append = [
        'del_flag_text',
        'first_deduct_flag_text',
        'project_confirmation_flag_text',
    ];
    
    /**
     * 获取删除标志文本
     */
    public function getDelFlagTextAttr($value, $data)
    {
        return $data['del_flag'] ? '已删除' : '正常';
    }
    
    /**
     * 获取是否是首次划扣文本
     */
    public function getFirstDeductFlagTextAttr($value, $data)
    {
        return $data['first_deduct_flag'] ? '是' : '否';
    }
    
    /**
     * 获取是否已确认项目文本
     */
    public function getProjectConfirmationFlagTextAttr($value, $data)
    {
        return $data['project_confirmation_flag'] ? '已确认' : '未确认';
    }
    
    /**
     * 获取客户信息
     */
    public function customer()
    {
        return $this->belongsTo('Customer', 'customer_id', 'id');
    }
    
    /**
     * 获取治疗记录信息
     */
    public function treatRecord()
    {
        return $this->belongsTo('TreatRecord', 'treat_record_id', 'id');
    }
    
    /**
     * 获取订单信息
     */
    public function billing()
    {
        return $this->belongsTo('Billing', 'billing_id', 'id');
    }
    
    /**
     * 获取订单项信息
     */
    public function billingItem()
    {
        return $this->belongsTo('BillingItem', 'billing_item_id', 'id');
    }
    
    /**
     * 格式化金额字段
     */
    public function getPerformPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getPerformGivenBalanceAttr($value)
    {
        return (float) $value;
    }
    
    public function getPerformCardMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getPerformOtherVirtualMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getPerformScoreMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getPerformGivenEquityAttr($value)
    {
        return (float) $value;
    }
    
    public function getChangeFairValueAttr($value)
    {
        return (float) $value;
    }
}
