<?php
namespace app\api\controller\pub;
// use app\api\model\LawcaseLawsuitType as LawcaseLawsuitTypeModel;
use app\api\model\LawcaseAgentStage as LawcaseAgentStageModel;
use app\api\model\Department as DepartmentModel;
use app\api\model\ProjectPaymentMethod as ProjectPaymentMethodModel;
use app\api\model\LawcaseSource as LawcaseSourceModel;
use app\api\model\ProjectServiceContent as ProjectServiceContentModel;
use app\api\model\ProjectServiceContentTrd as ProjectServiceContentTrdModel;
use app\api\model\ProjectServiceWay as ProjectServiceWayModel;
use app\api\model\SysUsers as SysUsersModel;
use app\api\model\AuthRole as AuthRoleModel;
use app\api\model\LawcaseCollectionAccount as LawcaseCollectionAccountModel;
use app\api\model\LawcaseHandleType as LawcaseHandleTypeModel;
use app\api\model\LawcaseEvidenceProvider as LawcaseEvidenceProviderModel;
use app\api\model\LawcaseCasedistrict as LawcaseCasedistrictModel;
use app\api\model\LawcaseType as LawcaseTypeModel;
use app\api\model\PointsRule as PointsRuleModel;
use app\api\model\LawcaseHandleMatters as LawcaseHandleMattersModel;
class Project extends BaseController{
    // 诉讼案件类型
    // public function LawcaseLawsuitType(){
    //     $type = new LawcaseLawsuitTypeModel;
    //     $res = $type->catetree();
    //     return json($res);
    // }
    // 案件类型
    public function getCaseType(){
        $type = new LawcaseTypeModel;
        $res = $type->catetree();
        return json($res);
    }
    // 接案律师 律师和系统管理员
    public function getLawyerUser(){
        $roles = new AuthRoleModel;
        $lawyer_roles = AuthRoleModel::where('name', 'like', ['%律师%','%管理员'],'OR')->select();
        // return $lawyer_roles;

        

        $lawyer = [];
        foreach ($lawyer_roles as $roles) {
            foreach ($roles->sysuser as $user) {
                $t = [
                    'id'=>$user['id'],
                    'realname'=>$user['realname']
                ];
                if($user['status'] == 1 && !in_array($t,$lawyer)){ //（如果数组中已经存在该用户，则不插入数组）用户有多角色情况下会出现重复
                    $lawyer[] = $t;
                }
            }
        }

        // 第二种方法
        // $lawyer_roles = AuthRoleModel::where('name', 'like', ['%律师%','%管理员'],'OR')
        // ->with('sysuser')
        // ->select()->toArray();

        // foreach ($lawyer_roles as $role) {
        //     foreach ($role['sysuser'] as $user){
        //         $t = [
        //             'id'=>$user['id'],
        //             'realname'=>$user['realname']
        //         ];
        //         if($user['status'] == 1 && !in_array($t,$lawyer)){
        //             $lawyer[] = $t;
        //         }
        //     }
        // }
        return json(($lawyer));
    }

    // 获取代理阶段
    public function getAgentStage(){
        $res = LawcaseAgentStageModel::order('sort')->select();
        return json($res);
    }
    // 案件来源
    public function getCasesSource() {
        $cases_source = LawcaseSourceModel::order('sort')->select();
        return json($cases_source);
    }
    // 客服人员
    public function getCustService() {
        $dcrc = SysUsersModel::where('identity','销售')->select();
        return $dcrc;
    }

    // 获取律师部门下团队
    public function getLawyerTeam() {
        $lawyerTeam = DepartmentModel::where('department_name','律师部')->with('children')->select();
        return $lawyerTeam;
    }
    // 收款方式
    public function getPaymentMethods() {
        $paymentMethods = ProjectPaymentMethodModel::order('sort')->select();
        return json($paymentMethods);
    }
    // 收款账户类型
    public function getCollectionAccount(){
        $collection_ccount = LawcaseCollectionAccountModel::where('status',1)->order('sort')->select();
        return json($collection_ccount);
    }
    // 项目服务内容
    public function getOurServices() {
        $servicesList = new ProjectServiceContentModel;
        $res = $servicesList->catetree();
        return $res;
    }
    // 项目服务内容三级分类
    public function getServiceContentTrd() {
        return ProjectServiceContentTrdModel::order('sort')->select();
    }
    // 服务方式
    public function getServicesWay() {
        $serviceWay = ProjectServiceWayModel::order('sort')->select();
        // $res = $servicesList->catetree();
        // return json_encode($res);
        return $serviceWay;
    }
    // 办案类别
    // public function getCaseHandleType() {
    //     $caseHandleType = LawcaseHandleTypeModel::order('sort')->select();
    //     return json($caseHandleType);
    // }
    // 办理事项
    public function handlingMatters(){
        $type = new LawcaseHandleMattersModel;
        $res = $type->catetree();
        return json($res);
    }

    // 证据提供者
    public function getEvidenceProvider(){
        $res = LawcaseEvidenceProviderModel::order('sort')->select();
        return json($res);
    }
    // 办案区域
    public function getCaseDistrict(){
        $res = LawcaseCasedistrictModel::order('sort')->select();
        return json($res);
    }
    // 积分规则
    public function getPointRules(){
        $res = PointsRuleModel::select();
        return json($res);
    }
}