<?php 
namespace app\admin\validate;
    use \think\Validate;
    class Goods extends Validate{
        protected $rule =   [
            'goods_name'  => 'require',
            'goods_selfprice' => 'require',
            'goods_marketprice' => 'require',
            'goods_img'  => 'require',
            'goods_imgs'  => 'require',
            'goods_num'  => 'require',
            'goods_score'  => 'require',
            
        ];
        protected $message =[
            'goods_name.require' => '商品名称必填',
            'goods_selfprice.require' => '本店价格必填',
            'goods_marketprice.require' => '市场价格必填',
            'goods_score.require' => '商品积分必填',
            'goods_num.require' => '商品库存必填',
            'goods_img.require' => '商品图片必选',
            'goods_imgs.require' => '商品轮播图必选',

              ];
        protected $scene = [ 
            'add'=>['goods_name','goods_selfprice','goods_marketprice','goods_up','goods_img','goods_imgs','goods_num','goods_score'],
            'edit'=>['goods_name','goods_selfprice','goods_marketprice','goods_up','goods_num','goods_score']
            ];
        }
?>