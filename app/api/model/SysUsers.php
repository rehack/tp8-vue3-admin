<?php

namespace app\api\model;

use think\Model;

class SysUsers extends Model
{
    // 隐藏字段
    protected $hidden = ['update_time','password'];
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 关联客户模型
    public function customers()
    {
        return $this->hasMany('Customer','user_id');
    }
    // 多对多关联角色
    public function roles()
    {
        // 用户 BELONGS_TO_MANY 角色
        return $this->belongsToMany(AuthRole::class, 'auth_access');
    }
    // 关联部门
    public function dept()
    {
        return $this->belongsTo('Department','dept_id');
    }
    // 用户打的跟进
    public function customerCallbacks(){
        return $this->hasMany('CustomerCallback','callback_user_id');
    }

    // 获取所有用户列表
    public static function getAllUser(){
        $allUser = self::where('id','>',0)->visible(['id','realname','status'])->select();
        return $allUser;
    }

    // 多对多关联 律师领取的客户
    public function receives()
    {
        return $this->belongsToMany('Customer','LawyerReceive', 'customer_id', 'lawyer_id');
    }

    // 根据职员ID获取对应的部门名称
    public static function getDepartmentNameByStaffId($staffId) {
        return self::where('id',$staffId)->with('dept')->visible(['department_name'])->find();
    }

    // 多对多关联 律师接案
    public function recipientCases()
    {
        return $this->belongsToMany('Lawcase','LawcaseRecipientMid', 'lawcase_id', 'sys_users_id');
    }

    // 多对多关联 律师被分配的案件
    public function assignedCases()
    {
        return $this->belongsToMany(Lawcase::class, LawcaseHandlelawyerMid::class, 'lawcase_id', 'sys_users_id');
    }
    // 录入的案件
    public function createCases(){
        return $this->hasMany(Lawcase::class, 'operator_id');
    }
    //  已结案件
    public function closedCases()
    {
        return $this->belongsToMany('Lawcase','LawcaseHandlelawyerMid', 'lawcase_id', 'sys_users_id')->where('status',3);
    }
    // 办理的案件记录
    public function handleCases()
    {
        return $this->hasMany(LawcaseCaseHandle::class, 'handle_operator_id');
    }

    // 一对多 分值
    public function score()
    {
        return $this->hasMany(PointsDetail::class,'lawyer_id');
    }
    
    // 关联接案中间表
    public function recmid(){
        return $this->hasMany(LawcaseRecipientMid::class,'sys_users_id');
    }
    // 档案
    public function archives(){
        return $this->hasMany(LawcaseEvidenceArchives::class,'uploader_id');
    }
    // 待办事项中间表 多对多 包括@自己的
    public function todolist()
    {
        return $this->belongsToMany(TodoList::class,TodolistUser::class, 'todolist_id', 'uid');
    }
}
