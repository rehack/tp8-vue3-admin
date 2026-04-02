<?php

namespace app\api\model;

use think\Model;
// 角色表
class AuthRole extends Model
{
    // 隐藏字段
    protected $hidden = ['status','create_time','update_time'];

    // 多对多关联用户
    public function sysuser()
    {
        // 角色 BELONGS_TO_MANY 用户
        return $this->belongsToMany(SysUsers::class, 'auth_access');
    }

    // 多对多关联菜单
    public function menus()
    {
        return $this->belongsToMany(AuthMenu::class, 'auth_role_menu','menu_id','role_id');
    }
}
