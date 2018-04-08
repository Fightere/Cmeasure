<?php
namespace app\index\controller;
use think\Db;
use app\index\controller\Base;

class Data extends Base
{
	/**
     * @Author:      fyd
     * @DateTime:    2018-03-17 15:44:50
     * @Description: 对于数据的处理,$a,$b都是从数组中获取到的数据
     */
    private function oper($a,$b){
        $a0 = '0x'.$a;
        $a1 = hexdec($a0);
        $b0 = '0x'.$b;
        $data = '0x'.$a.$b; //得到16进制数字

        if($a1 > 128){ //代表是第一种类型，也就是负值
            $demi = hexdec($data);
            $bin = decbin($demi);
            $recv = ~$demi; //取反

            $datan = bindec((int)substr(decbin($recv),-16));
            $fdata = ('0x'.dechex($datan) + 1) / 10;
        }else{  //代表是第二种类型，也就是正值
            $demi = hexdec($data);
            $fdata = $demi / 10;
        }

        $fdata = number_format($fdata,2);
        return $fdata;
    }

    /**
     * @Author:      fyd
     * @DateTime:    2018-03-24 15:35:17
     * @Description: 获取文件最终修改时间
     */
    private function mtime($filepath){
        $moditime = filemtime($filepath);
        $time = date("Y-m-d H:i:s",$moditime);
        return $time;
    }

    /**
     * @Author:      fyd
     * @DateTime:    2018-03-17 18:36:36
     * @Description: 把读取到的数据添加到数据库中
     */
	public function add(){
		// while(True){
			$myfile = fopen("./log.txt", "r") or die("Unable to open file!");
            $filestr = fread($myfile,filesize("./log.txt"));
            $filestr = str_replace('EE', '', $filestr);
            $filearr = explode(' ', $filestr);
            $filearr = array_values(array_filter($filearr));

            $mtime = $this->mtime("./log.txt");
            $mres = Db::name('set')->where('setname','filemtime')->find();
            if($mres){
                if($mtime == $mres['setvalue']){ //这里表明文件并没有发生变化，所以不进行数据库操作
                    echo "并没有写入文件！";
                }else{ //这里说明已经进行了新的写入，所以要从其中读取后面三组的数据然后进行添加数据操作
                    Db::name('set')->where('setname','filemtime')->update(['setvalue'=>$mtime]);
                    $count = count($filearr);

                    $counts = Db::name('data')
                                ->count();

                    if($counts > 20){
                        $res1 = Db::name('data')
                                -> where('id','>',0)
                                -> limit(5)
                                -> delete();
                    }

                    for($i=0;$i<3;$i++){
                        $a = $filearr[$count-(2+3*$i)];
                        $b = $filearr[$count-(1+3*$i)];

                        $origin = '0x'.$a.$b;

                        $intime = $filearr[$count-(3+3*$i)];
                        $intimearr = explode('.',trim($intime,'[]'));
                        $time = $intimearr[0];

                        $res = $this->oper($a,$b);

                        // echo $res.'---'.$time.'---'.$origin.'<br>';
                        $insertdata = ['datanum'=>$res,'origindata'=>$origin,'port'=>(3-$i),'inserttime'=>$time];
                        $res = Db::name('data')->insert($insertdata);
                    }
                }
            }else{
                Db::name('set')->insert(['setname'=>'filemtime','setvalue'=>$mtime]);
            }

            echo "
                <script>
                    function myrefresh() 
                    { 
                    window.location.reload(); 
                    } 
                    setTimeout('myrefresh()',10000); 
                </script>
            ";
	}

    /**
     * @Author:      fyd
     * @DateTime:    2018-03-19 10:39:53
     * @Description: 从数据库中提取数据
     */
    public function show(){
        return $this->fetch('display/display');

    }

    /**
     * @Author:      fyd
     * @DateTime:    2018-03-24 19:04:57
     * @Description: 获取所需数据
     */
    private function formatdata($port){
        $data = Db::name('data')
                -> where('port',$port)
                -> order('id desc')
                -> limit(20)
                -> select();
        $count = count($data);

        $arr = [];
        for($i=0;$i<$count;$i++){
            $arr[$i] = [$data[$i]['inserttime'],$data[$i]['datanum']];
        }

        return $arr;
    }

    /**
     * @Author:      fyd
     * @DateTime:    2018-03-24 17:02:33
     * @Description: 数据转化成json格式
     */
    public function getdata(){
        $arr1 = $this->formatdata(1);
        $arr2 = $this->formatdata(2);
        $arr3 = $this->formatdata(3);
        
        $data = [$arr1,$arr2,$arr3];

        dump($data);
        echo json(['code'=>0,'data'=>$data])->getcontent();
    }
}