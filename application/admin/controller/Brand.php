<?php
namespace app\admin\controller;
//use think\Controller;
use app\admin\model\Brand as B;
class Brand extends  Common{
    public function checkName(){
        $obj=new B();
        //$brand_id=input("param.brand_id");
        //$type=input("param.type");
        $brand_name=input("param.brand_name");
        $where=[    
            'brand_name'=>$brand_name
        ];
        $res=$obj->where($where)->find();
        if($res){
            return  0;
        }else{
            return  1;
        }
    }
     /**品牌添加 */
    public function brandAdd(){
        $obj=new B();
       if(checkRequest()){
           $data=input("param.");
           //dump($data);exit;
        //验证器
        $validate=validate("Brand");
        $result=$validate->scene("add")->check($data);
        if(!$result){
            fail($validate->getError());
        }
        $res=$obj->allowField(true)->save($data);
        if($res){
            successly("添加成功");
        }else{
            fail("添加失败");
        }
        
       }else{
        return view();
        }
     }
    public function brandShow(){
        return view();
        }
    //上传图片
    public function brandLogo(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        //dump($arr);die;  
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'brandLogo');
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
    } 
    //分页
    public function brandList(){
        $obj=new B();
        $page=input("get.page");
        $limit=input("get.limit");
        $count=$obj->count();
        $data=$obj->brandInfo($page,$limit);
        $info=[
            'code'=>0,
            'msg'=>'',
            'count'=>$count,
            'data'=>$data
        ];
        echo json_encode($info);
    }
    //删除
    public function brandDel(){
        $obj=new B();
        $brand_id=input("post.brand_id");
        //品牌下是否有商品
        $where=[
            'brand_id'=>$brand_id
        ];
       $goods_count=model("goods")->where($where)->count();
       if($goods_count>0){
           fail("此品牌下有商品无法删除");
       }
        $res=$obj->where($where)->delete();
        if($res){
            successly("删除成功");
        }else{
            fail("删除失败");
        }
    }
    //即点即改
    public function brandFileUpdate(){
        $obj=new B();
        $value=input("post.value");
        $field=input("post.field");
        $brand_id=input("post.brand_id");
        if($field=='brand_name'){
            $where=[
            'brand_id'=>['neq',$brand_id],
            'brand_name'=>$value
            ];
        $arr=$obj->where($where)->find();
        if($arr){
            fail("账号已存在");
        } 
        }
        if(empty($value)){
            fail("必填项不能为空");
        }
        
        //修改
        $updateWhere=[
            'brand_id'=>$brand_id
        ];
        $data=[
            $field=>$value
        ];
        $res=$obj->save($data,$updateWhere);
        if($res){
            successly("修改成功");
        }else{
            fail("修改失败");
        }
    }
    //编辑
    public function brandUpdate(){
    $obj=new B();
    if(checkRequest()){
        $data=input("post.");
        //验证器
        $validate=validate('Brand');
        $result=$validate->scene('edit')->check($data);
        if(!$result){
           fail($validate->getError());
        }
        $where=[
            'brand_id'=>$data['brand_id']
        ];
        $res=$obj->allowField(true)->save($data,$where);
        if($res===false){
            fail("修改失败");
        }else{
            successly("修改成功");
        }

    }else{
        $brand_id=input("get.brand_id");
        $where=[
            'brand_id'=>$brand_id   
        ];
        $data=$obj->where($where)->find();
        $this->assign('data',$data);
        return view();
        }  
    }

}
?>