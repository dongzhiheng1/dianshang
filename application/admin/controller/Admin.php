<?php
namespace app\admin\controller;
//use think\Controller;
use app\admin\model\Admin as A;
class Admin extends Common{
    //管理员添加
    public function adminAdd(){
        $obj=new A();
        if(checkRequest()){
            $data=input('post.');
            //dump($data);
            //验证器
            $validate=validate('Admin');
            //dump($validate);
            $result=$validate->scene('add')->check($data);
            if(!$result){
                fail($validate->getError());
            }
            //保存到数据库
            $res=$obj->save($data);
            if($res){
                successly("添加成功");
            }else{
                fail("添加失败");
            }
            //盐值 密码
            //生成盐值 str_shuffle substr rand
            //修改器(model层)
            //时间戳(model层)
        }else{
            return view();
        }
    }
    //验证唯一性
    public function checkName(){
        $obj=new A();
        $admin_name=input("param.admin_name");
        $type=input('post.type');
        $admin_id=input("param.admin_id");
        //dump($admin_id);die;
        if($type==1){
            $where=[
            'admin_name'=>$admin_name 
        ];
        }else{
            $where=[
                'admin_id'=>['neq',$admin_id],
               ' admin_name'=>$admin_name
            ];
        }
        //dump($admin_name);
         $res=$obj->where($where)->find();
         //echo $obj->getLastSql();
        if($res){
          return 0;
        }else{
          return 1;
         }  
     
     
    }
    //管理员展示
    public function adminShow(){
        return view();
    }
    public function adminList(){
        $obj=new A();
        $page=input("get.page");
        $limit=input("get.limit");
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
    //即点即改
    public  function adminFileUpdate(){
        $obj=new A();
        $value=input("post.value");
        $field=input("post.field");
        $admin_id=input("post.admin_id");
        if($field=='admin_name'){
             $where=[
             'admin_id'=>['neq',$admin_id],
             'admin_name'=>$value
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
                'admin_id'=>$admin_id
            ];
            $data=[
                $field=>$value

            ];
        $res=$obj->save($data,$updateWhere);
            //echo $obj->getLastSql();die;
            if($res){
                successly("修改成功");
            }else{
                fail("修改失败");
            }
       
    }
    //删除
    public function adminDel(){
        $obj=new A();
        $admin_id=input('post.admin_id');
        $where=[
            'admin_id'=>$admin_id
        ];
        $res=$obj->where($where)->delete();
        //echo $obj->getLastSql();exit;
        if($res){
            successly("删除成功");
        }else{
            fail("删除失败");
        }
    }
    //修改
    public function adminUpdate(){
        $obj=new A();
        if(checkRequest()){
            $data=input("post.");
            //dump($data);die;
            $validate=validate('Admin');
            //dump($validate);
            $result=$validate->scene('edit')->check($data);
            if(!$result){
                fail($validate->getError());
            }
        $where=[
            'admin_id'=>$data['admin_id']
        ];
        $res=$obj->save($data,$where);
        if($res===false){
            fail('修改失败');
        }else{
            successly("修改成功");
        }
        }else{
        $admin_id=input('get.admin_id');
        $where=[
            'admin_id'=>$admin_id
        ];
        $data=$obj->where($where)->find();
        $this->assign('data',$data);
        return view();
        }
       
    }
    //修改密码
    public function updatePwd(){
        if(checkRequest()){
                $old_pwd=input("post.old_pwd");
                $new_pwd1=input("post.new_pwd1");
                $new_pwd2=input("post.new_pwd2");
                $admin_id=session("adminInfo.admin_id");
                 $where=[
                        "admin_id"=>$admin_id
                    ];
                if(empty($old_pwd)){
                    fail("原密码不能为空");
                }else{  
                    //echo $admin_id;exit;
                   
                    $info=model("Admin")->where($where)->find();
                    $admin_pwd=createPwd($old_pwd,$info['salt']);
                    if($admin_pwd!=$info['admin_pwd']){
                        fail("原密码错误");
                    }
                    
                    if(empty($new_pwd1)){
                        fail("新密码不能为空");
                    }else if(strlen($new_pwd1)<5){
                        fail("新密码长度应为5位或5位以上");
                    }
                    if($new_pwd2!=$new_pwd1){
                        fail("确认密码必须与新密码保持一致");
                    }


                    //修改
                    $arr=[
                        "admin_pwd"=>createPwd($new_pwd1,$info['salt'])
                    ];
                    $res=model("admin")->where($where)->update($arr);
                    if($res===false){
                        fail("修改失败");
                    }else{
                        successly("修改成功");
                    }


                }

        }else{

            return view();
        }
    }
    //权限管理
    public function giveRole(){
        if(checkRequest()){
            $data=input("param.");
            //根据管理员id删除派生表数据
            $adminrole=model("Adminrole");
            $adminrole->startTrans();
            try{
                $where=[
                'admin_id'=>$data['admin_id']
                ];
                $res=$adminrole->where($where)->delete();
                if($res===false){
                    $adminrole->rollback();
                    throw new \Exception("提交失败");
                }
                 //dump($data);die;
                if(empty($data['role_id'])){
                    $adminrole->rollback();
                    throw new \Exception("角色必选");
                }
                $dataInfo=[];
                foreach($data['role_id'] as $k=>$v){
                    $dataInfo[]=['admin_id'=>$data['admin_id'],'role_id'=>$v];
                }
                $res1=$adminrole->saveAll($dataInfo);
                if($res1===false){
                    fail("提交失败");
                }
                $adminrole->commit();
                successly("提交成功");

            }catch(\Exception $e){
                fail($e->getMessage());
            }
             
        }else{
            $admin_id=input("param.admin_id");
            //查询所有角色
            $role=model("role");
            $roleInfo=$role->select();
            //查询当前管理员所选角色id
            $adminrole=model("Adminrole");
            $where=[
                'admin_id'=>$admin_id
            ];
            $selectRoleId=$adminrole->where($where)->column("role_id");
            $this->assign("selectRoleId",$selectRoleId);
            $this->assign("roleInfo",$roleInfo);
            $this->assign("admin_id",$admin_id);
            return view();
        }
        
    }

}
?>