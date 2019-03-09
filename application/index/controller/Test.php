<?php
namespace app\index\controller;
use think\Controller;
class Test extends Controller{
    public function index(){
      $res=sendEmail('1428511549@qq.com',rand(100000,999999));
      //dump($res);
     /*  $res=sendSms('17788131078',rand(100000,999999));
      echo $res->Code; */
    }
}