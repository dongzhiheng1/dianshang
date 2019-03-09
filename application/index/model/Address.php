<?php 
    namespace app\index\model;
    header("content-type:text/html;charset=utf-8");
    use think\Model;
    class Address extends Model{
        protected $updateTime=false;
        protected $insert = ['user_id'];
        protected $table="shop_address";
        protected function setUserIdAttr(){
            return session("userInfo.user_id");
        }
        public function getAddressInfo($where){
            $addressInfo=$this->where($where)->select();
            $area=model('Area');
            foreach($addressInfo as $k=>$v){
                /* $addressInfo[$k]['address_province']=$area->where(['id'=>$v['address_province']])->value("name");
                $addressInfo[$k]['address_city']=$area->where(['id'=>$v['address_city']])->value("name");
                $addressInfo[$k]['address_area']=$area->where(['id'=>$v['address_area']])->value("name"); */
                $areaWhere=[
                    'id'=>['in',[$v['address_province'],$v['address_city'],$v['address_area']]]
                ];
                $field=$area->where($areaWhere)->column("name");
                //echo $area->getLastSql();die;
                $addressInfo[$k]['address']=implode("",$field);
            };
           //print_r($addressInfo);die;
           return $addressInfo;
        }
  

    }
?>