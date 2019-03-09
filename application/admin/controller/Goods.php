<?php
namespace app\admin\controller;
//use think\Controller;
class Goods extends  Common{
    public function goodsAdd(){
        if(checkRequest()){
            $data=input("param.");
             //验证器
        $validate=validate("Goods");
        $result=$validate->scene('add')->check($data);
        if(!$result){
            fail($validate->getError());
        }
            $res=model("Goods")->allowField(true)->save($data);
            if($res){
                successly("添加成功");
            }else{
                fail("添加失败");
            }
        }else{
            //查询分类
            $cateInfo=$this->getCateInfo();
            //dump( $cateInfo);exit;
            //查询品牌
            $where=[
                "brand_show"=>1
            ];
            $brandInfo=model("Brand")->where($where)->select();
            $this->assign("cateInfo",$cateInfo);
            $this->assign("brandInfo",$brandInfo);
            return view();
        } 
    }
    //查询分类
    public function getCateInfo(){
        $data=model('Cate')->select();
        $info=getCateInfo($data);
        return $info;
     }
     //上传图片
     public function goodsUpload(){
        $type=input("param.type");
        $dir=$type==1?'goods_img':'goods_imgs';
        // 获取表单上传文件 例如上传了001.jpg
        $info=$this->upload($dir);     
     }
    public function upload($dir){
        $file = request()->file('file');
        //dump($arr);die;  
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . $dir);
        if($info){
            $arr=[
                'code'=>1,
                'font'=>'上传成功',
                'src'=>$info->getSaveName()
            ];
            echo json_encode($arr);
        }else{
            // 上传失败获取错误信息
            fail($file->getError());
        }
        //return $info;
    }

    public function goodsShow(){
         //查询分类
         $cateInfo=$this->getCateInfo();
         //dump( $cateInfo);exit;
         //查询品牌
         $where=[
             "brand_show"=>1
         ];
         $brandInfo=model("Brand")->where($where)->select();
         $this->assign("cateInfo",$cateInfo);
         $this->assign("brandInfo",$brandInfo);
         return view();
        //echo json_encode($info);
        return view();
    }
    //goodsList数据接口
    public function goodsList(){
        $page=input("param.page");
        $limit=input("param.limit");
        $cate_name=input("param.cate_name");
        $goods_name=input("param.goods_name");
        $brand_name=input("param.brand_name");
        $where=[];
       if(!empty($goods_name)){
           $where['goods_name']=['like',"%$goods_name%"];
       };
       if(!empty($cate_name)){
        $where['cate_name']=$cate_name;
        };
        if(!empty($brand_name)){
        $where['brand_name']=$brand_name;
        };
        $count=model("Goods")->getCount($where);
        $data=model("goods")->getGoodsInfo($page,$limit,$where);
        $info=[
            'code'=>0,
            'msg'=>'',
            'count'=>$count,
            'data'=>$data
        ];
        echo json_encode($info);
    }
    //即点即改
    public function goodsFileUpdate(){
        $field=input("param.field");
        $value=input("param.value");
        $goods_id=input("param.goods_id");
        if($field=="goods_name"){
            $where=[
                'goods_id'=>['neq',$goods_id],
                'goods_name'=>$value
            ];
            $result=model("goods")->where($where)->find();
            if($result){
                fail("商品名称已存在");
            }
            }
            if(empty($value)){
                fail("必填项不能为空");
            }

        //修改
        $updateWhere=[
            'goods_id'=>$goods_id
        ];
        $data=[
            $field=>$value
        ];
        $res=model("goods")->save($data,$updateWhere);
        //echo model("goods")->getLastSql();exit;
        if($res){
           successly("修改成功");
        }else{
            fail("修改失败"); 
        }
    }
    //删除
    public function goodsDel(){
        $goods_id=input("param.goods_id");
        $where=[
            'goods_id'=>$goods_id
        ];
        $res=model("goods")->where($where)->delete();
        if($res){
            successly("删除成功");
        }else{
            fail("删除失败");
        }
    }
    //修改
    public function goodsUpdate(){
      if(checkRequest()){
        $data=input("param.");
        if(empty($data['goods_new'])){
            $data['goods_new']=0;
        }
        if(empty($data['goods_best'])){
            $data['goods_best']=0;
        }
        if(empty($data['goods_hot'])){
            $data['goods_hot']=0;
        }

        //dump($data);exit;
        //验证器
        $validate=validate("Goods");
        $result=$validate->scene('edit')->check($data);
        if(!$result){
            fail($validate->getError());
        }
        $where=[
            'goods_id'=>$data['goods_id']
        ];
        $res=model("goods")->save($data,$where);
        if($res===false){
             fail("修改失败");
        }else{
           successly("修改成功");
        }

      }else{
        $goods_id=input("param.goods_id");
        $where=[
            'goods_id'=>$goods_id
        ];
        $data=model("goods")->where($where)->find();
        $this->goodsAdd();
        $this->assign("data",$data);
        return view();
      }
    }  
    //富文本编辑器
    public function goodsEdit(){
        $file = request()->file('file');
        //dump($arr);die;  
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . "goodseditimg");
        if($info){
            $arr=[
                'code'=>0,
                'font'=>'上传成功',
                'data'=>[
                    "src"=>"http://www.shop.com/public/goodseditimg/".$info->getSaveName(),
                    "title"=>"商品"
                    ]
            ];
            echo json_encode($arr);
        }else{
            // 上传失败获取错误信息
            fail($file->getError());
        }
        //return $info;
    }
}
?>