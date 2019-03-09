<?php 
  namespace app\index\validate;
  use think\Validate; 
  class Address extends Validate{
      protected $rule=[
        'address_province'=>'require|checkProvince',
        'address_city'=>'require|checkCity',
        'address_area'=>'require|checkArea',
          'address_name'=>'require',
          'address_detail'=>'require',
          'address_tel'=>'require|checkTel',
          'send_time'=>'require', 
      ];
      protected $message=[
        
        'address_name.require'=>'收货人姓名必填',
        'address_detail.require'=>'详细地址必填',
        'address_tel.require'=>'手机号必填',
        'send_time.require'=>'最佳送货时间必填',
    ];
    //手机号验证
    public function checkTel($value,$rule,$data){
        $reg='/^1[1-9]\d{9}$/';
        if(!preg_match($reg,$value)){
            return "请输入正确手机号";
        }else{
            return true;
        }
    }
    public function checkProvince($value,$rule,$data){
        if($value==0){
            return "请选择省份";
        }else{
            return true;
        }
    }
    public function checkCity($value,$rule,$data){
        if($value==0){
            return "请选择城市";
        }else{
            return true;
        }
    }
    public function checkArea($value,$rule,$data){
        if($value==0){
            return "请选择地区";
        }else{
            return true;
        }
    }

  }


?>