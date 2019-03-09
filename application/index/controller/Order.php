<?php
    namespace app\index\controller;
    use think\Controller;
    class Order extends Common{
        //订单展示
        public function confirmOrder(){
            $cart_id=input("param.cart_id",0);
            $this->getIndexCateInfo();
            $cartInfo=$this->getConfirmOrder($cart_id);
            if(empty($cartInfo)){
                $this->error("订单无数据","Cart/buycar");
            }
            $user_id=$this->getUserId();
            $addressWhere=[
                'user_id'=>$user_id,
            ];
            $addressInfo=$this->getAddressArea($addressWhere);
            $this->assign("addressInfo",$addressInfo);
            $this->assign("cartInfo",$cartInfo);
            return view();
        }
        //提交订单
        public function orderConfirm(){
            $cart_id=input("param.cart_id",0);
            $cartInfo=$this->getConfirmOrder($cart_id);
            if(empty($cart_id)){
                $this->error("请先选择结算商品","Cart/buycar");
            }
            $user_id=$this->getUserId();
            $order_model=model("order");
            $order_model->startTrans();
            try{
                //订单表数据写入
                $order_Info=[];
                $order_number=$this->getOrderNumber($user_id);
                $order_Info['order_number']=$order_number;
                $order_Info['user_id']=$user_id;
                $order_amount=0;
                $order_score=0;
                foreach($cartInfo as $k=>$v){
                    $order_amount+=$v['buy_number']*$v['goods_selfprice'];
                    $order_score+=$v['buy_number']*$v['goods_score'];
                }
                $order_Info['order_amount']=$order_amount;
                $order_Info['order_score']=$order_score;
                $type=input("param.type",0,"intval");
                $order_Info['pay_type']=$type;
                if($type==2){
                    $order_Info['order_status']=4;
                }
                $order_Info['order_message']=input("param.order_message",0);
                $res1=$order_model->save($order_Info);
                if(empty($res1)){
                    $order_model->rollback();
                    throw new \Exception("下单失败");
                }
                $order_id=$order_model->order_id;
                //dump($order_id);die;
                //订单详情表数据写入
                $orderDetail=model("Orderdetail");
                $order_detail=[];
                foreach($cartInfo as $k=>$v){
                    //dump($cartInfo);die;
                    $order_detail[]=[
                        'user_id'=>$user_id,
                        'order_id'=>$order_id,
                        'goods_id'=>$v['goods_id'],
                        'goods_name'=>$v['goods_name'],
                        'goods_img'=>$v['goods_img'],
                        'goods_selfprice'=>$v['goods_selfprice'],
                        'buy_number'=>$v['buy_number'],
                    ];
                }
                $res2=$orderDetail->saveAll($order_detail);
                if(empty($res2)){
                    $order_model->rollback();
                    throw new \Exception("下单失败");
                }
                //订单收货地址表 数据写入
                $address_id=input("param.address_id");
                $order_address=[];
                $addressWhere=[
                    'user_id'=>$user_id
                ];
                $addressInfo=$this-> getAddressArea($addressWhere,$address_id);
                $order_address['order_id']=$order_id;
                $order_address['user_id']=$user_id;
                $order_address['province']=$addressInfo['address_province'];
                $order_address['city']=$addressInfo['address_city'];
                $order_address['area']=$addressInfo['address_area'];
                $order_address['address_name']=$addressInfo['address_name'];
                $order_address['address_tel']=$addressInfo['address_tel'];
                $order_address['address_detail']=$addressInfo['address_detail'];
                $order_addressmodel=model("Orderaddress");
                $res3=$order_addressmodel->save($order_address);
                //dump($res3);die;
                if(empty($res3)){
                    $order_model->rollback();
                    throw new \Exception("下单失败");
                }
                //购物车表 数据清空
                $cartWhere=[
                    'user_id'=>$user_id,
                    'cart_id'=>["in",$cart_id]
                ];
                $cartData=[
                    'status'=>2,
                    'buy_number'=>0
                ];
                $cart_model=model("Cart");
                $res4=$cart_model->where($cartWhere)->update($cartData);
                if(empty($res4)){
                    $order_model->rollback();
                    throw new \Exception("下单失败");
                }
                //商品表 修改库存
                $goods_model=model("goods");
                foreach($cartInfo as $k=>$v){
                    $goodsWhere=[
                        'goods_id'=>$v['goods_id']
                    ];
                     $goodsData=[
                        'goods_num'=>$v['goods_num']-$v['buy_number']
                    ];
                $res5=$goods_model->where($goodsWhere)->update($goodsData);
                }
                //dump($res5);die;
                if(empty($res5)){
                    $order_model->rollback();
                    throw new \Exception("下单失败");
                }
                $order_model->commit();
                echo json_encode(['font'=>'下单成功','code'=>1,'order_id'=>$order_id]);
            }catch(\Exception $e){
                fail($e->getMessage());
            }

        }
        //得到购物车信息
        public function getConfirmOrder($cart_id){
            $user_id=$this->getUserId();
            if(!$this->checkUserLogin()){
                $this->error("请先登录","Login/login");
            }
            if(empty($cart_id)){
                $this->error("请先选择结算商品","Cart/buycar");
            }
            $cart=model("Cart");
            $cartWhere=[
                'user_id'=>$user_id,
                'status'=>1,
                'cart_id'=>['in',$cart_id],
                'goods_up'=>1
            ];
            $cartInfo=$cart
                    ->alias("c")
                    ->join("goods g","c.goods_id=g.goods_id")
                    ->where($cartWhere)
                    ->select();
           return $cartInfo;
        }
        //获取订单号
        public function getOrderNumber($user_id){
            //标志 +年月日 +用户id4位随机数 
            $date=date("ymd");
            $user_id=str_repeat(0,4-strlen($user_id)).$user_id;
            $order_number="1".$date.$user_id.rand(1000,9999);
            return $order_number;
        }
        //支付成功
        public function orderSuccess(){
            $addressInfo=$this->getIndexCateInfo();
            $order_id=input("param.order_id");
            $orderWhere=[
                'order_id'=>$order_id
            ];
            $order=model("order");
            $orderInfo=$order->where($orderWhere)->find();
            $this->assign("orderInfo",$orderInfo);
            $this->assign("addressInfo",$addressInfo);
            return view();
        }
        //我的订单
        public function myOrder(){
            $addressInfo=$this->getIndexCateInfo();
            $this->getIndexCateInfo();
            return view();
        }
        //支付宝支付
        public function alipay(){
            //获取订单号
            $order_number=input("param.order_number",0);
            //查询订单信息
            $orderWhere=[
                'order_number'=>$order_number
            ];
            $orderInfo=$this->getOrderInfo($orderWhere);
            if(empty($orderInfo)){
                $this->error("订单不存在",url('Index/index'));
            }
            //dump($orderInfo);die;
            //支付宝支付
            $config=config('alipay_config');
            require_once EXTEND_PATH.'alipay/pagepay/service/AlipayTradeService.php';
            require_once EXTEND_PATH.'alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';
            //商户订单号，商户网站订单系统中唯一订单号，必填
            $out_trade_no =$orderInfo['order_number'];
            //订单名称，必填
            $subject = '商品';
            //付款金额，必填
            $total_amount =$orderInfo['order_amount'] ;
            //商品描述，可空
            $body ='';
            //构造参数
            $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
            $payRequestBuilder->setBody($body);
            $payRequestBuilder->setSubject($subject);
            $payRequestBuilder->setTotalAmount($total_amount);
            $payRequestBuilder->setOutTradeNo($out_trade_no);
            $aop = new \AlipayTradeService($config);
            /**
             * pagePay 电脑网站支付请求
             * @param $builder 业务参数，使用buildmodel中的对象生成。
             * @param $return_url 同步跳转地址，公网可以访问
             * @param $notify_url 异步通知地址，公网可以访问
             * @return $response 支付宝返回的信息
            */
            $response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);
            //输出表单
            var_dump($response);
        }
        //支付成功
        public function paysuccess(){
            //echo "同步成功";
            $param=input("get.");

            //验证订单号
            $orderWhere=[
                'order_number'=>$param['out_trade_no']
            ];
            $orderInfo=$this->getOrderInfo($orderWhere);
            if(empty($orderInfo)){
                $this->error("当前支付的订单号不存在",url('Index/index'));
            }
            //验证订单金额
            if($orderInfo['order_amount']!=$param['total_amount']){
                $this->error("当前支付金额有误",url('Index/index'));
            }
            //验证签名
            $config=config("alipay_config");
            require_once EXTEND_PATH.'alipay/pagepay/service/AlipayTradeService.php';
            $arr=$_GET;
            $alipaySevice = new \AlipayTradeService($config); 
            $result = $alipaySevice->check($arr);
            //dump($result);die;
            if($result){
                if ($orderInfo['pay_status'] == 1) {
                    $data = [
                        'pay_status' => 2,
                        'order_status' => 3,
                        'pay_time' => time(),
                    ];
                    $where = [
                        'order_number' =>$param['out_trade_no']
                    ];
                    $order_model = model('Order');
                    $order_model->where($where)->update($data);
                $this->getIndexCateInfo();
                $this->assign("orderInfo",$orderInfo);
                return view();
            }
            else {
                //验证失败
                echo "验证失败";
            }
        }
    }
        //异步
        public function notify()
        {
            $param = input("post.");
            $filename = "/data/wwwroot/default/shop/notify.log";
            file_put_contents(
                $filename,
                print_r($param, true),
                FILE_APPEND
            );
            //验证订单是否正确
            $order_number = $param['out_trade_no'];
            $orderWhere = [
                'order_number' => $order_number
            ];
            $orderInfo = $this->getOrderInfo($orderWhere);
            if (empty($orderInfo)) {
                file_put_contents(
                    $filename,
                    'order_number not found',
                    FILE_APPEND
                );
                echo 'order_number error';
                exit;
            }

            //验证订单金额
            if ($orderInfo['order_amount'] != $param['total_amount']) {
                file_put_contents(
                    $filename,
                    'order_amount error',
                    FILE_APPEND
                );
                echo 'order_amount error';
                exit;
            }
            //验证签名
            $config = config("alipay_config");
            require_once EXTEND_PATH . 'alipay/pagepay/service/AlipayTradeService.php';
            $alipaySevice = new \AlipayTradeService($config);
            $result = $alipaySevice->check($param);
            if (empty($result)) {
                file_put_contents(
                    $filename,
                    'sign error',
                    FILE_APPEND
                );
                echo 'sign error';
                exit;
            }
            //验证应用id
            if ($config['app_id'] != $param['app_id']) {
                file_put_contents(
                    $filename,
                    'app_id error',
                    FILE_APPEND
                );
                echo 'app_id error';
                exit;
            }
            //验证状态
            if ($orderInfo['pay_status'] == 2) {
                file_put_contents(
                    $filename,
                    'pay_status pass',
                    FILE_APPEND
                );
                echo 'success';
            }
            //改支付状态
            if ($orderInfo['pay_status'] == 1) {
                $data = [
                    'pay_status' => 2,
                    'order_status' => 3,
                    'pay_time' => time(),
                    'utime' => time(),
                ];
                $where = [
                    'order_number' => $order_number
                ];
                $order_model = model('Order');
                $res = $order_model->where($where)->update($data);
                if ($res) {
                    file_put_contents(
                        $filename,
                        'pay_status pass',
                        FILE_APPEND
                    );
                    echo 'success';
                } else {
                    file_put_contents(
                        $filename,
                        'pay_status fail',
                        FILE_APPEND
                    );
                    echo 'fail';
                    exit;
                }
            }
        }
    }
?>    