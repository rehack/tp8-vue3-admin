<?php

namespace app\api\model;

use think\Model;

class Billing extends Model
{
    // 设置表名
    protected $name = 'billing';
    
    // 设置主键
    protected $pk = 'id';
    
    // 默认时间戳字段
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_date';
    protected $updateTime = 'update_date';
    
    // 追加属性
    protected $append = [
        'del_flag_text',
        'status_text',
        'approval_status_text',
        'billing_type_text',
        'data_source_text',
    ];
    
    /**
     * 获取订单状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            0 => '待缴费',
            1 => '已缴费',
            2 => '已作废',
            3 => '欠费',
        ];
        return $status[$data['status']] ?? '未知';
    }
    
    /**
     * 获取审批状态文本
     */
    public function getApprovalStatusTextAttr($value, $data)
    {
        $status = [
            0 => '待审批',
            1 => '审批中',
            2 => '已通过',
            3 => '已拒绝',
        ];
        return $status[$data['approval_status']] ?? '未知';
    }
    
    /**
     * 获取订单类型文本
     */
    public function getBillingTypeTextAttr($value, $data)
    {
        $types = [
            1 => '普通订单',
            2 => '赠送订单',
            3 => '套餐订单',
        ];
        return $types[$data['billing_type']] ?? '未知';
    }
    
    /**
     * 获取订单来源文本
     */
    public function getDataSourceTextAttr($value, $data)
    {
        $sources = [
            0 => '客户端',
            1 => '微信',
            2 => 'APP',
        ];
        return $sources[$data['data_source']] ?? '未知';
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
     * 获取开单人信息
     */
    public function billingUser()
    {
        return $this->belongsTo('SysUsers', 'billing_user_id', 'id');
    }
    
    /**
     * 获取订单项列表
     */
    public function billingItems()
    {
        return $this->hasMany('BillingItem', 'billing_id', 'id');
    }
    
    /**
     * 格式化金额字段
     */
    public function getReceivedMoneyAttr($value)
    {
        return (float) $value;
    }
    
    public function getTotalPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getOriginalPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getDiscountPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getDepositPriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getFreePriceAttr($value)
    {
        return (float) $value;
    }
    
    public function getCoursePriceAttr($value)
    {
        return (float) $value;
    }
}
