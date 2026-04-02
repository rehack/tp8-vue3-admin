<?php
namespace app\api\validate;

class UserLogin extends BaseValidate{
    protected $rule=[
        'username'=>'require',
        'password'=>'require'
        
    ];

    protected $message=[
        'username'=>'账号或密码不能为空！',
        'password'=>'账号或密码不能为空！'
    ];
    

}
