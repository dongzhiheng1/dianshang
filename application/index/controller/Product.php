<?php
namespace app\index\controller;
//use think\Controller;
use \page\AjaxPage;
class Product extends Common
{
    //商品页面
    public function product()
    {  
        $goods_id=input("param.goods_id",0,"intval");
        //dump($goods_id);exit;
        if(empty($goods_id)){
            $this->error("请选择商品");exit;
        }
        $where=[
            "goods_id"=>$goods_id
        ];
        $goods=model("goods");
        $goodsInfo=$goods->where($where)->find();

        /* 
        $brand_id=$goodsInfo['brand_id'];
       
        $brand=model("brand");
        $brandWhere=[
            'brand_id'=>$brand_id,
        ];
        $brandInfo=$brand->where($brandWhere)->find();

        $cate_id=$goodsInfo['cate_id'];
        $cate=model("cate");
        $cateWhere=[
            'cate_id'=>$cate_id
        ];
        $cInfo=$cate->where($cateWhere)->find();
        //echo $cate->getLastSql();exit;

        $ca_id=$cInfo['pid'];
        $caWhere=[
            'cate_id'=>$ca_id
        ];
        $caInfo=$cate->where($caWhere)->find();


        $cat_id=$caInfo['pid'];
        $catWhere=[
            'cate_id'=>$cat_id
        ];
        $catInfo=$cate->where($catWhere)->find();*/

        $goodsInfo["goods_imgs"]=ltrim($goodsInfo["goods_imgs"],"|");
        $goodsInfo["goods_imgs"]=explode("|",$goodsInfo["goods_imgs"]);
       
        if(empty($goodsInfo)){
            $this->error("没有此商品,请重新选择");exit;
        }
        if($goodsInfo['goods_up']==2){
            $this->error("该商品已下架");exit;
        }
        
        //记录浏览记录
        if($this->checkUserLogin()){
            //存数据库
            $this->saveHistoryDb($goods_id);
        }else{
            //存cookie
            $this->saveHistoryCookie($goods_id);
        }
        $str_name=$this->getCrumbs($goodsInfo['cate_id']);
        
        $this->assign("goodsInfo",$goodsInfo);
        $this->assign("str_name",$str_name);
       /*  $this->assign("brandInfo",$brandInfo);
        $this->assign("cInfo",$cInfo);
        $this->assign("caInfo",$caInfo);
        $this->assign("catInfo",$catInfo); */
        $this->getIndexCateInfo();
        return view();
    
    }
    //商品列表
    public function ProductList(){
        //历史记录查询
        $this->getIndexCateInfo();
        //查询所有品牌
        $cate_id=input("param.cate_id",0,"intval");
        //查询顶级分类
        $cate=model("cate");
        $cWhere=[
            'cate_id'=>$cate_id
        ];
        $catInfo=$cate->where($cWhere)->find();
        /* if(empty($cate_id)){
            echo "全部商品";
        } */
        //dump($cate_id);die;
        if(empty($cate_id)){
            $where=[
                'goods_up'=>1
            ];
            cookie('cate_id',null);
        }else{
            $cateInfo=model("Cate")->where(['is_show'=>1])->select();
            //echo model("Cate")->getLastSql();die;
            $cate_id=getCateId($cateInfo,$cate_id);
            $cate_id=implode(",",$cate_id); 
            cookie("cate_id",$cate_id);
            //dump($cate_id);die;
            $where=[
                'goods_up'=>1,
                'cate_id'=>['in',$cate_id]
            ];
        }
        $brand=model("brand");
        $brandInfo=$brand->field("distinct(g.brand_id),brand_name")
        ->alias("b")
        ->join("shop_goods g","b.brand_id=g.brand_id")
        ->where($where)
        ->select();
        //echo $brand->getLastSql();die;

        //获取价格区间
        $goods=model("Goods");
        $max_price=$goods->where($where)->value("max(goods_selfprice)");
        //echo $goods->getLastSql();die;
        $priceInfo=$this->getPriceSection($max_price);

        //获取默认商品数据
        $p=1;
        //$p=input("param.page");
        $page_num=20;
        $goodsInfo=$goods
        ->where($where)
        ->order("sale_num","desc")
        ->page($p,$page_num)
        ->select();
        $count=$goods->where($where)->count();
        $page_str=AjaxPage::ajaxpager($p,$count,$page_num,url("Product/productsearch"),"show");
        //查询浏览历史记录
        if($this->checkUserLogin()){
            $goodsHistory=$this->getHistoryDb();
        }else{
            $goodsHistory=$this->getHistoryCookie();
        } 
        $this->assign("goodsHistory",$goodsHistory);
        $this->assign("brandInfo",$brandInfo);
        $this->assign("priceInfo",$priceInfo);
        $this->assign("goodsInfo",$goodsInfo);
        $this->assign("count",$count);
        $this->assign("page_str",$page_str);
        $this->assign("catInfo",$catInfo);
        return view();
    }
    //价格区间
    public function getPriceSection($max_price){
        $price=[];
        for($i=0;$i<6;$i++){
            $start=($max_price/7)*$i;
            $end=($max_price/7)*($i+1)-0.01;
            $price[]=number_format($start,2,".",",")."-".number_format($end,2,".",",");
        }
        $price[]=number_format($end+0.01,2,".",",")."以上";
        return $price;
    }
    //获取价格
    public function getPrice(){
        //品牌id
        $brand_id=input("param.brand_id",0,"intval");
        $goods=model("goods");
        $where=[
            "goods_up"=>1,
            "brand_id"=>$brand_id
        ];
        $max_price=$goods->where($where)->order("goods_selfprice","desc")->value("goods_selfprice");
       //echo $max_price;exit;
       if(!empty($max_price)){
        $price=$this->getPriceSection($max_price);
        echo json_encode($price);
       }else{
           fail("此品牌下没有商品");
       }
       
    }
    //搜索
    public function productSearch(){
        $p=input("param.p",1,'intval');
        $brand_id=input("param.brand_id",'','intval');
        $price=input("param.price",'');
        //dump($price);die;
        $price=str_replace(',',"",$price);
        //dump($price);die;
        $flag=input("param.flag",'');
        $order=input("param.order",'desc');
        $cate_id=cookie("cate_id");
        //dump($cate_id);exit;
        //dump($flag);die;
        /*  dump($p);

        dump($brand_id);

        dump($price);
        
        dump($flag);

        dump($order); */
        //条件
        $where=[];
        if(!empty($cate_id)){
            $where['cate_id']=["in",$cate_id];
        }
        //print_r($where);die;
        if(!empty($brand_id)){
            $where['brand_id']=$brand_id;
        }
        if(!empty($price)){
            if(substr_count($price,"-")>0){
                $arr=explode("-",$price);
                //echo $arr;die;
                $where['goods_selfprice']=["between",$arr];
            }else{
                $up=strpos($price,"以");
                $up2=substr($price,0,$up);
                $where['goods_selfprice']=['>=',$up2];
            }
        }
        
        //筛选
        
        $field="sale_num";
        if($flag==2){
            $field="sale_num";
        }else if($flag==3){
            $field="goods_selfprice";
        }else if($flag==4){
            $where['goods_new']=1;
        }
           
           
        //查询商品
        $goods=model("goods");
        $page_num=20;
        $goodsInfo=$goods->where($where)->order($field,$order)->page($p,$page_num)->select();
        //echo $goods->getLastSql();die;
        //使用分页类 获取页码
        $count=$goods->where($where)->count();
        $page_str=AjaxPage::ajaxpager($p,$count,$page_num,url("Product/productsearch"),"show");
        $this->assign("goodsInfo", $goodsInfo);
        $this->assign("page_str", $page_str);
        $this->assign("count", $count);
        $this->view->engine->layout(false); 
        echo $this->fetch("detail");
    }
    //存储历史记录到cookie
    public function saveHistoryCookie($goods_id){
        //先从cookie中取出cookie
        $ctime=time();
        $prevHistory=cookie("history");
        if(!empty($prevHistory)){
            //解开数组
            $history=unserialize(base64_decode($prevHistory));
            //加入这次浏览记录
            $history[]=['goods_id'=>$goods_id,'ctime'=>$ctime];
            $str=base64_encode(serialize($history));
            cookie("history",$str);   
        }else{
            //$ctime=time();
            $arr[]=['goods_id'=>$goods_id,'ctime'=>$ctime];
            $str=base64_encode(serialize($arr));
            cookie("history",$str);
        }
        
    }
    //存浏览记录到数据库
    public function saveHistoryDb($goods_id){
        //$ctime=time();
        $history=[
            'user_id'=>$this->getUserId(),
            'goods_id'=>$goods_id
        ];
        $model=model("history");
        $model->save($history);
        //echo $model->getLastSql();die;
    }

