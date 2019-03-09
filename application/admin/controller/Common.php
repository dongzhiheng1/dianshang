<?php 
    namespace app\admin\Controller;
    use think\Controller;
    class Common extends Controller{
        public function __construct(){ 
            parent::__construct();
            if(!session('?adminInfo')){
                $this->error("请先登录");
            }
            $nodeInfo=$this->getNodeMessage();
            //dump($nodeInfo);die;
            $Info=getRecursionNode($nodeInfo);
            $this->assign('Info',$Info);
            //查询管理员拥有的权限
           foreach($nodeInfo as $k=>$v){
               $node_id[]=$v['node_id'];
           }
           $node_model=model("Node");
           $nodeWhere=[
               'controller_name'=>request()->controller(),
               'action_name'=>request()->action(),
           ];
           $nodeId=$node_model->where($nodeWhere)->value('node_id');
           if(!in_array($nodeId,$node_id)){
               $this->error("权限不够",url('Index/index'));die;
           }

        }
        //管理员的节点信息
        public function getNodeMessage(){
            $admin_id=session('adminInfo.admin_id');
            $where=[
                'admin_id'=>$admin_id,
            ];
            $node_model=model('Node');
            $nodeInfo= $node_model
                ->alias('n')
                ->join('shop_power p','n.node_id=p.node_id')
                ->join('shop_role r','p.role_id=r.role_id')
                ->join('shop_adminrole a_r','r.role_id=a_r.role_id')
                ->where($where)
                ->select();
                //dump($nodeInfo);die;
            return $nodeInfo;
        }
    }

?>