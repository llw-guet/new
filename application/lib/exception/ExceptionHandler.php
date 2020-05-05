<?php


namespace app\lib\exception;

use Exception;
use think\exception\Handle;
use think\Log;
use think\Request;

class ExceptionHandler extends Handle {
    private $code;
    private $msg;
    private $errCode;
    //还有出现异常的url地址

    //全局自定义异常处理， 抛出的未被捕获处理的异常都会通过该方法来处理返回到客户端
    public function render(Exception $e){
        if($e instanceof BaseException){
            //如果是自定义的异常
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errCode = $e->errCode;
        }else{
            //如果是服务器内部的异常
            //根据服务器开发人员选择是否开启框架提供的默认异常处理页面
            if (config('app_debug')) {
                return parent::render($e);
            } else {
                $this->code = 500;
                $this->msg = '服务器内部错误，不想告诉你';
                $this->errCode = 999;
                $this->recordErrorLog($e);
            }
        }

        $request = Request::instance();
        $result = [
            'msg' => $this->msg,
            'errCode' => $this->errCode,
            'request_url' => $request->url()
        ];
        return json($result, $this->code);
    }

    public function recordErrorLog(Exception $e){
        //在config.php中已关闭日志功能，故在此处需要开启日志
        Log::init([
            'type' => 'File',
            'path' => LOG_PATH,
            'level' => ['error']
        ]);
        Log::record($e->getMessage(), 'error');
    }
}