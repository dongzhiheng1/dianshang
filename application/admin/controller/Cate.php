<?php
namespace app\admin\controller;
//use think\Controller;
class Cate extends  Common{
    //分类添加
    public function cateAdd(){
        if(checkRequest()){
            $data=input("post.");
            //验证器
            $validate=validate("Cate");
            $result=$validate->scene("edit")->check($data);
            if(!$result){
                fail($validate->getError());
            }
            $model=model("Cate");
            $res=$model->save($data);
            if($res){
                successly("添加成功");
            }else{
                fail("添加失败");
            }
        }else{
        $this->getCateInfo();
        return view();
        }
    }
    //分类展示
    public function cateShow(){
        $this->getCateInfo();
       return view();
    }
    //分类数据
    public function getCateInfo(){
       $data=model('Cate')->select();
       $info=getCateInfo($data);
       $this->assign("info",$info);
    }
    //验证唯一
    public function checkName(){
        $cate_name=input("param.cate_name");
        $where=[
            'cate_name'=>$cate_name
        ];
        $res=model("Cate")->where($where)->find();
        if($res){
            return 0;
        }else{
            return 1;
        }
    }
    //即点即改
    public function cateFieldUpdate(){
        $value=input("param.value");
        $field=input("param.field");
        $cate_id=input("param.cate_id");
        if($field=="cate_name"){
            $upWhere=[
               'cate_id'=>['neq',$cate_id],
               'cate_name'=>$value
            ];
            $arr=model("cate")->where($upWhere)->find();
            if($arr){
                fail("分类已存在");
            }
        }if(empty($value)){
                fail("必填项不能为空");
            }
       /*  dump($value);
         dump($field);
         dump($cate_id);exit; */
        $where=[
            'cate_id'=>$cate_id
        ];
        $data=[
            $field=>$value
        ];
        $res=model("Cate")->save($data,$where);
       if($res===false){
           fail("修改失败");
       }else{
           successly("修改成功");
       }

    }
    //删除
    public function cateDel(){
        $cate_id=input("param.cate_id");
        //此分类下是否有子类
        $cateWhere=[
            'pid'=>$cate_id
        ];
        $count=model('cate')->where($cateWhere)->count();
        if($count>0){
           fail("此分类下有子类或商品");
        }
        //查找分类下是否有商品
        $where=[
            "cate_id"=>$cate_id
        ];
        $goods_count=model("goods")->where($where)->count();
        if($goods_count>0){
            fail("此分类下有子类或商品");
        }
        //删除
        $res=model("cate")->where($where)->delete();
        if($res){
            successly("删除成功");
        }else{
            fail("删除失败");
        }
    }
    public function cateUpdate(){
        if(checkRequest()){
            $data=input("param.");
            $cate_id=input("param.cate_id");
            //验证器
            $validate=validate("cate");
            $result=$validate->scene("edit")->check($data);
            if(!$result){
            fail($validate->getError());
            }

            $where=[
                "cate_id"=>$cate_id
            ];
            $res=model("cate")->save($data,$where);
            if($res===false){
                fail("修改失败");
            }else{
                successly("修改成功");
            }

        }else{
            //接收id
            $cate_id=input("param.cate_id");
            $where=[
                'cate_id'=>$cate_id
            ];
            $arr=model("cate")->where($where)->find();
            $this->assign('arr',$arr);
            $this->getCateInfo();
            return view();
        }

    }
}
?>