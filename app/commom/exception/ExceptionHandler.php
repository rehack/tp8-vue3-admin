<?php
namespace app\commom\exception;
use think\exception\Handle;
use think\facade\Log;
use think\facade\Request;
use think\facade\Env;
use think\Response;
use Throwable;

class ExceptionHandler extends Handle
{
    private $code;
    private $msg;
    private $errorCode;

    /**
     * @param \Exception $e 这里的Exception指的是PHP的Exception，而不是thinkphp的Exception，所以无需use think\Exception,如果在命名空间引用了php的Exception就用Exception,如果没有引用就用\Exception,建议使用\Exception
     * @return \think\Response|\think\response\Json
     */
    public function render($request, Throwable $e): Response
    {
        if ($e instanceof BaseException) {
            // 属于客户端的产生的异常
            //自定义异常
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
        } else {
            // 属于系统的异常
            if (Env::get('APP_DEBUG') == '1') {
                // 在应用调试模式下 错误交给tp系统处理
                return parent::render($request, $e);
            } else {
                $this->code = 500;
                $this->msg = '系统错误，请联系管理员  '.date('Y-m-d H:i:s');
                $this->errorCode = 999;
                $this->recordErrorLog($e);
            }

        }
        // $request = Request::instance();
        $result = [
            'msg' => $this->msg,
            'error_code' => $this->errorCode,
            'request_url' => Request::url()
        ];
        return json($result, $this->code);
    }

    /**
     * @param \Exception $e
     */
    private function recordErrorLog(\Exception $e)
    {
        // dump(Env::get('root_path'). 'log'.DIRECTORY_SEPARATOR);die;
        Log::init([
            'type' => 'File',
            'path' => Env::get('runtime_path') . 'errlog/'.DIRECTORY_SEPARATOR,
            'level' => ['error']
        ]);
        // IP 错误文件 错误行号 错误信息
        $err_log_txt = Request::ip().' '.$e->getFile().' '.$e->getLine().' '.$e->getMessage();
        // var_dump($err_log_txt);
        //生产环境记录服务器内部错误
        Log::error($err_log_txt);
    }
}