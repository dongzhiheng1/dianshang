<?php 
    namespace app\index\model;
    use think\Model;
    class User extends Model{
        protected $table='shop_user';
        protected $updateTime=false;
        //密码修改器
        public function setUserPwdAttr($value){
            return md5($value);
        }
    }
?>