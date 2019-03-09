<?php 
namespace app\admin\validate;
    use \think\Validate;
    class Node extends Validate{
        protected $rule =[
            'node_name'  => 'require|checkName',
            'controller_name'  => 'require',
        ];
        protected $message  =   [
            'node_name.require' => '节点名称必填',
            'controller_name.require' => '控制器名称必填',
              ];

//验证节点唯一性
public function checkName($value,$rule,$data){
    if(empty($data['node_id'])){
        $where=[
            'node_name'=>$value
        ];
    }else{
        $where=[
            'node_id'=>['neq',$data['node_id']],
            'node_name'=>$value
        ];
    }
    $model=model('Node');
    $arr=$model->where($where)->find();
    if(!empty($arr)){
      fail('节点名已存在');
    }else{
         return true;
    }
}
    }
?>