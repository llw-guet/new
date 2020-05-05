<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\model\User as UserModel;
use app\api\model\UserAddress;
use app\api\service\Token as TokenService;
use app\api\validate\AddressNew;
use app\api\validate\IDMustBePositiveInt;
use app\lib\exception\SuccessMessage;
use app\lib\exception\UserException;

class Address extends BaseController {
    protected $beforeActionList = [
        'checkPrimaryScope' => ['only' =>
            ['createOrUpdateAddress', 'getUserAddress']
        ]
    ];

    /**
     * 添加用户地址信息
     * @return \think\response\Json
     * @throws UserException
     * @throws \app\lib\exception\ParameterException
     * @throws \think\exception\DbException
     */
    public function createOrUpdateAddress() {
        $addressValidate = new AddressNew();
        $addressValidate->goCheck();

        // 根据客户端传递过来的token获取uid
        $uid = TokenService::getCurrentUid();

        // 判断uid是否合法，即本地user表是否存在该用户，如果不存在则抛出异常
        $user = UserModel::get($uid, 'address');
        if(!$user){
            throw new UserException();
        }

        // 获取用户从客户端提交来的地址信息
        $newAddress = $addressValidate->getDataByRule(input('post.'));

        // 根据用户地址信息是否存在，从而判断是添加地址还是更改地址
        $address = $user->address;

        if(!$address){    //如果当前用户地址不存在， 则添加
            $user->address()->save($newAddress);   //模型查询返回的结果仍然是模型，可以使用模型的方法
        }else{
            $user->address->save($newAddress);
        }
        return json(new SuccessMessage(), 201);
    }

    public function getUserAddress() {
        $uid = TokenService::getCurrentUid();
        $address = UserAddress::where('user_id', '=', $uid)->find();
        if(!$address){
            throw new UserException([
                'msg' => '用户地址不存在',
                'errCode' => 60001
            ]);
        }
        return $address;
    }
}