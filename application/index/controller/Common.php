<?php
namespace app\index\controller;
use think\Controller;
class Common extends Controller
{
    //构造方法
    public function __construct(){
        parent::__construct();
        
        //获取网站配置信息
        $this->getWebConfig();
        //获取购物车数据
        $this->getTopCartInfo(); 
    }
        //防非法登录
    public function checkUserLogin(){
        if(session("?userInfo")){
        $userInfo=session("userInfo");
        return $userInfo;
        }else{
        return false;
        }
    }

        //获取网站配置信息
    public function getWebConfig(){
        if(session("?configInfo")){
            $configInfo_str=session("configInfo");
            $configInfo=unserialize($configInfo_str);
            //dump( $configInfo);die;
        }else{
        $config=model("config");
        $data=$config->select();
        foreach($data as $k=>$v){
            $configInfo[$v['name']]=$v['value'];
        }
        $str=serialize($configInfo);
        //dump($str);
        session("configInfo",$str);
        }
        $this->assign("config", $configInfo);
    }
        //获取分类数据
    public function getIndexCateInfo(){
        //是否展示
        $cate=model("cate");
        $where=[
            'is_show'=>1
        ];
        $data=$cate->where($where)->select();
        $cateInfo=getIndexCateInfo($data);
        //print_r($cateInfo);die;
        $this->assign("cateInfo",$cateInfo);

    }
        //导航栏展示
    public function getCateNav(){
        $cate=model("cate");
        //是否在导航栏展示
        $navwhere=[
            'is_nav'=>1
        ];
        $data=$cate->where($navwhere)->select();
        $cateInfo=getIndexCateInfo($data);
        //print_r($cateInfo);die;
        $this->assign("cateInfo",$cateInfo);
    }
        //session
    public function getUserId(){
        return session("userInfo.user_id");
    }
        //同步历史记录导数据库
    public function asyncHistory(){
        $history=cookie("history");
        if(!empty($history)){
            $arr=unserialize(base64_decode($history));
            $user_id=$this->getUserId();
            $model=model("History");
            foreach($arr as $k=>$v){
            $v['user_id']=$user_id;
            $model->insert($v);
            }
            cookie("history",null);
        }   
    }
        //检查库存
    public function checkNumber($goodsInfo,$buy_number){
        $goods_num=$goodsInfo["goods_num"];
        if($buy_number>$goods_num){
            fail('此商品库存'.$goods_num.'件,您选择的数量以超过库存,最多只能购买'.$goods_num);
        }
    }
        //购物车同步cookie到数据库
    public function asyncCookie(){
        $cart=cookie("cart");
        $cartInfo=unserialize(base64_decode($cart));
        if(!empty($cartInfo)){
            $user_id=$this->getUserId();
            $model=model("cart");
            $goods=model("goods");
            foreach($cartInfo as $k=>$v){
                $goodsInfo= $goods->where(['goods_id'=>$v['goods_id']])->find();
                if(empty($goodsInfo)){
                    fail("请选择您要购买的商品");
                }
                if($goodsInfo['goods_up']==2){
                fail("该商品已下架");
                }

                $where=[
                    'goods_id'=>$v['goods_id'],
                    'user_id'=>$user_id
                ];
                $cartData=$model->where($where)->find();
                if(!empty($cartData)){
                    //改
                    $update=[
                        'buy_number'=>$cartData['buy_number']+$v['buy_number'],
                        'utime'=>time(),
                        'status'=>1
                    ];
                    $model->where($where)->update($update);
                }else{
                    //添加
                    $v['add_price']=$goodsInfo['goods_selfprice'];
                    $v['user_id']=$user_id;
                    $model->insert($v);
                }
                cookie("cart",null);
            }
        }

    }
        //面包屑
    public function getCrumbs($cate_id){
        $cate=model("cate");
        $where=[
            'is_show'=>1,
        ];
        $arr=$cate->where($where)->select();
        $name=getCateName($arr,$cate_id);
        $new_name=array_reverse($name);
        $str='';
        foreach($new_name as $k=>$v){
        $str.="<a href='".url('Product/productList')."?cate_id=".$v['cate_id']."'>".$v['cate_name']."</a>".'>';
        };
        //echo $str;die;
        $cate_str=rtrim($str,">");
        return $cate_str;
    }

