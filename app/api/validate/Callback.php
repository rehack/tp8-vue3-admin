<?php
namespace app\api\validate;
// 营销中心客户回访
class Callback extends BaseValidate{
    protected $rule=[
        'customer_id'=>'require|number|isNotEmpty',
        'next_followup_time'=>'require',
        'next_followup_user_id'=>'require',
        'callback_info'=>'require',
        'is_meeting'=>'number'
        
    ];

    protected $message=[
        'customer_id'=>'请选择客户再操作!',
        'next_followup_time'=>'请选择下次跟进时间',
        'next_followup_user_id'=>'请选择下次跟进人员',
        'callback_info'=>'请认真填写跟进情况！'
    ];
    

}
