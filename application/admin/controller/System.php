<?php 
    namespace app\admin\controller;
    use think\Controller;
    class System extends Common{
       /*  public function aa(){
            dump(session("configInfo"));die;
        } */
       //系统设置
        public function systemadd(){
            $model=model("config");
            if(checkRequest()){
                $data=input("post.");
                //dump($data);
                foreach($data as $k=>$v){
                    $info[]=[
                        "name"=>$k,
                        "value"=>$v
                    ];   
                }  
                $model->query("delete from shop_config");
                $res=$model->allowField(true)->saveAll($info);
                if($res){
                    session("configInfo",null);
                    successly("保存成功");
                }else{
                    fail("保存失败");
                }
            }else{
                $data=$model->select();
                if(!empty($data)){
                    foreach($data as $k=>$v){
                   $info[$v['name']]=$v['value'];
                }
                $this->assign("info",$info);
                }
                return view();
            }
           
        }
    }

?>