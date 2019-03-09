<?php
namespace app\index\controller;
use think\Controller;
class Cart extends Common{
    //加入购物车
    public function addCar(){
        $goods_id=input("param.goods_id",0,"intval");
        $buy_number=input("param.buy_number",0,"intval");

        if(empty($goods_id)){
           fail("请选择商品");
        }
        if(empty($buy_number)){
            fail("请输入需要购买的数量");
        }
        $goodsWhere=[
            'goods_id'=>$goods_id
        ];
        $goods=model("goods");
        $goodsInfo=$goods->where($goodsWhere)->find();
        if(empty($goodsInfo)){
            fail("请选择购买的商品");
        }

        if($this->checkUserLogin()){
            $res=$this->addCartDb($goodsInfo,$buy_number);
        }else{
            $res=$this->addCartCookie($goodsInfo,$buy_number);
        }
        
        if($res){
            successly("加入购物车成功");
        }else{
            fail("加入购物车失败");
        }
    }
    //购物车展示
    public function buyCar(){
        $this->getIndexCateInfo();
        if($this->checkUserLogin()){
            $cartInfo=$this->getCartInfoDb();
            $login=1;
        }else{
            $cartInfo=$this->getCartInfoCookie();
            $login=2;
        }
        if(!empty($cartInfo)){
            $this->assign("cartInfo",$cartInfo);
        }else{
            $this->assign("cartInfo",$cartInfo);
        }
        $this->assign("login",$login);
        return view();
    }
    //购物车存入cookie
    public function addCartCookie($goodsInfo,$buy_number){
        $goods_id=$goodsInfo['goods_id'];
        $time=time();
        //取出cookie信息
        $cateInfo=$this->getCartCookie();
        if(!empty($cateInfo[$goods_id])){
            $cateInfo[$goods_id]['buy_number']=$cateInfo[$goods_id]['buy_number']+$buy_number;
            $cateInfo[$goods_id]['utime']=$time;
            $this->checkNumber($goodsInfo,$cateInfo[$goods_id]['buy_number']);
            $cart=base64_encode(serialize($cateInfo));
            cookie("cart",$cart,3600*24*5);
            return true;
        }else{
            $cateInfo[$goods_id]=[
                'goods_id'=>$goods_id,
                'buy_number'=>$buy_number,
                'ctime'=>$time,
                'utime'=>$time
            ];
            $this->checkNumber($goodsInfo,$buy_number);
            $cart=base64_encode(serialize($cateInfo));
            cookie("cart",$cart,3600*24*5);
            return true;
        }
    }
    //取出购物车信息
    public function getCartCookie(){
        $cart=cookie("cart");
        $carInfo=[];
        if(empty($cart)){
            return $carInfo;
        }else{
            $carInfo=unserialize(base64_decode($cart));
            return $carInfo;
        }
    }
    //购物车信息存数据库
    public function addCartDb($goodsInfo,$buy_number){
        $time=time();
        $goods_id=$goodsInfo['goods_id'];
        $cart=model("cart");
        $cartWhere=[
            'goods_id'=>$goods_id,
            'user_id'=>$this->getUserId()
        ];
        $cateInfo=$cart->where($cartWhere)->find();
        if($cateInfo){
            $update=[
                "buy_number"=>$cateInfo['buy_number']+$buy_number,
                "utime"=>$time,
                "status"=>1
            ];
            $this->checkNumber($goodsInfo,$update['buy_number']);
            $res=$cart->where($cartWhere)->update($update);
            if($res){
                return true;
            }else{
                return false; 
            }
        }else{
            $insert=[
                "goods_id"=>$goods_id,
                "add_price"=>$goodsInfo['goods_selfprice'],
                "buy_number"=>$buy_number,
                "user_id"=>$this->getUserId()
            ];
            $this->checkNumber($goodsInfo,$buy_number);
            $res=$cart->save($insert);
            if($res){
                return true;
            }else{
                return false;
            }

        }
    }
    //购物车修改
    public function cartUpdate(){
        $buy_number=input("param.buy_number",0,"intval");
        
        if(empty($buy_number)){
            fail("请输入购买数量");
        }
        if($this->checkUserLogin()){
            $cart_id=input("param.cart_id",0,"intval");
            if(empty($cart_id)){
                fail("请选择要更改数量的商品");
            }
            $res=$this->updateNumDb($cart_id,$buy_number);
        }else{
            $goods_id=input("param.goods_id",0,"intval");
            if(empty($goods_id)){
                fail("请选择要更改数量的商品");
            }
            $res=$this->updateCookie($goods_id,$buy_number);
        }
        if($res===false){
             fail("修改失败");
        }
    }
    //数据库的数量改变
    public function updateNumDb($cart_id,$buy_number){
        $where=[
            'cart_id'=>$cart_id
        ];
        $data=[
            'buy_number'=>$buy_number
        ];
        $cart=model("cart");
        $cartInfo=$cart->where($where)->update($data);
        return $cartInfo;
    }
    //修改cookie的购买数量
    public function updateCookie($goods_id,$buy_number){
        $cart_str=cookie("cart");
        if(empty($cart_str)){
            fail("购物车太空了");
        }
        $cartInfo=unserialize(base64_decode($cart_str));
        $cartInfo[$goods_id]['buy_number']=$buy_number;
        $cart=base64_encode(serialize($cartInfo));
        $second=60*60*24;
        cookie("cart",$cart,$second);
        return true;
    }
    //删除
    public function cartDel(){
        $cart_id=input("param.cart_id",0);
        $type=input("param.type",0,"intval");
        if($this->checkUserLogin()){
            $cart=model("cart");
            if($type==1){
            $where=[
                'cart_id'=>$cart_id
            ];
            $cartDate=[
                'status'=>2,
                'buy_number'=>0
            ];
            $res=$cart->where($where)->update($cartDate);
            }else if($type==2){
                //dump($cart_id);die;
                $allWhere=[
                    'cart_id'=>['in',$cart_id]
                ];
                $date=[
                    'status'=>2,
                    'buy_number'=>0
                ]; 
                $res=$cart->where($allWhere)->update($date);
            }else{
                if(empty($cart_id)){
                    fail("购物车已清空");
                }
                $clearWhere=[
                    'cart_id'=>['in',$cart_id]
                ];
                $clearDate=[
                    'status'=>2,
                    'buy_number'=>0
                ]; 
                $res=$cart->where($clearWhere)->update($clearDate);
                if($res===false){
                    fail("清空失败");
                }else{
                    successly("清空购物车成功");
                }
            }
                if($res){
                    successly("删除成功");
                }else{
                    fail("删除失败");
                }  
            
        }else{
            if($type==3){
                fail("请先登录");
            }
            $cart_str=cookie("cart");
            $goods_id=input("param.goods_id",0);
            if($type==1){
                $cartInfo=unserialize(base64_decode($cart_str));
                unset($cartInfo[$goods_id]);
                $cart=base64_encode(serialize($cartInfo));
                cookie("cart",$cart);
                successly("删除成功");
            }else{
                $cartInfo=unserialize(base64_decode($cart_str));
                unset($cartInfo[$goods_id]);
                $cart=base64_encode(serialize($cartInfo));
                cookie("cart",$cart);
                successly("删除成功");
            }
            
        }
    }
    //收藏或批量收藏
    public function cartCollection(){
        $user_id=$this->getUserId();
        $goods_id=input("param.goods_id",0);
        if(empty($goods_id)){
            fail("请选择需要收藏的商品");
        }
        $model=model("Collection");
        $type=input("param.type",0,"intval");
        $num=0;
        if($type==1){
            $data=[
                'goods_id'=>$goods_id,
                'user_id'=>$user_id
            ];
            $count=$model->where($data)->count();
               if($count==0){
                    $res=$model->insert($data);
               }else{
                    $num+=1;
               }
               if(!empty($res)){
                if($count>0){
                    successly("收藏成功,有".$num."件商品已收藏");
                }
                
            }else{
                fail("所有商品已收藏");
            }
            successly("收藏成功"); 
        }else{
            $goods_id=explode(",",$goods_id);
            foreach($goods_id as $v){
               $goodsData=['user_id'=>$user_id,'goods_id'=>$v];
               $count=$model->where($goodsData)->count();
               if($count==0){
                    $res=$model->insert($goodsData);
               }else{
                    $num+=1;
               }
            } 
            if(!empty($res)){
                if($count>0){
                    successly("收藏成功,有".$num."件商品已收藏");
                }
                
            }else{
                fail("所有商品已收藏");
            }
            successly("收藏成功");  
        }
        
    }
}