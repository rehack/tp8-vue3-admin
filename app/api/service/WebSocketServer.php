<?php
namespace app\api\service;
class WebSocketServer
{
    protected $serv = null;       //Swoole\Server对象
    protected $host = '0.0.0.0'; //监听对应外网的IP 0.0.0.0监听所有ip
    protected $port = 19609;      //监听端口号
    public $server;
    public function __construct()
    {
        //创建websocket服务器对象，监听0.0.0.0:9604端口
        $this->server = new \Swoole\Websocket\Server($this->host, $this->port);
        // $this->server = new \Swoole\Websocket\Server($this->host, $this->port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
        //设置参数
        //如果业务代码是全异步 IO 的，worker_num设置为 CPU 核数的 1-4 倍最合理
        //如果业务代码为同步 IO，worker_num需要根据请求响应时间和系统负载来调整，例如：100-500
        //假设每个进程占用 40M 内存，100 个进程就需要占用 4G 内存
        $this->server->set([
            'worker_num' => 2,         //设置启动的worker进程数。【默认值：CPU 核数】
            'max_request' => 1000,    //设置每个worker进程的最大任务数。【默认值：0 即不会退出进程】
            // 'daemonize' => 1,          //开启守护进程化【默认值：0 关闭】
            // 'ssl_cert_file' => '/usr/local/webserver/nginx/cert/api/swoole_api.nanhenglaw.com.pem',
            // 'ssl_key_file' => '/usr/local/webserver/nginx/cert/api/swoole_api.nanhenglaw.com.key'
        ]);

        //监听WebSocket连接打开事件
        $this->server->on('Open', function ($ws, $request) {
            // var_dump($request);
            // $request->get['uid'] //获取参数
            $this->server->bind($request->fd,$request->get['uid']);
            $uid = $this->server->getClientInfo($request->fd);

            $uid = json_encode($uid);
            $this->server->push($request->fd, "$uid\n");
            $this->server->push($request->fd, "from server:hello, welcome rehack!\n");
        });
        // $this->server->on('Open', function(Swoole\WebSocket\Server $server, $request)
        // {
        //     echo "server: handshake success with fd{$request->fd}\n";
        // });
        //监听WebSocket消息事件
        //客户端向服务器端发送信息时，服务器端触发 onMessage 事件回调
        //服务器端可以调用 $server->push() 向某个客户端（使用 $fd 标识符）发送消息，长度最大不得超过 2M
        $this->server->on('Message', function ($ws, $frame) {
            echo "Message: {$frame->data}\n";
            $this->server->push($frame->fd, "server: {$frame->data}");
        });
            
        //监听WebSocket主动推送消息事件
        $this->server->on('Request', function(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
        {
            /*
            * Loop through all the WebSocket connections to
            * send back a response to all clients. Broadcast
            * a message back to every WebSocket client.
            */
            // echo 'on Request';
            // var_dump($request->post);
            $uids = $request->post['notify_users'];
            $msg = $request->post['data'];
            // var_dump($this->server);
            foreach($this->server->connections as $fd) //遍历TCP连接迭代器，拿到每个在线的客户端id
            {
                // Validate a correct WebSocket connection otherwise a push may fail
                if($this->server->isEstablished($fd))
                {
                    $clientInfo = $this->server->getClientInfo($fd);
                    // var_dump($uids,$clientInfo['uid']);
                    if(in_array($clientInfo['uid'],$uids)){
                        $this->server->push($fd, json_encode($msg));
                    }
                }
            }
        });
        //监听WebSocket连接关闭事件
        $this->server->on('Close', function ($ws, $fd) {
            // echo "client-{$fd} is closed\n";
        });

        //启动服务
        // $this->server->start();
    }

    public function start()
    {
        $this->server->start();
    }
}

// Create a new WebSocket server instance
$webSocketServer = new WebSocketServer();

// Start the server
$webSocketServer->start();

// 前端浏览器测试
// var wsServer = 'ws://127.0.0.1:9609?uid=89';
// var websocket = new WebSocket(wsServer);
// websocket.onopen = function (evt) {
//     console.log("Connected to WebSocket server.");
// };

// websocket.onclose = function (evt) {
//     console.log("Disconnected");
// };

// websocket.onmessage = function (evt) {
//     console.log('Retrieved data from server: ' + evt.data);
// };

// websocket.onerror = function (evt, e) {
//     console.log('Error occured: ' + evt.data);
// };