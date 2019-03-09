<?php
namespace app\admin\controller;
//use think\Controller;
use  app\admin\model\Friendurl as F;
class Friendurl extends  Common{
    //添加
    public function friendurlAdd(){
        $obj=new F();
        if(checkRequest()){
            $data=input("param.");
            //验证器
            $validate=validate('Friendurl');
            $result=$validate->scene("add")->check($data);
            if(!$result){
                fail($validate->getError());
            }
            //添加
            $res=$obj->save($data);
            if($res){
                successly("添加成功");
            }else{
                fail("添加失败");
            }

        }else{
            return view();
        }
    }
    //验证唯一
    public function checkName(){
        $obj=new F();
        $friend_name=input("param.friend_name");
        $where=[
            'friend_name'=>$friend_name
        ];
        $res=$obj->where($where)->find();
        if($res){
            return 0;
        }else{
            return 1;
        }
    }
    //分页
    public function friendurlList(){
        $obj=new F;
        $page=input("param.page");
        $limit=input("param.limit");
        $count=$obj->count();
        $data=$obj->page($page,$limit)->select();
        $info=[
            'code'=>0,
            'msg'=>'',
            'count'=>$count,
            'data'=>$data
        ];
       echo json_encode($info);
    }
    //展示
    public function friendurlShow(){
        return view();
    }
    //删除
    public function friendurlDel(){
        $obj=new F();
        $friend_id=input("param.friend_id");
        $where=[
            'friend_id'=>$friend_id
        ];
        $res=$obj->where($where)->delete();
        if($res){
            successly("删除成功");
        }else{
            fail("删除失败");
        }


    }
    //即点即改
    public function friendurlFileUpdate(){
        $obj=new F();
        $value=input("param.value");
         //dump($value);exit;
        $field=input("param.field");
        //dump($field);exit;
        $friend_id=input("param.friend_id");
        //dump($friend_id);exit;
        if($field=="friend_name"){
            $where=[
                "friend_id"=>['neq',$friend_id],
                "friend_name"=>$value
            ];
            $arr=$obj->where($where)->find();
            if($arr){
                fail("账号已存在");
            }
        }if(empty($value)){
                fail("必填项不能为空");
            }
       //修改
       $updateWhere=[
           'friend_id'=>$friend_id
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
    //修改
    public function friendurlUpdate(){
        $obj=new F();
        if(checkRequest()){
            $data=input("param.");
            //修改
        $validate=validate('Friendurl');
        $result=$validate->scene('edit')->check($data);
        if(!$result){
       fail($validate->getError());
        }
        $where=[
        'friend_id'=>$data['friend_id']
        ];
        $res=$obj->allowField(true)->save($data,$where);
        if($res===false){
        fail("修改失败");
        }else{
        successly("修改成功");
        }

    }else{
    $friend_id=input("get.friend_id");
    $where=[
        'friend_id'=>$friend_id   
    ];
    $data=$obj->where($where)->find();
    $this->assign('data',$data);
    return view();
    }  
}
    
}
?>