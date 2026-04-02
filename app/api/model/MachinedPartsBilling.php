<?php

namespace app\api\model;

use think\Model;

class MachinedPartsBilling extends Model
{
    // 设置表名
    protected $name = 'machined_parts_billing';
    
    // 设置主键
    protected $pk = 'id';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_date';
    protected $updateTime = 'update_date';
    
    // 状态常量
    const STATUS_DRAFT = 0;          // 待提交(暂存)
    const STATUS_PROCESSING = 1;     // 制作中(提交)
    const STATUS_RECEIVED = 2;       // 已收货
    const STATUS_CANCELLED = 3;      // 作废
    const STATUS_USED = 4;           // 已使用
    const STATUS_PARTIAL_USED = 5;   // 部分使用
    
    // 类型常量
    const TYPE_IMPLANT = 0;          // 种植件
    const TYPE_MOVABLE = 1;          // 活动件
    const TYPE_OTHER = 2;            // 其它件
    
    // 状态获取器
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            self::STATUS_DRAFT => '待提交',
            self::STATUS_PROCESSING => '制作中',
            self::STATUS_RECEIVED => '已收货',
            self::STATUS_CANCELLED => '作废',
            self::STATUS_USED => '已使用',
            self::STATUS_PARTIAL_USED => '部分使用'
        ];
        return $status[$data['status']] ?? '未知';
    }
    
    // 类型获取器
    public function getTypeTextAttr($value, $data)
    {
        $types = [
            self::TYPE_IMPLANT => '种植件',
            self::TYPE_MOVABLE => '活动件',
            self::TYPE_OTHER => '其它件'
        ];
        return $types[$data['machined_parts_type']] ?? '未知';
    }
    
    // 牙位JSON获取器
    public function getToothPositionAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }
    
    // 牙位JSON修改器
    public function setToothPositionAttr($value)
    {
        return $value ? json_encode($value, JSON_UNESCAPED_UNICODE) : null;
    }
    
    // 牙色JSON获取器
    public function getToothColorAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }
    
    // 牙色JSON修改器
    public function setToothColorAttr($value)
    {
        return $value ? json_encode($value, JSON_UNESCAPED_UNICODE) : null;
    }
    
    // 关联客户
    public function customer()
    {
        return $this->belongsTo('app\api\model\Customer', 'customer_id', 'id');
    }
    
    // 关联加工件
    public function machinedParts()
    {
        return $this->belongsTo('app\api\model\MachinedParts', 'machined_parts_id', 'id');
    }
    
    // 关联种植件
    public function dentalImplant()
    {
        return $this->belongsTo('app\api\model\DentalImplant', 'dental_implant_id', 'id');
    }
    
    // 关联活动件
    public function movableParts()
    {
        return $this->belongsTo('app\api\model\MovableParts', 'movable_parts_id', 'id');
    }
    
    // 关联下单人
    public function billingUser()
    {
        return $this->belongsTo('app\api\model\SysUsers', 'parts_billing_user_id', 'id');
    }
    
    // 关联收货人
    public function receiverUser()
    {
        return $this->belongsTo('app\api\model\SysUsers', 'receiver_user_id', 'id');
    }
    
    // 关联治疗人
    public function doctor()
    {
        return $this->belongsTo('app\api\model\SysUsers', 'doctor_id', 'id');
    }
    
    // 关联普通订单
    public function billing()
    {
        return $this->belongsTo('app\api\model\Billing', 'billing_id', 'id');
    }
    
    // 关联定金订单
    public function depositBilling()
    {
        return $this->belongsTo('app\api\model\Billing', 'deposit_billing_id', 'id');
    }
}
