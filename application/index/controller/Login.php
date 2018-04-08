<?php
namespace app\index\controller;
use think\Db;
use app\index\controller\Base;

class Login extends Base{

	public function index(){
		return $this->fetch('login');
	}

	public function login(){
		$data = input('post.');
		$username = $data['username'];
		$password = $data['password'];

		$res1 = Db::name('user') -> where('username',$username) -> find();
		if($res1){
			if($res1['password'] == $password){
				return 1;
			}else{
				return 0;
			}
		}else{
			return 0;
		}
	}
}