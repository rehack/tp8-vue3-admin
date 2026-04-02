<?php
namespace app\api\validate;

class Marketing extends BaseValidate{
    protected $rule=[
        'customer_name'=>'require|isNotEmpty',
        'linkman_name'=>'require',
        'customer_phone'=>'require|mobile|unique:customer|isNotEmpty',
        'type_id'=>'require|egt:1|integer',
        'user_id'=>'egt:1|integer'
        
    ];

    protected $message=[
        'customer_name'=>'客户名称不能为空！',
        'linkman_name'=>'请填写联系人',
        'customer_phone'=>'请正确填写客户手机号',
        'customer_phone.unique'=>'该客户已经录入过了，请核实！',
        'type_id'=>'请选择客户类别',
        'user_id'=>'开发人员选择错误'
    ];
    

}
