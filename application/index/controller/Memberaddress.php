<?php
namespace app\index\controller;
use think\Controller;
class Memberaddress extends Common{
    public function address(){
    //防非法登录
    if(!$this->checkUserLogin()){
        $this->error("请先登录",url('login/login'));
    }
    //查询当前用户的所有收货地址
    $where=[
        'user_id'=>session("userInfo.user_id")
    ];
    $address=model("Address");
    $addressInfo=$address->getAddressInfo($where);
     $provinceInfo=$this->getAreaInfo(0);

        $this->assign("province",$provinceInfo);
        $this->assign("address",$addressInfo);
        return view();
   
    }
    //三级联动
    public function getArea(){
        $id=input("param.id");
        $info=$this->getAreaInfo($id);
        echo json_encode($info);
        
    }
    //获取区域信息
    public function getAreaInfo($id){
        $area=model("Area");
        $where=[
            'pid'=>$id
        ];
        $info=$area->where($where)->select();
        return $info;

    }
    //添加
    public function add(){
        $data=input("param.");
        //dump($data);die;
        //$model=model("Address");
        //验证器
        $validate=validate("Address");
        $result=$validate->check($data);
        if(!$result){
            fail($validate->getError());
        }
        
        //判断是否有默认收货地址
        $address=model("Address");
        if($data['is_default']=="true"){
            $data['is_default']=1;
            $where=[
                'user_id'=>session("userInfo.user_id"),
            ];
            $res=$address->where($where)->update(['is_default'=>2]);
        }else{
            $data['is_default']=2;
        }
        $res=$address->save($data);
        if($res){
            successly("添加成功");
        }else{
            fail("添加失败");
        }
    }
    //删除
    public function del(){
        $id=input("param.id",0,'intval');
        if(empty($id)){
            echo '非法操作';exit;
        }
        $address=model("Address");
        $where=[
            'address_id'=>$id
        ];
        $res=$address->where($where)->delete();
        if($res){
            successly("删除成功");
        }else{
            fail("删除失败");
        } 
    }
    //设置默认地址
    public function updateCheck(){
        $id=input("param.id",0,'intval');
        if(empty($id)){
            fail("非法操作");
        }
            $address=model("Address");
            $where=[
                'user_id'=>session("userInfo.user_id"),
            ];
            $res=$address->where($where)->update(['is_default'=>2]);

            $updateWhere=[
                'user_id'=>session("userInfo.user_id"),
                'address_id'=>$id
            ];
            $res2=$address->where($updateWhere)->update(['is_default'=>1]);
            if($res2){
                successly("设置成功");
            }else{
                fail("设置失败");
            }
    }
    //修改
    public function addressUpdate(){
        $address=model("Address");
        if(checkRequest()){
            $address_id=input("param.address_id");
            //dump($address_id);die;
            $data=input("param.");
            //验证器
            $validate=validate("Address");
            $result=$validate->check($data);
            if(!$result){
                fail($validate->getError());
            }

            if($data['is_default']=="true"){
                $data['is_default']=1;
                $checkWhere=[
                    'user_id'=>session("userInfo.user_id"),
                ];
                $res2=$address->where($checkWhere)->update(['is_default'=>2]);
            }else{
                $data['is_default']=2;
            }

            $where=[
                'address_id'=>$address_id
            ];
            $res=$address->update($data,$where);
            if($res){
                successly("修改成功");
            }else{
                fail("修改失败");
            }
        }else{
           
            $id=input("param.id");
            $where=[
            'address_id'=>$id
        ];
        $addressInfo=$address->where($where)->find();
        //dump($addressInfo);die;
        $province=$this->getAreaInfo(0);
        $city=$this->getAreaInfo($addressInfo['address_province']);
        $area=$this->getAreaInfo($addressInfo['address_city']);

        $this->assign("province",$province);
        $this->assign("city",$city);
        $this->assign("area",$area);
        $this->assign("address",$addressInfo);
        return view(); 
        }
        
    }
}

   