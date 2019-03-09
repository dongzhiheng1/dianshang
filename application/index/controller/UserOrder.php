<?php
    namespace app\index\controller;
    header("content-type:text/html;charset=utf-8");
    use think\Controller;
    class UserOrder extends Common{
        //我的订单
        public function myOrder(){
            if(!$this->checkUserLogin()){
                $this->error("请先登录");
            }
            $order_model=model("order");
            $orderWhere=[
                'user_id'=>$this->getUserId()
            ];
            $orderInfo=$order_model->where($orderWhere)->select();
            $this->assign("orderInfo",$orderInfo);
            $this->getIndexCateInfo();
            return view();
        }
        //订单详情
        public function orderDetail(){
            $order_number=input("param.order_number");
            $data=$this->getExpressInfo($order_number);
            $this->assign('data',$data);
            return view();

        } 
        //订单
        public function getExpressInfo($order_number){
            $showapi_appid = '83147';  //替换此值,在官网的"我的应用"中找到相关值
            $showapi_secret = '28ebc754d56a49bb9726816e1a94a65b';  //替换此值,在官网的"我的应用"中找到相关值
            $paramArr = array(
            'showapi_appid'=> $showapi_appid,
                'com'=> "zhongtong",
                'nu'=> $order_number,
            //添加其他参数
            );
            //创建参数(包括签名的处理)
            function createParam ($paramArr,$showapi_secret) {
            $paraStr = "";
            $signStr = "";
            ksort($paramArr);
            foreach ($paramArr as $key => $val){
            if ($key != '' && $val != '') {
            $signStr .= $key.$val;
            $paraStr .= $key.'='.urlencode($val).'&';
            }
            }
            $signStr .= $showapi_secret;//排好序的参数加上secret,进行md5
            $sign = strtolower(md5($signStr));
            $paraStr .= 'showapi_sign='.$sign;//将md5后的值作为参数,便于服务器的效验
            return $paraStr;
            }
            $param = createParam($paramArr,$showapi_secret);
            $url = 'http://route.showapi.com/64-19?'.$param;
            $result = file_get_contents($url);
            $result = json_decode($result);
            //print_r($result);die;
            $data=$result->showapi_res_body->data;
            return $data;
        }
        //取消订单
        public function unOrder(){
            $order_number=input("order_number");
            $order=model("Order");
            $order->startTrans();
            try{
                //修改订单表
                $orderWhere=[
                    'order_number'=> $order_number
                ];
                $orderData=[
                    'order_status'=>2
                ];
                $res=$order->where($orderWhere)->update($orderData);
                if($res===false){
                    $order->rollback();
                    throw new \Exception("取消订单失败");
                }
                //根据订单id查订单详情表 根据详情表的goods_id修改商品表库存
                $orderInfo=$this->getOrderInfo($orderWhere);
                $order_id=$orderInfo->order_id;
                $where=[
                    'order_id'=>$order_id
                ];
                $orderDetail=model("Orderdetail");
                $data=$orderDetail->field("goods_id,buy_number")->where($where)->select();
                foreach($data as $k=>$v){
                   $where=[
                       'goods_id'=>$v['goods_id']
                   ];
                   $goods=model("Goods");
                   $goodsSelect=$goods->field("goods_id,goods_num")->where($where)->select();
                   //print_r($goodsSelect);die;
                   foreach($goodsSelect as $kk=>$vv){
                       $goodsData=[
                           'goods_num'=>$v['buy_number']+$vv['goods_num']
                       ];
                    $res2=$goods->where($where)->update($goodsData);
                   }
                  
                } 
                //echo $goods->getLastSql();die;
                if($res2===false){
                    $order->rollback();
                    throw new \Exception("取消订单失败");
                }
                $order->commit();
                successly("取消订单成功");

            }catch(\Exception $e){
                fail($e->getMessage());
            }
        }
        //退款
        public function refund(){
            $order_number=input('get.order_number');
            $goods_amount=input('post.goods_amount');
            //dump($goods_amount);die;
            $where=[
                'order_number'=>$order_number
            ];
            $orderInfo=$this->getOrderInfo($where);
            //dump($orderInfo);die;
            $order_id=$orderInfo->order_id;
            $orderWhere=[
                'order_id'=>$order_id
            ];
            $order_detail=model('Orderdetail');
            $goodsInfo=$order_detail->where($orderWhere)->select();
            $this->assign('goodsInfo',$goodsInfo);
            return view();
        }
    }
?>    