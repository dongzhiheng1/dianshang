<?php
    namespace app\admin\model;
    use think\Model;
    class Admin extends Model{
        protected $updateTime = false;
        protected $salt;
        protected $insert=['salt'];
        public function setAdminPwdAttr($value){
           $this->salt=$salt=createSalt();
           return createPwd($value,$salt);
       }
       public function setSaltAttr(){
           return  $this->salt;
       }
    }

?>