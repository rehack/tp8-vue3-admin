<?php
declare (strict_types = 1);

namespace app\middleware;
use think\facade\Db;
use think\facade\Cache;
use app\commom\exception\TokenException;

use Closure;
use think\Config;
use think\Request;
use think\Response;
class SysLog
{
    /**
     * 处理请求
     * 记录操作日志后置中间件(注册全局中间件)
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        $response = $next($request);
 
        // 添加中间件执行代码
        
        $data = [
            'apistr'=>str_replace('/','.',$request->pathinfo()),
            'method'=>$request->method(),
            'param'=>json_encode($request->param(),JSON_UNESCAPED_UNICODE),
            'code'=>$response->getCode(),
            'ip'=>$request->ip(),
            'user_id'=>$this->getCurrentVarByToken($request->header('authorization'))
        ];
        
        // halt($request,$data);
        $this->saveOplog($data);
        return $response;
    }
    

    // 把操作日志记录存入数据库
    private function saveOplog($data){
        $rule = Db::table('auth_rule')->where('name',$data['apistr'])->find();
        // var_dump($rule);

        if(!empty($rule) && $rule['type']>=2){
            $api_id = $rule['id'];
            $data['api_id'] = $api_id;
            $data['create_time'] = time();
            // unset($data['apistr']);
            
            try {
                if(mb_strlen($data['param']) > 5000){
                    $data['param'] = mb_substr($data['param'],0,5900);
                }
                Db::table('syslog_oplog')->strict(false)->insert($data);
            } catch (\Exception $e) {
                $code = $e->getCode();
                $msg = $e->getMessage();
                // $data['param'] = substr($code.'--'.$msg.'--'.$data['param'],0,1000);
                $data['param'] = 'error:'.$code.'--'.$msg;
                Db::table('syslog_oplog')->strict(false)->insert($data);
            }
            unset($data);
        }else{
            // var_dump('empty');
            return true;
        }
        
    }

    // 获取当前操作用户的uid
    private function getCurrentVarByToken($tokenstr){
        // 从客户的请求头信息里获取token
        // $bearerToken = Request::header('Authorization');
        // halt($bearerToken);
        try {
            $arr = explode(' ', $tokenstr);
        
            if(empty($arr[1])){
                // 请求时没有携带token
                return true;
            }
            $token = $arr[1];
            $values = Cache::get($token);

            // 如果缓存过期或不存在
            if(!$values){
                // 把异常抛到客户端，客户端重新获取token
                return true;
            }else{
                if(!is_array($values)){
                    $values = json_decode($values,true);
                }

                if(array_key_exists('uid',$values)){
                    return $values['uid'];
                }else{
                    return true;
                }
            }
        } catch (\Throwable $th) {
            // 404，或者没有authorization头
            // halt($th);
            // echo 11544;
            // throw new TokenException([
            //     'msg' => '资源不存在或无效参数'
            // ]);
            return true;
        }
        
    }
}
