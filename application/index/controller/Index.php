<?php
namespace app\index\controller;
use think\Db;
use app\index\controller\Base;

class Index extends Base
{
    /**
     * @Author:      fyd
     * @DateTime:    2018-03-17 15:42:09
     * @Description: 显示操作的主界面
     */
    public function index()
    {
        $data = Db::name('data') -> find();
        return $this->fetch('temperature/temperature');
    }

}
