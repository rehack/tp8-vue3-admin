<?php
namespace app\api\validate;
// 新增项目服务
class ProjectService extends BaseValidate{
    protected $rule=[
        'id'=>'number',
        'project_id'=>'require|number',
        'service_content_ids'=>'require|array',
        'service_time'=>'require|dateFormat:Y-m-d',
        'service_place'=>'require',
        'service_way_id'=>'require|number',
        'service_duration'=>'require|number',
        'servicer_id'=>'require|number'
        
    ];

    protected $message=[
        'project_id'=>'请选择项目后再操作!',
        'service_content_ids'=>'请选择服务内容!',
        'service_time'=>'请选择服务时间!',
        'service_place'=>'请填写服务地点!',
        'service_way_id'=>'请选择服务方式!',
        'service_duration'=>'请正确填写服务时长!',
        'servicer_id'=>'请选择服务人员!'
    ];
    

}
