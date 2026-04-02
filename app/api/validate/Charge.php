<?php
namespace app\api\validate;
// 项目收款
class Charge extends BaseValidate{
    protected $rule=[
        'lawcase_uuid'=>'require',
        'charge_method_id'=>'require|number',
        'collection_account_id'=>'require',
        'actual_amount'=>'require|number',
        'charge_time'=>'require|dateFormat:Y-m-d',
    ];

    protected $message=[
        'lawcase_uuid'=>'请选择案件后再操作!',
        'charge_method_id'=>'请选择收款方式!',
        'collection_account_id'=>'请选择收款账户!',
        'actual_amount'=>'请填写收款金额!',
        'charge_time'=>'请选择收款日期!'
    ];
    

}
