<?php 
    namespace app\admin\validate;
    use \think\Validate;
    class Admin extends Validate{
        protected $rule =   [
            'admin_name'  => 'require|checkName',
            'admin_pwd'=>'require|checkPwd',
            'admin_email'=>'require|email',
            'admin_tel'=>'require|checkTel',

        ];
        protected $message  =   [
            'admin_name.require' => '账号必填',
            'admin_pwd.require' => '密码必填',
            'admin_email.require' => '邮箱必填',
            'admin_email.email' => '邮箱格式错误',
            'admin_tel.require'=>'手机号不能为空',
              ];
        protected $scene = [
            'edit'  => ['admin_name','admin_tel','admin_email'],
            'add'=>['admin_name','admin_tel','admin_email','admin_pwd'],
            ];
            
                
           //账号验证 
           public function checkName($value,$rule,$data){
            $reg='/[a-z_]\w{3,11}/i';
            if(!preg_match($reg,$value)){
                return "账号由数字，字母，下划线组成，非数字开头，并且为4-12位";
            }else{
                if(empty($data['admin_id'])){
                    $where=[
                'admin_name'=>$value
                ];
                }else{
                $where=[
                    'admin_id'=>['neq',$data['admin_id']],
                    'admin_name'=>$value
                ];
            }
            $model=model('Admin');
            $arr=$model->where($where)->find();
            if(!empty($arr)){
                fail('账号已存在');
            }else{
                 return true;
            }  
            }
        }
        public function checkPwd($value,$rule,$data){
            $reg='/^.{6,16}$/';
            if(!preg_match($reg,$value)){
                return "密码为6-16位";
            }else{
                return true;
            }
        }
        public function checkTel($value,$rule,$data){
            $reg='/^1[3-9]\d{9}$/';
            if(!preg_match($reg,$value)){
                return "请输入正确手机号";
            }else{
                return true;
            }
        }
    }
?>