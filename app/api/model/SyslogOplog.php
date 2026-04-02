<?php

namespace app\api\model;
use think\Model;
//  系统操作日志
class SyslogOplog extends Model
{
    // api表
    public function apirule() {
        return $this->belongsTo('AuthRule','api_id');
    }

    // 用户
    public function user() {
        return $this->belongsTo('SysUsers','user_id');
    }
}
