<?php 
    namespace app\admin\controller;
    use think\Controller;
    class Login extends Controller{
        public function login(){
            if(checkRequest()){
                $admin_name=input("post.admin_name");
                $admin_pwd=input("post.admin_pwd");
                $mycode=input("post.mycode");
                if(empty($admin_name)||empty($admin_pwd)||empty($mycode)){
                    fail("必填项不能为空");
                }
                if(!captcha_check($mycode)){
                        fail("验证失败");
                };

                //检测账号
                $model=model("Admin");
                $where=[
                    'admin_name'=>$admin_name
                ];
                $arr=$model->where($where)->find();
                if(!empty($arr)){
                    $salt=$arr['salt'];
                    $pwd=createPwd($admin_pwd,$salt);
                    if($pwd==$arr['admin_pwd']){
                     session("adminInfo",['admin_id'=>$arr['admin_id'],"admin_name"=>$admin_name]);
                     $updateWhere=[
                        'admin_id'=>$arr['admin_id']
                     ];
                     $updateInfo=[
                         "last_login_time"=>time(),
                         "last_login_ip"=>request()->ip()
                     ];
                    $res = $model->save($updateInfo,$updateWhere);
                    if ($res){
                        successly("登录成功");
                    }else{
                        fail('登录失败');
                    }

                }else{
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
        public function quit(){
            session("adminInfo",null);
            $this->redirect("Login/login");
        }
    }

?>