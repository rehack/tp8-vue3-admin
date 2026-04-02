<?php

namespace app\api\controller\pub;
use app\api\model\SysUsers as SysUsersModel;
use app\api\model\Department as DepartmentModel;
use app\api\model\DataTree as DataTreeModel;
use app\api\model\AuthRule as AuthRuleModel;
use app\api\model\AuthRole as AuthRoleModel;
use app\api\model\AuthMenu as AuthMenuModel;
use app\api\service\BaseToken;
// use think\Controller;

// 公共资源模块下的接口不需要验证权限,中间件里没有注册Auth，但需要验证token
class System extends BaseController
{
    // 获取所有用户人员列表数据
    public function getSysUser(){
        $allUser = SysUsersModel::getAllUser();
        return json($allUser);
    }

    function in_array_r($item , $array){
        return preg_match('/"'.preg_quote($item, '/').'"/i' , json_encode($array));
    }
    // 通过token获取当前登录用户信息
    public function getUserInfo(){
        // return 'dasdsa'.$this->uid;
        $uid = BaseToken::getCurrentVarByToken('uid');
        $userInfo = SysUsersModel::with(['roles'=>['menus'],'dept'])->find($uid)->toArray();

        // var_dump($uid);die;
        // echo SysUsersModel::getLastSql();
        $roles_name = [];
        $roles_scope = [];
        $menus = [];
        // halt($userInfo['roles']);
        foreach ($userInfo['roles'] as $role) {
            $roles_name[] = $role['name'];
            $roles_scope[] = $role['view_scope'];
            foreach($role['menus'] as $v){
                if( !in_array($v['id'], array_column($menus , 'id')) ){ //去除重复
                    $menus[] = $v; 
                    $menu_ids[] = $v['id'];
                }
                
            }
        }
        array_multisort($menu_ids,SORT_ASC,SORT_NUMERIC,$menus); //多维数组排序 按照menu_id
        $userInfo['roles'] = $roles_name;
        $userInfo['roles_scope'] = $roles_scope;
        $userInfo['menus'] = $menus;
        return json($userInfo);
    }

    // 获取部门树结构数据
    public function getDpamtTree(){
        $dept = DepartmentModel::select()->toArray();
        // $data = $dep->toArray();
        $places = array_column($dept, null, 'id');
        // dump($places);die;
        return json(generate_tree($places));
    }
    // 获取部门数据 不带角色
    public function getDepartment(){
        $dep = DepartmentModel::where('pid','>',0)->select();
        return $dep;
    }

    // data tree 数据字典目录
    public function getDataTree(){
        return DataTreeModel::where('status',1)->select();
    }
    // 系统角色职位列表
    public function getRoles(){
        $res = AuthRoleModel::with(['menus'])
        ->select();

        // 关联数据扁平化处理
        /*
        foreach($res as &$role){
            $role['menu_id']= [];
            foreach($role['menus'] as $k=>$menu){
                // $role['menus'][$k] = $menu['id'];
                $role['menu_id'][] = $menu['id'];
            }
        }
        */
        return json($res);
    }

    // 权限规则列表
    public function getAuthRules(){
        $rules = AuthRuleModel::where('status',1)->select();
        $data = $rules->toArray();
        $places = array_column($data, null, 'id');
        // dump($places);die;
        return json(generate_tree($places));
    }

    // 菜单数据
    public function getMenus(){
        $menu = AuthMenuModel::where('status',1)->select()->toArray();
        $places = array_column($menu, null, 'id');
        return json(generate_tree($places));
    }
}
