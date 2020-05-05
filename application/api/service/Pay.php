<?php


namespace app\api\service;


use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use app\api\service\Token as TokenService;
use app\lib\enum\OrderStatusEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use think\Exception;
use think\Loader;
use think\Log;

//加载extend目录下的第三方API
Loader::import('WxPay.WxPay',EXTEND_PATH,'.Api.php');

class Pay {
    private $orderId;
    private $orderNo;

    public function __construct($orderId) {
        if(!$orderId){
            throw new Exception('订单号不允许为空');
        }
        $this->orderId = $orderId;
    }

    public function pay(){
        //对订单有效性进行检测
        $this->isValidOrder();

        // 4.进行库存量检查
        $os = new OrderService();
        $status = $os->checkOrderStock($this->orderId);
        if(!$status['pass']){
            return $status;
        }
        return $this->makeWxPreOrder($status['orderPrice']);
    }

    /**
     * 提交微信支付预订单,返回用户微信支付参数
     * @param $totalPrice
     * @throws Exception
     * @throws TokenException
     */
    private function makeWxPreOrder($totalPrice){
        $openid = TokenService::getCurrentTokenVar('openid');
        if(!$openid){
            throw new TokenException();
        }
        $wxOrderData = new \WxPayUnifiedOrder();
        $wxOrderData->SetOut_trade_no($this->orderNo);  //设置交易订单号
        $wxOrderData->SetOpenid($openid);       //设置交易用户openid
        $wxOrderData->SetTrade_type('JSAPI');   //设置交易类型， 'JSAPI'表示当前交易为小程序
        $wxOrderData->SetTotal_fee($totalPrice * 100);   //设置支付总金额，以分为单位
        $wxOrderData->SetBody('零食商贩');
        $wxOrderData->SetNotify_url(config('secure.pay_back_url'));    //向微信支付提交预订单之后，微信支付接口向服务器返回参数的服务器指定接口

        return $this->getPaySignature($wxOrderData);
    }

    /**
     * 获取微信支付签字
     *
     * @param $wxOrderData
     * @throws \WxPayException
     */
    private function getPaySignature($wxOrderData){
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);   //向微信服务器申请签名
        if($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS'){
            Log::record($wxOrder, 'error');
            Log::record('获取预支付订单失败','error');
        }
        $this->recordPreOrder($wxOrder);
        return $this->sign($wxOrder);
    }

    /**
     * 获取需要传递给小程序客户端拉起微信支付的参数
     * @param $wxOrder
     * @return array
     */
    private function sign($wxOrder){
        $jsApiPayData = new \WxPayJsApiPay();
        $jsApiPayData->SetAppid(config('wx.app_id'));
        $jsApiPayData->SetTimeStamp((string)time());

        $rand = md5(time() . mt_rand(0,1000));
        $jsApiPayData->SetNonceStr($rand);

        $jsApiPayData->SetPackage('prepay_id=' . $wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');

        $sign = $jsApiPayData->MakeSign();      //讲设置好的参数通过md5算法生成签字
        $rawValue = $jsApiPayData->GetValues();   //讲对象数据抽取成数组
        $rawValue['sign'] = $sign;

        unset($rawValue['appId']);
        return $rawValue;
    }

    /**
     * 把prepay_id保存到数据库 Order表中对应的订单中
     * @param $wxOrder
     */
    private function recordPreOrder($wxOrder){
        OrderModel::where('id','=',$this->orderId)->update([
            'prepay_id' => $wxOrder['prepay_id']
        ]);
    }

    private function isValidOrder(){
        //1.判断该订单是否存在
        $order = OrderModel::where('id', '=', $this->orderId)->find();
        if(!$order){
            throw new OrderException();
        }
        // 2.判断当前用户是否与订单用户为同一用户
        $checkedUID = $order->user_id;
        if(!TokenService::isValidOperate($checkedUID)){
            throw new ForbiddenException([
                'msg' => '当前用户与订单用户不匹配!',
                'errCode' => 10003
            ]);
        }
        // 3.判断该用户是否已经被支付
        if($order->status != OrderStatusEnum::UNPAID){
            throw new OrderException([
                'code' => 400,
                'msg' => '订单已经支付过了',
                'errCode' => 80003
            ]);
        }
        $this->orderNo = $order->order_no;
        return true;
    }
}