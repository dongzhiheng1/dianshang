<?php
namespace app\index\controller;
use think\Controller;
class Login extends Common{
    public function login(){
        if(checkRequest()){
            $account=input("param.account");
            $user_pwd=input("param.user_pwd");
            $remember_me=input("param.remember_me");
            $user=model("user");
            /*  dump($account);
            dump($user_pwd);
            dump($remember_me); */
            if(empty($account)){
                fail("账号必填");
            }
            if(empty($user_pwd)){
                fail("密码必填");
            }

            //处理条件
            if(substr_count($account,"@")){
                $where=[
                    'user_email'=>$account
                ];
            }else{
                $where=[
                    'user_tel'=>$account
                ];
            }

            //根据账号查询数据
            $info=$user->where($where)->find();
            //查询到账号
            $time=time();
            $last_error_time=$info['last_error_time'];
            $error_num=$info['error_num'];
            $update_where=[
                'user_id'=>$info['user_id']
            ];
            //dump($info);exit;
            //echo $user->getLastSql();
            if(!empty($info)){
                if(md5($user_pwd)==$info['user_pwd']){
                    if($error_num>=5&&$time-$last_error_time<3600){
                        $open_time=60-(ceil(($time-$last_error_time)/60));
                        fail("账号已锁定".$open_time."分钟之后可以登录");}
                        //次数清零 时间清空
                        $updateInfo=[
                            'error_num'=>0,
                            'last_error_time'=>null
                        ];
                        $res=$user->where($where)->update($updateInfo);
                        //判断是否记住密码 账号密码储存cookie10天
                        if($remember_me=='true'){
                            $cookieInfo=[
                                'account'=>$account,
                                'user_pwd'=>$user_pwd
                            ];
                            $day=60*60*24*10;
                            cookie("account",$account,$day);
                            cookie("user_pwd",$user_pwd,$day);
                        }

                             //用户信息储存session
                            $sessionInfo=[
                                'user_id'=>$info['user_id'],
                                'account'=>$account,
                                'time'=>time()
                            ];
                            //print_r($sessionInfo);die;

                            session("userInfo",$sessionInfo);
                            
                            //同步浏览记录到数据库
                            $this->asyncHistory();
                            $this->asyncCookie();
                            successly("登录成功");
                   
                }else{
                     //距离上次错误时间超过一小时 次数改为1 时间为当前时间
                     if($time-$last_error_time>3600){
                        $updateInfo=[
                            'error_num'=>1,
                            'last_error_time'=>$time
                        ];
                        $res=$user->where($where)->update($updateInfo);
                        fail("您还可以输入4次");
                    }else{
                        //如果错误次数>=5次提示已锁定
                        
                            //错误次数>=5 并且 时间在一小时内 不允许登录
                        if($error_num>=5&&$time-$last_error_time<3600){
                            //五次以后
                            $open_time=60-(ceil(($time-$last_error_time)/60));
                            fail("账号已锁定".$open_time."分钟之后可以登录");

                        }else{
                            //次数累计
                            $error_num++;
                            $updateInfo=[
                                'error_num'=>$error_num,
                                'last_error_time'=>$time
                            ];
                            $res=$user->where($where)->update($updateInfo);
                            $num=5-$error_num;
                            if($num==0){
                                fail("账号已锁定,请1小时后登录");
                            }
                            fail("您还可以输入".$num.'次');
                        }
                    }
                    fail("账号或密码错误");
                }

            }else{
                    fail("账号或密码错误");
                }

        }else{
             $this->view->engine->layout(false);
            return view();
        }
       
    }
    //退出
    public function quit(){
        session("userInfo",null);
        cookie("account",null);
        cookie("user_pwd",null);
        $this->redirect(url("Index/index"));
    }
}