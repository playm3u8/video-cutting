#!/usr/bin/php
<?php

$info = [];
foreach ($argv as $key => $value) {
	if(substr($value, 0,1) == "-"){
		if(!empty($argv[$key+1])){
			$info[substr($value, 1)] = $argv[$key+1];
		}
	}
}

if(array_key_exists("d",$info) && array_key_exists("s",$info)){
	E("执行参数有误");
}
if(substr($info['h'], -1) != "/"){
	E("保存路径格式有误，请以'/'结尾");
}

$shell  = shell_exec("ffmpeg -i ".$info['f']." 2>&1");
$time_f = explode(':', strzhong($shell, 'Duration: ', ','));
$time_d = intval($time_f[0]) * 3600 + intval($time_f[1]) * 60 + ceil($time_f[2]);

if(array_key_exists("s", $info)){
	// 每段秒数
	$m = $info['s'];
	$info['d'] = ceil($time_d / $info['s']);
} else {
	// 每段秒数
	$m = $time_d / $info['d'];
}

if($info['d'] < 2) {
	E("分割段数不能小于2段");
}
// 目录不存在就创建一个
if(!file_exists($info['h'])){
	mkdir($info['h']);
}

if(array_key_exists("d", $info)){

	// 取文件后缀
	$exe  = stryou($info['f'], '.');
	// 去除后缀名
	$name =  stryou(strzuo($info['f'], '.'.$exe), '/');
	if(empty($name)){
		E("源文件路径格式有误");
	}
    // 循环处理
	for ($x=0; $x<=$info['d']-1; $x++) {
		if($x == 0){
			$tt = secToTime(0);
		} else {
			$tt = secToTime($m * $x);
		}
		// 保存文件完整路径
		$path = $info['h'].$name."_".($x + 1).".".$exe;
		// 删除临时文件
		if(file_exists($path)){
			unlink($path);
		}
		$shell = shell_exec("ffmpeg -ss ".$tt." -i ".$info['f']." -c copy -t ".$m." ".$path." 2>&1");
		if(strstr($shell, 'Duration:')){
			echo $path." OK"."\r\n";
		} else {
			echo $path." ERROR"."\r\n";
		}
	}
}


function E($str){
	die("$str\r\n");
}

function stryou( $str , $you){
    $wz = strrpos($str,$you);
    if($wz === false){
        return null;
    }else{
        return substr($str, $wz + strlen($you));
    }
}

function strzuo( $str , $zuo ){
    $wz = strpos( $str , $zuo);
    if($wz === false){
        return $str;
    }
    if ( !$text = substr( $str , 0 , $wz )){
        return null;
    }else{
        return $text;
    }
}

function strzhong($str, $leftStr, $rightStr){
    if (!empty($str)) {
        $left = strpos($str, $leftStr);
        if ($left === false) {
            return '';
        }
        $right = strpos($str, $rightStr, $left + strlen($leftStr));
        if ($left === false or $right === false) {
            return '';
        }
        return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
    }
}
// time
function secToTime($times){  
        $result = '00:00:00';  
        if ($times>0) {  
                $hour = floor($times/3600); 
                if($hour<10){
                    $hour = "0".$hour;
                } 
                $minute = floor(($times-3600 * $hour)/60); 
                if($minute<10){
                    $minute = "0".$minute;
                } 
                $second = floor((($times-3600 * $hour) - 60 * $minute) % 60); 
                 if($second<10){
                    $second = "0".$second;
                } 
                $result = $hour.':'.$minute.':'.$second;  
        }  
        return $result;  
} 