    //取cookie中购物车数据
    public function getCartInfoCookie(){
        $cart=cookie("cart");
        //echo $cart;die;
        $cartInfo=unserialize(base64_decode($cart));
        if(!empty($cartInfo)){
        $goods=model("goods");
        foreach($cartInfo as $k=>$v){
            $where=[
                'goods_id'=>$v['goods_id'],
                'goods_up'=>1
            ];
        $goodsInfo=$goods->where($where)->find()->toArray();
        $cartData[]=array_merge($v,$goodsInfo);
        }
        if(empty($cartData)){
            return [];
        }
            return $cartData;
        }else{
            return [];
        }
        
    }
    //取数据库中购物车数据
    public function getCartInfoDb(){
        $user_id=$this->getUserId();
        $cart=model("cart");
        $cartWhere=[
            'user_id'=>$user_id,
            'status'=>1,
            'goods_up'=>1
        ];
        $cartInfo=$cart
                ->field("cart_id,buy_number,add_price,goods_name,goods_num,goods_img,goods_selfprice,c.goods_id")
                ->alias("c")
                ->join("goods g","c.goods_id=g.goods_id")
                ->where($cartWhere)
                ->select();
        if(!empty($cartInfo)){
            return $cartInfo;
        }else{
            return [];
        }
    }
    //顶部数据
    public function getTopCartInfo(){
        if($this->checkUserLogin()){
            $cartInfo=$this->getCartInfoDb();
        }else{
            $cartInfo=$this->getCartInfoCookie();
        }
            $this->assign('cartInfo',$cartInfo);
        if(!empty($cartInfo)){
            $this->assign('is_cartInfo',1);
        }else{
            $this->assign('is_cartInfo',0);
        }
    }
    //获取地区
    public function getAddressArea($where,$address_id=''){
         $address_model=model("Address");
         $Area_model=model("Area");
        if(empty($address_id)){
            $addressInfo=$address_model->where($where)->select();
            if(!empty($addressInfo)){
                  ///print_r($addressInfo);die;
            $id=[];
            foreach($addressInfo as $k=>$v){
                $id[]=$v['address_province'];
                $id[]=$v['address_city'];
                $id[]=$v['address_area'];
            }
            $id=array_unique($id); 
            $areaWhere=[
                'id'=>['in',$id]
            ];
            $areaInfo=$Area_model->where($areaWhere)->select();
            $area_name=[];
            foreach($areaInfo as $k=>$v){
                $area_name[$v['id']]=$v['name'];
            }
            foreach($addressInfo as $k=>$v){
                $addressInfo[$k]['address_province']=$area_name[$v['address_province']];
                $addressInfo[$k]['address_city']=$area_name[$v['address_city']];
                $addressInfo[$k]['address_area']=$area_name[$v['address_area']];
            }
             return $addressInfo;
            }else{
                return [];
            }
        }else{
           $where['address_id']=$address_id;
           $addressInfo=$address_model->where($where)->find();
           if(!empty($addressInfo)){
                $id=[];
                $id[]=$addressInfo['address_province'];
                $id[]=$addressInfo['address_city'];
                $id[]=$addressInfo['address_area'];
                $areaWhere=[
                    'id'=>['in',$id]
                ];
                $areaInfo=$Area_model->where($areaWhere)->select();
                $area_name=[];
                foreach($areaInfo as $k=>$v){
                    $area_name[$v['id']]=$v['name'];
                }
                $addressInfo['address_province']=$area_name[$addressInfo['address_province']];
                $addressInfo['address_city']=$area_name[$addressInfo['address_city']];
                $addressInfo['address_area']=$area_name[$addressInfo['address_area']];
                return  $addressInfo;
           }else{
               return [];
           }
           
        }
        
    }
    //获取订单信息
    public function getOrderInfo($orderWhere){
        $order=model("order");
        $orderInfo=$order->where($orderWhere)->find();
        return  $orderInfo;
    } 
    }
