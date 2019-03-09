<?php 
namespace app\admin\validate;
    use \think\Validate;
    class Friendurl extends Validate{
        protected $rule =   [
            'friend_name'  => 'require|checkName',
            'friend_url'  => 'require|url',
        ];
        protected $message  =   [
            'friend_name.require' => '友链名称必填',
            'friend_url.require' => '网址必填',
            'friend_url.url' => '请输入正确网址格式',
              ];
        protected $scene = [ 
            'add'=>['friend_name','friend_url'],
            'edit'=> ['friend_name','friend_url']
            ];

 //网址验证唯一
 public function checkName($value,$rule,$data){
    if(empty($data['friend_id'])){
        $where=[
            'friend_name'=>$value
        ];
    }else{
        $where=[
            'friend_id'=>['neq',$data['friend_id']],
            'friend_name'=>$value
        ];
    }
    $model=model('Friendurl');
    $arr=$model->where($where)->find();
    if(!empty($arr)){
      fail('网址已存在');
    }else{
         return true;
    }
}

}
?>