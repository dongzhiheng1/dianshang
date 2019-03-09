<?php
namespace app\index\controller;
use think\Controller;
class Register extends Controller{
    public function register(){
        if(checkRequest()){ 
            $type=input("param.type");
            $data=input("param.");
            //dump($data);
            $validate=validate("User");
            if($type==1){
                $res=$validate->scene("registerEmail")->check($data);
                $account=$data['user_email'];
            }else{
                $res=$validate->scene("registerTel")->check($data);
                $account=$data['user_tel'];
            }
          
            if($res){
                $user=model('User');
                $res=$user->allowField(true)->save($data);

                if($res){
                    $userInfo=[
                    'user_id'=>$user->user_id,
                    'account'=>$account
                ];
                session("userInfo",$userInfo);
                successly("注册成功");
                }else{
                    fail("注册失败");
                }
                
            }else{
               fail($validate->getError());
                //fail("注册失败");
            }
            
        }else{
            $this->view->engine->layout(false);
            return view();
        }
        
    }
    //发送邮件
    public function sendEmail(){
        $user=model('User');
        $email=input("param.email");
        if(!empty($email)){
            $where=[
                'user_email'=>$email
            ];
            $res=$user->where($where)->find();
            if($res){
                fail("邮箱已存在");
            }else{
                 //echo $email;die;
            $code=createCode();
            //echo $code;die;
            $res=sendEmail($email,$code);
            if($res){
                $emailInfo=[
                    'email'=>$email,
                    'code'=>$code,
                    'time'=>time()
                ];
                session("Info",$emailInfo);
            }
            if($res){
                successly("发送成功");
            }else{
                fail("发送失败");
            }

        }
    }
       
    }



     //发送电话验证码
     public function sendTel(){
        $user=model('User');
        $tel=input("param.tel");
        if(!empty($tel)){
            $where=[
                'user_tel'=>$tel
            ];
            $res=$user->where($where)->find();
            if($res){
                fail("手机号已存在");
            }else{
                 //echo $email;die;
            $code=createCode();
            //echo $code;die;
            $res=sendSms($tel,$code);
            if($res){
                $telInfo=[
                    'tel'=>$tel,
                    'code'=>$code,
                    'time'=>time()
                ];
                session("Info",$telInfo);
            }
            if($res){
                successly("发送成功");
            }else{
                fail("发送失败");
            }
            }
        }
       
    }



}