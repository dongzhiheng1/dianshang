<?php
namespace app\admin\controller;
//use think\Controller;
//header("content-type:text/html;charset=utf-8");
class Node extends  Common{
    //节点添加
    public function nodeAdd(){
        $node_model=model('Node');
        if(checkRequest()){
            $data=input("param.");
            $validate=validate("Node");
            $result=$validate->check($data);
            if(!$result){
                fail($validate->getError());
            }
            $res=$node_model->save($data);
            if($res){
                successly("添加成功");
            }else{
                fail("添加失败");
            }
        }else{
            $this->getNodeInfo();
            return view();
        }
    }
    //节点展示
    public function nodeShow(){
        $node_model=model('Node');
        $where=[
            'pid'=>0
        ];
        $nodeInfo=$node_model->where($where)->select();
        foreach($nodeInfo as $k=>$v){
            $data[]=$v;
            $info=$node_model->where(['pid'=>$v['node_id']])->select();
            foreach($info as $key=>$val){
                $data[]=$val;
            }
        }
        $this->assign("data",$data);
        return view();
    }
    //即点即改
    public function nodeFieldUpdate(){
        $value=input("param.value");
        $field=input("param.field");
        $node_id=input("param.node_id");
        if($field=="node_name"){
            $upWhere=[
                'node_id'=>['neq',$node_id],
                'node_name'=>$value
            ];
            $arr=model("Node")->where($upWhere)->find();
            if($arr){
                fail("分类已存在");
            }
        }if(empty($value)){
            fail("必填项不能为空");
        }
        $where=[
            'node_id'=>$node_id
        ];
        $data=[
            $field=>$value
        ];
        $res=model("Node")->save($data,$where);
        if($res===false){
            fail("修改失败");
        }else{
            successly("修改成功");
        }

    }
    //删除
    public function nodeDel(){
        $node_id=input("param.node_id");
        //此分类下是否有子类
        $nodeWhere=[
            'pid'=>$node_id
        ];
        $count=model('Node')->where($nodeWhere)->count();
        if($count>0){
            fail("此分类下有节点");
        }
        //查找分类下是否有商品
        $where=[
            "node_id"=>$node_id
        ];
        //删除
        $res=model("Node")->where($where)->delete();
        if($res){
            successly("删除成功");
        }else{
            fail("删除失败");
        }
    }
    //修改
    public function nodeUpdate(){
        $node_model=model('Node');
        if(checkRequest()){
            $data=input('param.');
            $validate=validate("Node");
            $result=$validate->check($data);
            if(!$result){
                fail($validate->getError());
            }
            $node_id=input('param.node_id');
            $updateWhere=[
                'node_id'=>$node_id
            ];
            $res=$node_model->save($data,$updateWhere);
            //echo $node_model->getLastSql();die;
            //dump($res);die;
            if($res===false){
                fail('修改失败');
            }else{
                successly('修改成功');
            }
        }else{
            $this->getNodeInfo();
            $node_id=input('param.node_id');
            $where=[
                'node_id'=>$node_id
            ];
            $Info= $node_model->where($where)->find();
            $this->assign('Info',$Info);
            return view();
        }
    }
    //查询父节点为0数据
    public function getNodeInfo(){
        $node_model=model('Node');
        $where=[
            'pid'=>0
        ];
        $nodeInfo=$node_model->where($where)->select();
        $this->assign('nodeInfo',$nodeInfo);
    }
    //验证唯一性
    public function checkName(){
        $node_name=input("param.node_name");
        $where=[
            'node_name'=>$node_name
        ];
        $res=model("Node")->where($where)->find();
        if($res){
            return 0;
        }else{
            return 1;
        }
    }
}
?>