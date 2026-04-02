<?php
namespace app\commom\validate;
use think\Validate;
use think\facade\Request;
use app\commom\exception\ParameterException;

class BaseValidate extends Validate{
    public function doCheck(){
        // 获取HTTP传入的参数
        $params = Request::param(); // 获取当前请求的所有变量（经过过滤）（包含上传文件）
        // 对HTTP参数做校验
        $result = $this->check($params);
        if (!$result) {
            $error = $this->error;
            throw new ParameterException(['msg' => $error]);
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
        $rule = '^1[3-9]\d{9}$^';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}
