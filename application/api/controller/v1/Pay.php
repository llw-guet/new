<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\service\WxNotify;
use app\api\validate\IDMustBePositiveInt;
use app\api\service\Pay as PayService;

class Pay extends BaseController {
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'getPreOrder']
    ];

    /**
     * 用户选择某个订单进行支付，服务器与为微信服务器处理请求并返回客户端拉起支付参数
     * @param $id
     * @return array
     * @throws \app\lib\exception\ParameterException
     * @throws \think\Exception
     */
    public function getPreOrder($id) {
        (new IDMustBePositiveInt())->goCheck();
        $payService = new PayService($id);
        return $payService->pay();
    }

    /**
     * 微信服务器处理完客户端的支付请求后， 向服务器发送通知，告诉客户端支付结果
     * 通知频率为： 15/15/30/180/1800/1800/1800/1800/3600， 秒
     * 接收到微信服务器的通知后：
     *  1.检查库存量，看是否出现“超卖”
     *  2.更新这个订单的status状态
     *  3.减少库存量
     * 如果成功处理，我们就返回微信成功处理的信息，否则，我们需要返回没有成功处理
     *  微信服务器请求携带的参数特点：
     *      1.POST方式， 2.xml格式，会过滤掉本地服务器其他携带的参数
     */
    public function receiveNotify(){
        $wxNotify = new WxNotify();
        $wxNotify->Handle();
    }
}