<?php

namespace app\api\model;

use think\Model;
// 部门模型
class Department extends Model
{
    // 隐藏字段
    protected $hidden = ['create_time','update_time'];

    // 部门下面的岗位角色
    public function roles(){
        return $this->hasMany('AuthGroup','department_id');
    }
    // 模型自关联 关联父级
    public function parentDept() {
        // return $this->hasOne('Department','id','pid')->selfRelation();
        return $this->hasOne(self::class,'id','pid');
    }
    // 自关联子级
    public function children() {
        return $this->hasMany('Department','pid','id');
    }
}
