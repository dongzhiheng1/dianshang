<?php 
namespace app\admin\validate;
    use \think\Validate;
    class Brand extends Validate{
        protected $rule =   [
            'brand_name'  => 'require|checkName',
            'brand_url'  => 'require|url',
            'brand_describe'  => 'require',
        ];
        protected $message  =   [
            'brand_name.require' => '品牌名称必填',
            'brand_url.require' => '品牌网址必填',
            'brand_url.url' => '请输入正确网址格式',
            'brand_describe'  => '品牌描述不能为空',
              ];
        protected $scene = [ 
            'add'=>['brand_name','brand_url','brand_describe'],
            'edit'=> ['brand_name','brand_url']
            ];

 //品牌验证 
 public function checkName($value,$rule,$data){
    if(empty($data['brand_id'])){
        $where=[
            'brand_name'=>$value
        ];
    }else{
        $where=[
            'brand_id'=>['neq',$data['brand_id']],
            'brand_name'=>$value
        ];
    }
    $model=model('Brand');
    $arr=$model->where($where)->find();
    if(!empty($arr)){
      fail('品牌已存在');
    }else{
         return true;
    }

}

    }
?>