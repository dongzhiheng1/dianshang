<?php
//检测是否合法提交
function checkRequest(){
    return request()->isPost()&&request()->isAjax();
}
//生成盐值
function createSalt(){
    $str="0123456789abcdefghijklmnopqrstuvwxyz!@#$%^&*";
    return substr(str_shuffle($str),rand(4,20),6);
}
//密码
function createPwd($pwd,$salt){
    return md5(md5($pwd).md5($salt));
}
//错误提示
function fail($font){
    $arr=['font'=>$font,'code'=>2];
    echo json_encode($arr);exit;
}
//正确提示
function successly($font){
        $arr=['font'=>$font,'code'=>1];
    echo json_encode($arr);exit;
}
//分类递归
function getCateInfo($data,$pid=0,$level=0){
    static $info=[];
    foreach($data as $k=>$v){
        if($v['pid']==$pid){
            $v['level']=$level;
            $info[]=$v;
            getCateInfo($data,$v['cate_id'],$v['level']+1);
        }
    }
    return $info;
}
//处理首页分类数据
function getIndexCateInfo($data,$pid=0){
    $cateInfo=[];
    foreach($data as $k=>$v){
        if($v['pid']==$pid){
            $son=getIndexCateInfo($data,$v['cate_id']);
            $v['son']=$son;
            $cateInfo[]=$v;
        }
    }
    return $cateInfo;
}
//获取当前顶级分类下的子分类
function getCateId($data,$pid){
    static $cateId=[];
    foreach($data as $k=>$v){
        if($v['pid']==$pid){
            $cateId[]=$v['cate_id'];
            getCateId($data,$v['cate_id']);
        }  
    }
    return $cateId;
}
//发送邮件
function sendEmail($address,$code){
     //实例化PHPMailer核心类
     $mail=new \email\PHPMailer();        
     //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
     $mail->SMTPDebug =false;
     //使用smtp鉴权方式发送邮件
     $mail->isSMTP();
     
     //smtp需要鉴权 这个必须是true
     $mail->SMTPAuth=true;
     
     //链接qq域名邮箱的服务器地址
     $mail->Host = 'smtp.163.com';//163邮箱：smtp.163.com
     
     //设置使用ssl加密方式登录鉴权
     $mail->SMTPSecure = 'ssl';//163邮箱就注释
     
     //设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
     $mail->Port = 465;//163邮箱：25
     
     //设置smtp的helo消息头 这个可有可无 内容任意
     // $mail->Helo = 'Hello smtp.qq.com Server';
     
     //设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
     //$mail->Hostname = 'http://localhost/';
     
     //设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
     $mail->CharSet = 'UTF-8';
     
     //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
     $mail->FromName = '董志恒';
     
     //smtp登录的账号 这里填入字符串格式的qq号即可
     $mail->Username ='d1428511549@163.com';
     
     //smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
     $mail->Password = 'admin123';//163邮箱也有授权码 进入163邮箱帐号获取
     
     //设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
     $mail->From = 'd1428511549@163.com';
     
     //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
     $mail->isHTML(true);
     
     //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
     $mail->addAddress($address);
     
     //添加多个收件人 则多次调用方法即可
     // $mail->addAddress('xxx@163.com','爱代码，爱生活世界');
     
     //添加该邮件的主题
     $mail->Subject = '注册成功';
     
     //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
     $mail->Body = '验证码为:'.$code.',五分钟内有效';
     
     //为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
     // $mail->addAttachment('./d.jpg','mm.jpg');
     //同样该方法可以多次调用 上传多个附件
     // $mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');
     
     $status = $mail->send();

     //简单的判断与提示信息
     if($status) {
        return true;
     }else{
        return false;
     } 
}
//发送短信
function sendSms($tel,$code){
    $params = array ();

    // *** 需用户填写部分 ***
    // fixme 必填：是否启用https
    $security = false;

    // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
    $accessKeyId = "LTAIixvHKmbVOLT4";
    $accessKeySecret = "jYFuJoVSiQJdHBTWQ7UAGvbRoGUSui";

    // fixme 必填: 短信接收号码
    $params["PhoneNumbers"] = $tel;

    // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
    $params["SignName"] = "layui模板";

    // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
    $params["TemplateCode"] = "SMS_151766404";

    // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
    $params['TemplateParam'] = Array (
        "code" => $code,
    );

    // fixme 可选: 设置发送短信流水号
    $params['OutId'] = "12345";

    // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
    $params['SmsUpExtendCode'] = "1234567";


    // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
    if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
        $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
    }

    // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
    $helper = new \aliyun\SignatureHelper();

    // 此处可能会抛出异常，注意catch
    $content = $helper->request(
        $accessKeyId,
        $accessKeySecret,
        "dysmsapi.aliyuncs.com",
        array_merge($params, array(
            "RegionId" => "cn-hangzhou",
            "Action" => "SendSms",
            "Version" => "2017-05-25",
        )),
        $security
    );

    return $content;
}
//随机生成验证码
function createCode(){
    $str=rand(100000,999999);
    return $str;
}
//获取分类名称做面包屑
function getCateName($arr,$pid){
   static $name=[];
   foreach($arr as $k=>$v){
       if($v['cate_id']==$pid){
           $name[]=['cate_name'=>$v['cate_name'],'cate_id'=>$v['cate_id']];
           getCateName($arr,$v['pid']);
       }
   }
   return $name;
}
//获取左侧递归
function getRecursionNode($data,$pid=0){
    $info=[];
    foreach($data as $k=>$v){
        if($v['pid']==$pid&&$v['node_type']==1){
            $son=getRecursionNode($data,$v['node_id']);
            $v['son']=$son;
            $info[]=$v;
        }
    }
    return $info;
}


