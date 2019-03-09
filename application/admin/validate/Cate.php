<?php 
namespace app\admin\validate;
    use \think\Validate;
    class Cate extends Validate{
        protected $rule = [
            'cate_name'  => 'require|checkName',
        ];
        protected $message  =   [
            'cate_name.require' =>'分类名称必填',
              ];
        protected $scene = [ 
            'edit'=> ['cate_name']
            ];

 //分类验证 
 public function checkName($value,$rule,$data){
    if(empty($data['cate_id'])){
        $where=[
            'cate_name'=>$value
        ];
    }else{
        $where=[
            'cate_id'=>['neq',$data['cate_id']],
            'cate_name'=>$value
        ];
    }
    $model=model('Cate');
    $arr=$model->where($where)->find();
    if(!empty($arr)){
      fail('分类已存在');
    }else{
         return true;
    }

}

}
?>