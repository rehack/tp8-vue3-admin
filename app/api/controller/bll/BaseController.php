<?php

namespace app\api\controller\bll;
use think\App;
use app\middleware\Auth;

// bll目录下的控制器都需要经过Auth中间件 进行权限的验证
class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;
    // 控制器中间件
    protected $middleware = [Auth::class];
    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {}
}
