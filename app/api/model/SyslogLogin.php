<?php

namespace app\api\model;
use think\Model;
//  系统登录日志
class SyslogLogin extends Model
{
    // 用户
    public function user() {
        return $this->belongsTo('SysUsers','user_id');
    }
}
