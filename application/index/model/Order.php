<?php 
    namespace app\index\model;
    use think\Model;
    class Order extends Model{
        protected $updateTime=false;
        protected $createTime="ctime";
    }
?>