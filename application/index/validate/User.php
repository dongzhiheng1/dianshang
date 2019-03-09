<?php 
  namespace app\index\validate;
  use think\Validate; 
  class User extends Validate{
      protected $rule=[
          'user_tel'=>'require|checkTel|unique:user',
          'user_email'=>'require|checkEmail|unique:user',
          'user_code'=>'require|checkCode',
          'user_pwd'=>'require|checkPwd', 
          'user_pwd1'=>'require|confirm:user_pwd', 
      ];
      protected $message=[
        'user_tel.require'=>'手机号必填',
        'user_tel.unique'=>'手机号已经被注册,请登录',
        'user_email.unique'=>'邮箱已经被注册,请登录',
        'user_email.require'=>'邮箱必填',
        'user_email.email'=>'请输出正确的邮箱格式',
        'user_code.require'=>'验证码必填',
        'user_pwd.require'=>'密码必填',
        'user_pwd1'=>'确认密码必填',
        'user_pwd1.confirm'=>'确认密码必须和密码保持一致'
    ];
    protected $scene=[
       'registerTel'=>['user_tel','user_code','user_pwd','user_pwd1'],
       'registerEmail'=>['user_email','user_code','user_pwd','user_pwd1']
    ];

    //自定义邮箱验证
    public function checkEmail($value,$rule,$data){
        $email=session("Info.email");
        if($value!=$email){
            return "当前邮箱与发送验证码邮箱不一致";
        }else{
            return true;
        }
    }
    //密码验证
    public function checkPwd($value,$rule,$data){
        $reg='/^.{6,16}$/';
        if(!preg_match($reg,$value)){
            return "密码必须为6-16位";
        }else{
            return true;
        }
    }
    //邮箱验证码验证
    public function checkCode($value,$rule,$data){
        $code=session("Info.code");
        $time=session("Info.time");
        if(empty($data['user_tel'])){
            $code=session("Info.code");
            $time=session("Info.time");
        }else{
            $code=session("Info.code");
            $time=session("Info.time");
        }
        if($value!=$code){
            return "验证码有误";
        }else if(time()-$time>300){
            return "验证码已过期,五分钟内输入有效";
        }else{
            return true;
        }
    }


    //自定义手机号验证
    public function checkTel($value,$rule,$data){
        $tel=session("Info.tel");
        if($value!=$tel){
            return "当前手机号与发送验证码手机号不一致";
        }else{
            return true;
        }
    }
   /*  //手机号验证
    public function checkCode($value,$rule,$data){
        $code=session("Info.code");
        $time=session("Info.time");
        if($value!=$code){
            return "验证码有误";
        }else if(time()-$time>300){
            return "验证码已过期,五分钟内输入有效";
        }else{
            return true;
        }
    } */
   

  }


?>