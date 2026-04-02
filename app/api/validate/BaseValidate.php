<?php
namespace app\api\validate;

use app\commom\exception\ParameterException;
use think\Exception;
use think\facade\Request;
use think\Validate;

class BaseValidate extends Validate
{
    //获取http传入的参数
    //检验参数
    public function goCheck()
    {
        // $request = Request::instance();
        $params = Request::param();
        // Request::file()
        // $params = Request::param('excel'); // 获取当前请求的所有变量（包含上传文件）
        // $params = request()->file('excel');; // 获取当前请求的所有变量（包含上传文件）
        // dump($params);die;
        $result = $this->check($params);
        if (!$result) {
            $error = $this->error;
            throw new ParameterException(['msg' => $error]);
//            throw new Exception($error);
        } else {
            return true;
        }
    }

    //根据验证规则过滤数据
    public function getDataByRule($params)
    {
        if (array_key_exists('user_id', $params) || array_key_exists('uid', $params)) {
            throw new ParameterException([
                'msg' => '参数中包含非法参数名user_uid或者uid'
            ]);
        }
        $newParams = [];
        foreach ($this->rule as $k => $v) {
            $newParams[$k] = $params[$k];
        }
        return $newParams;
    }

    //根据验证规则过滤数据
    // Request::only(['name','email']); 可以用这个方法来过滤
    public function getDataByRule2($params, $filter = [])
    {
        if (!empty($filter)) {
            foreach ($filter as $k => $v) {
                if (array_key_exists($v, $params)) {
                    throw new ParameterException([
                        'msg' => '参数中包含非法参数名:' . $v
                    ]);
                }
            }
        }

        $newParams = [];
        foreach ($this->rule as $k => $v) {
            $newParams[$k] = $params[$k];
        }
        return $newParams;
    }

    //必须是正整数
    protected function isPositiveInteger($value, $rule = '', $date = '', $field = '')
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        } else {
//            return $field . '必须是正整数';
            return false;
        }
    }

    //值不为空
    protected function isNotEmpty($value, $rule = '', $date = '', $field = '')
    {
        /* if(empty($value)){
             return false;
         }else{
             return true;
         }*/

        return empty($value) ? false : true;
    }

    //验证手机号
    protected  function isMobile($value)
    {
        $rule = '^1(1|3|4|5|7|8)[0-9]\d{8}$^';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    // Request::only(['name','email']); 可以用这个方法来过滤
    // 根据validate规则过滤数据
    
    public function gl(){
        $rule=array_keys($this->rule);
        // var_dump(['name','email']);
        // return $rule;
        return Request::only($rule);
    }
    // 排除参数
    // Request::except(['id','name']);
}