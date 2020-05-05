<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\validate\IDMustBePositiveInt;
use app\api\validate\OrderPlace;
use app\api\model\Order as OrderModel;
use app\api\service\Token as TokenService;
use app\api\service\Order as OrderService;
use app\api\validate\PagingParameter;
use app\lib\exception\OrderException;

/**
 * 用户在选择商品之后， 向API提交包含他所选择商品的相关信息
 * API在接收到信息后，需要检查订单相关商品的库存量
 * 库存量大于0，把订单数据存入数据库中， 下单成功了， 返回客户端信息， 告诉客户端可以支付了
 * 用户在支付时，调用我们的支付接口， 进行支付， 同时需要再次对库存数量进行检查
 * 仍有库存， 服务器端就可以调用微信的支付接口进行支付
 * 小程序根据服务器返回的结果拉起微信支付
 * 微信会返回给我们一个支付的结果（异步）
 * 支付成功，仍然需要对库存量进行检查。  然后进行库存量的扣除
 *
 * Class Order
 * @package app\api\controller\v1
 */
class Order extends BaseController {
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'placeOrder'],
        'checkPrimaryScope' => ['only' => 'getSummaryByUser']
    ];

    //下单接口
    public function placeOrder() {
        (new OrderPlace())->goCheck();
        $products = input('post.products/a');
        $uid = TokenService::getCurrentUid();
        $os = new OrderService();
        $status = $os->place($uid, $products);
        return $status;
    }

    /**
     * 获取用户订单列表
     * @param int $page
     * @param int $size
     * @return array
     * @throws \app\lib\exception\ParameterException
     */
    public function getSummaryByUser($page = 1, $size = 15) {
        (new PagingParameter())->goCheck();
        $uid = TokenService::getCurrentUid();
        $orderPage = OrderModel::getSummaryByUser($uid, $page, $size);
        $hidden_data = ['user_id', 'snap_items', 'snap_address', 'prepay_id'];
        if($orderPage->isEmpty()){
            return [
                'data' => [],
                'current_page' => $orderPage->currentPage() //获取当前页码
            ];
        }
        return [
            'data' => $orderPage->hidden($hidden_data)->toArray(),
            'current_page' => $orderPage->currentPage()
        ];
    }

    /**
     * 获取用户某个订单详情
     * @param $id
     * @return OrderModel
     * @throws OrderException
     * @throws \app\lib\exception\ParameterException
     * @throws \think\exception\DbException
     */
    public function getOrderDetail($id){
        (new IDMustBePositiveInt())->goCheck();
        $order = OrderModel::get($id);
        if(!$order){
            throw new OrderException();
        }
        return $order->hidden(['prepay_id']);
    }
}