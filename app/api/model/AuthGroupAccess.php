<?php

namespace app\api\model;

use think\Model;
// 用户和用户组，职位关系表模型
class AuthGroupAccess extends Model
{
    // 隐藏字段
    protected $hidden = ['status','create_time','update_time'];

    // 通过uid获取group_id
    public static function getGroupIdByUid($uid){
        $groupId = self::where('uid',$uid)->field('group_id')->find();
        return $groupId['group_id'];
    }
}
