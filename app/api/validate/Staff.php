<?php
namespace app\api\validate;

class Staff extends BaseValidate{
    protected $rule=[
        'username'=>'require|length:2,25|unique:sys_users|isNotEmpty',
        'password'=>'require|min:6',
        'phone'=>'mobile',
        'roles'=>'require|array',
        'dept_id'=>'require|number',
        'status'=>'number'

    ];

    protected $message=[
        'username'=>'请正确填写用户名',
        'username.unique'=>'该用户名已经存在请核实',
        'password'=>'请填写密码',
        'phone'=>'请正确填写手机号',
        'roles'=>'请选择职员角色',
        'dept_id'=>'请选择职员部门'
    ];
}