<?php


namespace app\api\service;

use app\api\model\OrderProduct as OrderProductModel;
use app\api\model\Product as ProductModel;
use app\api\model\Order as OrderModel;
use app\api\model\UserAddress;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use think\Db;


class Order {
    //当前下单用户的Uid
    protected $uid;

    //当前用户下单的商品列表
    protected $oProducts;

    //当前用户下单的商品库存列表
    protected $products;


    //查询当前用户下单商品列表的库存数量
    private function getProductsByOrder($oProducts){
        //使用一个空数组保存用户下单商品列表的商品id
        $productIds = [];
        foreach ($oProducts as $product) {
            array_push($productIds, $product['product_id']);
        }
        return ProductModel::all($productIds)->visible(['id','price','stock','name','main_img_url'])->toArray();
    }

    //用户下单
    public function place($uid, $oProduct){
        $this->uid = $uid;
        $this->oProducts = $oProduct;
        $this->products = $this->getProductsByOrder($oProduct);
        $status = $this->getOrderStatus();
        if(!$status['pass']){
            //订单创建失败， 返回订单号为 -1
            $status['order_id'] = -1;
            return $status;
        }else{
            //订单创建成功，开始创建订单
            //生成订单快照
            $orderSnap = $this->snapOrder($status);
            $order = $this->createOrder($orderSnap);
            $order['pass'] = true;
            return $order;
        }
    }

    private function createOrder($orderSnap){
        try {
            Db::startTrans();
            $order = new OrderModel();
            $orderNo = self::makeOrderNo();
            $order->data([
                'order_no' => $orderNo,
                'user_id' => $this->uid,
                'total_price' => $orderSnap['orderPrice'],
                'snap_img' => $orderSnap['snapImg'],
                'snap_name' => $orderSnap['snapName'],
                'total_count' => $orderSnap['totalCount'],
                'snap_items' => json_encode($orderSnap['pStatus']),
                'snap_address' => $orderSnap['snapAddress']
            ])->save();

            $orderId = $order->id;//订单在数据库表中的ID
            $create_time = $order->create_time;//订单创建时间

            //把订单中的商品信息写入 order_product表中
            $orderProduct = new OrderProductModel();
            $data = [];//新建一个数组保存所有产品
            foreach ($this->oProducts as $oProduct) {
                $oProduct['order_id'] = $orderId;
                array_push($data, $oProduct);
            }
            $orderProduct->saveAll($data);
            Db::commit();
            return [
                'order_id' => $orderId,
                'order_no' => $orderNo,
                'create_time' => $create_time
            ];
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    /**
     * @return string  随机生成订单号
     */
    public static function makeOrderNo() {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;
    }

    /**
     * @param $status   传入用户下单的订单状态
     * @return array    订单快照信息
     * @throws UserException    找不到用户地址
     */
    private function snapOrder($status){
        $snap = [
            'snapName' => '',    //订单快照名称
            'snapImg' => '',    //订单快照缩略图
            'snapAddress' => '',    //订单快照地址
            'totalCount' => '',     //订单快照商品购买总数
            'orderPrice' => '',     //订单快照总商品购买价格
            'pStatus' => []     //订单快照中各个商品的状态
        ];
        $snap['snapName'] = $this->products[0]['name'];
        $snap['snapImg'] = $this->products[0]['main_img_url'];
        $snap['orderPrice'] = $status['orderPrice'];
        $snap['totalCount'] = $status['totalCount'];
        $snap['pStatus'] = $status['pStatusArray'];
        $snap['snapAddress'] = $this->getUserAddress();

        if(count($this->oProducts) > 1){
            $snap['snapName'] .= '等';
        }
        return $snap;
    }

    /**
     * 获取用户地址
     * @return false|string
     * @throws UserException
     */
    private function getUserAddress(){
        $address = UserAddress::where('user_id', '=', $this->uid)->find();
        if(!$address){
            throw new UserException([
                'msg' => '用户地址不存在，下单失败！',
                'errCode' => 60001
            ]);
        }
        return json_encode($address->toArray());
    }

    /**
     * 获取整个订单的状态
     * @return array
     * @throws OrderException
     */
    private function getOrderStatus(){
        $status = [
            'pass' => true,
            'orderPrice' => 0,
            'totalCount' => 0,
            'pStatusArray' => []
        ];
        foreach ($this->oProducts as $oProduct) {
            $pStatus = $this->getProductStatus(
                $oProduct['product_id'], $oProduct['count'], $this->products
            );
            if(!$pStatus['haveStock']){
                $status['pass'] = false;
            }
            $status['orderPrice'] += $pStatus['totalPrice'];
            $status['totalCount'] += $pStatus['count'];
            array_push($status['pStatusArray'], $pStatus);
        }
        return $status;
    }

    /**
     * @param $oPID   当前用户下单的产品ID
     * @param $oCount   当前用户下单的产品数量
     * @param $products    当前用户下单的所有产品在库存中的状态
     * @return array    返回单个商品的状态
     * @throws OrderException
     */
    private function getProductStatus($oPID, $oCount, $products){
        $pStatus = [
            'id' => null,
            'name' => '',
            'haveStock' => false,
            'count' => 0,
            'totalPrice' => 0
        ];
        $pIndex = -1;   //订单中指定商品在查找结果集合中的索引
        for($i=0;$i<count($products);$i++){
            if($oPID == $products[$i]['id']){
                $pIndex = $i;
            }
        }
        if($pIndex == -1){
            throw new OrderException(['msg' => '商品不存在，创建订单失败']);
        }else{
            $product = $products[$pIndex];
            $pStatus['id'] = $product['id'];
            $pStatus['count'] = $oCount;
            $pStatus['name'] = $product['name'];
            $pStatus['totalPrice'] = $product['price'] * $oCount;
            if($product['stock'] - $oCount >= 0){
                $pStatus['haveStock'] = true;
            }
        }
        return $pStatus;
    }

    public function checkOrderStock($orderId){
        $oProduct = OrderProductModel::where('order_id', '=', $orderId)->select();
        $this->oProducts = $oProduct;
        $products = $this->getProductsByOrder($oProduct);
        $this->products = $products;
        return $this->getOrderStatus();
    }
}