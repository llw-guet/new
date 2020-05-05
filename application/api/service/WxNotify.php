<?php


namespace app\api\service;

use app\api\service\Order as OrderService;
use app\api\model\Order as OrderModel;
use app\api\model\Product as ProductModel;
use app\lib\enum\OrderStatusEnum;
use think\Db;
use think\Loader;
use think\Log;

Loader::import('WxPay.WxPay', EXTEND_PATH,'.Api.php');

class WxNotify extends \WxPayNotify {
    public function NotifyProcess($data, &$msg) {
        //如果用户支付成功
        if($data['result_code'] == 'SUCCESS'){
            $order_no = $data['out_trade_no'];
            Db::startTrans();
            $order = OrderModel::where('order_no', '=', $order_no)->find();
            try {
                $orderId = $order->id;
                if ($order->status == 1) {
                    $stockStatus = (new OrderService())->checkOrderStock($orderId);
                    if ($stockStatus['pass']) {
                        $this->updateOrderStatus($orderId, true);
                        $this->reduceStock($stockStatus);
                    } else {
                        $this->updateOrderStatus($orderId, false);
                    }
                }
                Db::commit();
                return true;
            } catch (\Exception $e) {
                Log::record($e);
                Db::rollback();
                return false;
            }
        }else{
            return true;
        }
    }

    /**
     * 更改订单状态
     * @param $orderId
     * @param $hasStock
     */
    private function updateOrderStatus($orderId, $hasStock){
        OrderModel::where('id','=',$orderId)->update([
            'status' => $hasStock ? OrderStatusEnum::PAID : OrderStatusEnum::PAID_BUT_OUT_OF
        ]);
    }

    /**
     * 更新库存量
     * @param $stockStatus
     * @throws \think\Exception
     */
    private function reduceStock($stockStatus){
        foreach ($stockStatus['pStatusArray'] as $eachProduct) {
            ProductModel::where('id','=',$eachProduct['id'])->setDec('stock', $eachProduct['count']);
        }
    }
}