    //浏览历史
  public function getHistoryDb(){
       $goods=model('goods');
       $goodsWhere=[
           'user_id'=>$this->getUserId()
        ];
        $history=model("history");
        $goods_id=$history->where($goodsWhere)->order("ctime","desc")->column("goods_id");
        if(!empty($goods_id)){
            $goods_id=array_slice(array_unique($goods_id),0,5);
      //print_r($goods_id);die;
      foreach($goods_id as $k=>$v){
          $where=[
              'goods_id'=>$v,
              'goods_up'=>1
          ];
            $goodsInfo[]=$goods->field("goods_id,goods_img,goods_name,goods_score,goods_selfprice")->where($where)->find();
      }
            return $goodsInfo;
        }else{
            return [];
        }
        
   }
   //获取cookie 历史记录
   public function getHistoryCookie(){
       $history=cookie("history");
       $goods=model("goods");
       $historyInfo=unserialize(base64_decode($history));
       if(!empty($historyInfo)){
            $historyInfo=array_reverse($historyInfo);
            foreach($historyInfo as $k=>$v){
                $goods_id[]=$v['goods_id'];
            }
            $goods_id=array_slice(array_unique($goods_id),0,5);
            //dump($goods_id);
            foreach($goods_id as $k=>$v){
                $where=[
                    'goods_id'=>$v,
                    'goods_up'=>1
                ];
                $goodsInfo[]=$goods->field("goods_id,goods_img,goods_name,goods_score,goods_selfprice")->where($where)->find();
            }
            //echo $goods->getLastSql();die;
                return $goodsInfo;
            }else{

                return [];
            }
   } 

   //收藏入库
   public function productCollect(){
       $goods_id=input("goods_id");
       $user_id=$this->getUserId();
       if(empty($user_id)){
           fail("请先登录");
       }
       $collect=model("Collection");
       $where=[
           'goods_id'=>$goods_id,
           'user_id'=>$user_id,
       ];
       $res=$collect->where($where)->find();
       
       if(empty($res)){
        $data=[
        'goods_id'=>$goods_id,
        'user_id'=>$user_id,
        ];
       $res2=$collect->insert($data);
       if($res2===false){
            fail("收藏失败");
       }else{
            successly("收藏成功");
       }
       }else{
        successly("已收藏");  
       }
       

   }
    
}
