<?php 
    namespace app\admin\controller;
    use think\Controller;
        class Search extends  Common{
        public function search(){
                 $search=model("search");
                 
            if(checkRequest()){
                $value=input("param.value");
                //dump($value);die;
                $where=[];
                if(!empty($value)){
                     $where=[
                    'value'=>['like',"%$value%"]
                    ];
                }
                //$count=$search->count();
                //dump($count);
                $arr=$search->distinct(true)->field("value")->where($where)->order("id","desc")->limit(10)->select();
                //session("arr",$arr);
                //echo $search->getLastSql();die;
               $info=[
                   'arr'=>$arr
               ];
               echo json_encode($info);
            }else{ 
                return view();
            }  
        }
        public function add(){
            $search=model("search"); 
            $value=input("param.value");
            //dump($value);die;
            $cateinfo=[
                'value'=>$value
            ];
            if(!empty($value)){
               $res=$search->save($cateinfo);
               if($res){
                $this->redirect("search/search");
            }
        }
    }

    
}

?>