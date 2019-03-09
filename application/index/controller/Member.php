<?php
namespace app\index\controller;
use think\Controller;
class Member extends Common{

    public function member(){
        if(!$this->checkUserLogin()){
            $this->error("请先登录",url('login/login'));
        }
        $time=session('userInfo');
        $this->assign("time",$time);
        return view();
    }
    //收藏列表展示
    public function collectShow(){
        $collect=model("Collection");
        $user_id=$this->getUserId();
        $where=[
            'user_id'=>$user_id,
        ];
        $goods_id=$collect->where($where)->column('goods_id');
        $goods_id=implode(',',$goods_id);
        //print_r($goods_id);die;
        $goods=model("Goods");
        $goodsWhere=[
            'goods_id'=>['in',$goods_id],
        ];
        $goodsInfo=$goods
        ->where($goodsWhere)->select();
        $this->assign('goodsMessage',$goodsInfo);
        return view();
    }
    //收藏单删  批量删除
    public function collectDel(){
        $goods_id=input("param.goods_id");
        $type=input("param.type",0,'intval');
        //dump($type);die;
        if($type==1){
           $where=[
            'goods_id'=>$goods_id
        ];
        $collect=model("collection");
        $res=$collect->where($where)->delete();
        if($res===false){
            fail("删除失败");
        }else{
            successly("删除成功");
        } 
        }else{
            $where=[
                'goods_id'=>['in',$goods_id]
            ];
            $collect=model("collection");
            $res2=$collect->where($where)->delete();
            if($res2===false){
                fail("删除失败");
            }else{
                successly("删除成功");
            } 
        }
        

    }
}