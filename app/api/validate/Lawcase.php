<?php
namespace app\api\validate;
// 案件接入验证器
class Lawcase extends BaseValidate{
    protected $rule=[
        'caseInfoData.lawcase_id'=>'isPositiveInteger',
        'caseInfoData.customer_name'=>'require',
        'caseInfoData.customer_phone'=>'mobile',
        'caseInfoData.lawcase_name'=>'require',
        'caseInfoData.lawcase_type_id'=>'require|isPositiveInteger',
        'caseInfoData.lawcase_sec_type_id'=>'require|isPositiveInteger',
        'caseInfoData.accept_lawyer'=>'require|array',
        'caseInfoData.lawcase_source_id'=>'require|isPositiveInteger',
        'caseInfoData.agent_stage'=>'array',
        'caseInfoData.contracted_time'=>'require|dateFormat:Y-m-d',
        'caseInfoData.risk_agency_ratio'=>'egt:0',
        // 'caseInfoData.case_situation'=>'require',
        // 'caseInfoData.litigant_request'=>'require',

        // 'caseInfoData.advisory_unit_situation'=>'require'
        
        'chargeData.charge_method_id'=>'isPositiveInteger',
        'chargeData.collection_account_id'=>'isPositiveInteger',
        'chargeData.contract_amount'=>'float|egt:0',
        'chargeData.actual_amount'=>'float|egt:0',
        'chargeData.travel_expenses'=>'float|egt:0',
        'chargeData.charge_time'=>'dateFormat:Y-m-d',     
    ];

    protected $message=[
        'caseInfoData.customer_name'=>'请填写客户名称!',
        'caseInfoData.customer_phone'=>'请准确填写手机号码!',
        'caseInfoData.lawcase_name'=>'请填写案件名称!',
        'caseInfoData.lawcase_type_id'=>'请选择案件类型!',
        'caseInfoData.lawcase_sec_type_id'=>'请选择诉讼案件类型!',
        'caseInfoData.accept_lawyer'=>'请选择接案律师!',
        'caseInfoData.lawcase_source_id'=>'请选择案件来源!',
        'caseInfoData.agent_stage'=>'请填选择代理阶段!',
        'caseInfoData.contracted_time'=>'请填选择签约时间!',
        'caseInfoData.risk_agency_ratio'=>'请准确填写风险代理比例!',
        'caseInfoData.case_situation'=>'请填写案件情况!',
        'caseInfoData.litigant_request'=>'请填写客户诉求!',

        'chargeData.charge_method_id'=>'请选择收款方式',
        'chargeData.collection_account_id'=>'请选择收款账户!',
        'chargeData.contract_amount'=>'请正确填写合同金额(保留两位小数)!',
        'chargeData.actual_amount'=>'请正确填写已收款金额(保留两位小数)!',
        'chargeData.travel_expenses'=>'请正确填写差旅费(保留两位小数)!',
        'chargeData.charge_time'=>'请填选择收款时间!',
    ];
    
    // 验证场景
    protected $scene = [
        'lawsuit'  =>  ['caseInfoData.customer_name','caseInfoData.customer_phone','caseInfoData.lawcase_name','caseInfoData.lawcase_type_id','caseInfoData.lawcase_sec_type_id','caseInfoData.accept_lawyer'], // 诉讼案件
        'adviser'  =>  ['name','age'], // 顾问案件
    ];  
}
