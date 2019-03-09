<?php 
    namespace app\index\model;
    use think\Model;
    class History extends Model{
        protected $createTime="ctime";
        protected $updateTime=false;
    }
?>