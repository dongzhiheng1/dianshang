<?php
namespace app\index\controller;
//use think\Controller;
class Index extends Common
{
    public function index()
    {
        $goods=model("goods");
        $where=[
            'goods_hot'=>1
        ];
        $goods_id=$goods->where($where)->order("create_time","desc")->column("goods_id");
        if(!empty($goods_id)){
            $goods_id=array_unique($goods_id);
        }
        foreach($goods_id as $k=>$v){
            $goodsWhere=[
                'goods_id'=>$v,
                'goods_up'=>1
            ];
            $hotInfo[]=$goods->where($goodsWhere)->find();
        };
        //获取首页数据 
        //处理数据
        $this->getCateNav();
        $this->getIndexCateInfo();
        $cate_id=1;
        $info=$this->getFloorInfo($cate_id);
        $this->assign("hotInfo",$hotInfo);
        $this->assign('info',$info);
        return view();
    }
    public function getFloorInfo($cate_id){
       //顶层数据
        $model=model("cate");
        $topWhere=[
            'cate_id'=>$cate_id,
            'is_show'=>1
        ];
        $info['topData']=$model->field('cate_id,cate_name')->where($topWhere)->find();
        //获取二级分类
        $cateWhere=[
            'pid'=>$cate_id,
            'is_show'=>1
        ];
        $info['cateList']=$model->field('cate_id,cate_name')->where($cateWhere)->select();


        //获取商品数据
        $cateInfo=$model->where(['is_show'=>1])->select();
        //dump($cateInfo);die;
        $cateId=getCateId($cateInfo,$cate_id);
        //dump($cateId);die;
        $cateId=implode(',',$cateId);

        $goods=model("goods");
        $goodsWhere=[
            'goods_up'=>1,
            'cate_id'=>['in',$cateId]
        ];
        $info['goodsInfo']=$goods->field('goods_id,goods_name,goods_selfprice,goods_score,goods_img')->where($goodsWhere)->select();
        //print_r( $info['goodsInfo']);die;
        return $info;
    }
    public function moreFloor(){
        //接收id
        $cate_id=input("param.cate_id");
        $floor_num=input("param.floor_num");
        $floor_num=$floor_num+1;
        $where=[
            'cate_id'=>['>',$cate_id],
            'pid'=>0,
            'is_show'=>1
        ];
        $cate=model("cate");
        $arr=$cate->field("cate_id")->where($where)->order("cate_id","asc")->find();
        $cate_id=$arr['cate_id'];
        if(!empty($cate_id)){
        //获取楼层数据
        $info=$this->getFloorInfo($cate_id);
        $this->view->engine->layout(false);
        $this->assign('info',$info);
        $this->assign('floor_num',$floor_num);
        echo $this->fetch("div");
        }   
        
    }

}
