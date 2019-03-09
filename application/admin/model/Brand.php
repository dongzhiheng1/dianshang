<?php 
    namespace app\admin\model;
    use think\Model;
    class Brand extends Model{
        protected $updateTime = false;
        protected $table='shop_brand';
        public function brandInfo($page,$limit){
            $data=$this->page($page,$limit)->select();
            foreach($data as $k=>$v){
                $data[$k]['brand_show']=$v['brand_show']==1?"是":"否";
            }
            return $data;
        }
        
    }

?>