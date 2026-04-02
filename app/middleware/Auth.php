<?php
declare (strict_types = 1);

namespace app\middleware;
use app\api\service\BaseToken;
use app\commom\exception\AuthException;
use app\api\model\SysUsers as SysUsersModel;
use think\facade\Db;
class Auth
{
    /**
     * 处理请求
     * 接口权限中间件(控制器中间件BaseController)
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        $path = $request->pathinfo();     //获取路由URL的pathinfo信息(不含URL后缀) marketing/expirefollowquery
        // $route = substr($path,4);//去掉api开头
        $route = str_replace('/','.',$path); //把URL /替换成. v1.marketing.customer
        $route = $route;
        // $route = str_replace('/', '.', substr($path, 0, strpos($path, '/')));
        // var_dump($route);die;
        // $route = $request->pathinfo(); // api/v1/marketing/customer
        // $route = $request->path(); // api/v1/marketing/customer
        // echo $route;
        // var_dump(($request->routeInfo())['rule']);
        // var_dump($request->controller());die;
        $currentAction = strtolower($request->action()); //当前方法名
        $except = $this->exceptAction($currentAction); //当前方法名是否需要排除
        // 不需要排除的方法就进入中间件验证阶段
        if(!$except){
            // var_dump($this->checkAuth($route));
            // var_dump($this->getRolesViewScope());
            if ($this->checkAuth($route))
            {
                // echo $route;
                $request->current_uid = $this->getCurrentUid(); //当前用户的ID
                // echo $request->current_uid;
                $request->role_view_scope = $this->getRolesViewScope(); //权限范围
                // echo $request->group_view_scope;
            }else
            {
                throw new AuthException();
            }
        }
        

        return $next($request);
    }

    // 获取当前用户的uid
    private function getCurrentUid(){
        $uid = BaseToken::getCurrentVarByToken('uid');
        return $uid;
    }
    
    
    // 检查权限，当前的路由地址是否在当前用户所在组的权限列表里（权限列表里也是存的路由地址）
    private function checkAuth($name ='')
    {

        //$name是当前请求的路由地址
        if (!$name){
            return false;
        }
        
        $auth = Db::name('auth_rule')->where('name',$name)->find();//查询当前路由所在的规则
        if(!$auth){
            return false;
        }
        // 临时调试开放
        // if(!$auth){
        //     return false;
        // }
        // var_dump($auth);die;
        // $uid = $this->getCurrentUid();
        // return 'uid:'.$uid;
        //获取当前用户所在的组的id
        // $groupId = db('auth_group_access')->where('uid',$uid)->field('group_id')->find();
        // var_dump($groupId);die;
       
        // 查询出用户组所有的rules
        $roles = $this->getRoles();
        // $roles_rules = $roles_rules['rules_id'];
        $roles_rules = []; //用户角色所拥有的所有接口权限id数组
        foreach ($roles as $role){
            $roles_rules[] = $role['rules_id'];
        }
        
        $str = implode(',',$roles_rules);
        //把rules字符串1,2转化成数组
        $roles_rules = explode(',', $str);
        $result = in_array($auth['id'],$roles_rules);
        return $result;
    }
    // 获取当前用户所属角色，可能有多角色
    private function getRoles(){
        $uid = $this->getCurrentUid();
        $user = SysUsersModel::find($uid);// 需要优化SELECT * FROM `lawyeroa_sys_users` WHERE  `id` = 1 LIMIT 1 会被多次查询
        $roles = $user->roles;
        // var_dump($roles);
        // $groupId = db('auth_group_access')->where('uid',$uid)->field('group_id')->find();
        // $group = db('auth_group')->where('id',$groupId['group_id'])->find();
        return $roles;
    }
    // 获取组的scope数据查看范围
    private function getRolesViewScope(){
        $roles = $this->getRoles();
        $scope_arr = []; //用户角色所拥有的所有接口权限id数组
        foreach ($roles as $role){
            $scope_arr[] = $role['view_scope'];
        }
        return $scope_arr;
    }
    // 排除某些方法进入中间件，比如登录
    private function exceptAction($action){
        $exceptActionArr = [
            'gettoken'
        ];
        if(in_array($action,$exceptActionArr)){
            return true;
        }else{
            return false;
        }
    }
}
