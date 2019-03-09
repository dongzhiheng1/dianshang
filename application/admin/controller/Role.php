<?php
namespace app\admin\controller;
use think\Controller;
class Role extends Common{
    //角色添加
    public function roleAdd(){
        if(checkRequest()){
            $data=input("param.");
            if(empty($data['role_name'])){
                fail("角色名称不能为空");
            }
            if(empty($data['node_id'])){
                fail("节点必选");
            }
            $role=model("Role");
            $role->startTrans();
            try{
                //角色表
                $res=$role->allowField(true)->save($data);
                if(empty($res)){
                    $role->rollback();
                    throw new \Exception("角色添加失败");
                }
                //dump($res);die;
                $role_id=$role->role_id;
                //角色 节点派生表
                $power=model("Power");
                foreach($data['node_id'] as $k=>$v){
                    $powerData[]=['role_id'=>$role_id,'node_id'=>$v];
                }
                $res1=$power->saveAll($powerData);
                if(empty($res1)){
                    $role->rollback();
                    throw new \Exception("角色权限添加失败");
                }
                $role->commit();
                successly("添加成功");
            }catch(\Exception $e){
                fail($e->getMessage());
            }    
        }else{
        $node=model("Node");
        $data=$this->getRoleInfo();
        $this->assign("data",$data);
        return view();
        }
    }
    //角色展示
    public function roleShow(){
        $role=model("Role");
        $roleInfo=$role->select();
        //dump($roleInfo);die;
        $this->assign("roleInfo", $roleInfo);

        return view();
    }
    // 角色查询
    public function getRoleInfo(){
        $node=model("Node");
        $data=input("param.");
        $where=[
        'pid'=>0
        ];
        $data=$node->where($where)->select();
        foreach($data as $k=>$v){
        $info=$node->where(['pid'=>$v['node_id']])->select();
        $v['son']=$info;
        }
        return $data;
    }
    //角色修改
    public function roleUpdate(){
        if(checkRequest()){
            $data=input("param.");
            if(empty($data['role_name'])){
                fail("角色名称不能为空");
            }
            if(empty($data['node_id'])){
                fail("节点必选");
            }
            $where=[
                'role_id'=>$data['role_id']
            ];
            $role=model("Role");
            $role->startTrans();
            try{
                //角色修改
                $res=$role->allowField(true)->save($data,$where);
                if($res===false){
                    $role->rollback();
                    throw new \Exception("角色修改失败");
                }
                //删除节点
                $power=model("Power");
                $res1=$power->where($where)->delete();
                if($res1===false){
                    $role->rollback();
                    throw new \Exception("角色修改失败");
                }
                foreach($data['node_id'] as $k=>$v){
                    $powerData[]=['role_id'=>$data['role_id'],'node_id'=>$v];
                }
                $res2=$power->saveAll($powerData);
                if($res2===false){
                    $role->rollback();
                    throw new \Exception("角色修改失败");
                }
                $role->commit();
                successly("修改成功");

            }catch(\Exception $e){
                fail($e->getMessage());
            }
           


        }else{
            //角色表
            $role_id=input("role_id",0,"intval");
            if(empty($role_id)){
                fail("请选择角色");
            }
            $where=[
                'role_id'=>$role_id
            ];
            $role=model("Role");
            $roleInfo=$role->where($where)->find();
            //派生表查询
            $power=model("Power");
            $nodeInfo=$power->where($where)->column("node_id");
            //节点表
            $node=model("Node");
            $data=$this->getRoleInfo();
            $this->assign("data",$data);
            $this->assign("roleInfo",$roleInfo);
            $this->assign("nodeInfo",$nodeInfo);
            return view();
        }
        
    }
    //角色删除
    public function roleDel(){
        $role_id=input("param.role_id");
        $where=[
            'role_id'=>$role_id
        ];
        $role=model("Role");
        $role->startTrans();
        try{
            $adminrole=model('Adminrole');
            $count=$adminrole->where($where)->count();
            if($count>0){
                $role->rollback();
                throw new \Exception("该角色下有管理员不能删除");
            }
            //删除角色表
            $res=$role->where($where)->delete();
            if($res===false){
                $role->rollback();
                throw new \Exception("删除失败");
            }
            //删除派生表
            $power=model("Power");
                $res1=$power->where($where)->delete();
                if($res1===false){
                    $role->rollback();
                    throw new \Exception("删除失败");
                }
                $role->commit();
                successly("删除成功");
        }catch(\Exception $e){
            fail($e->getMessage());
        }
    }
    
}
?>