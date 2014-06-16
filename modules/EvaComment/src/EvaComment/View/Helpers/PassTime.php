<?php
namespace Eva\EvaComment\View\Helpers;

class PassTime
{
    public function __invoke($timestamp)
    {
        $now = time();
        $passTime = $now-$timestamp;
        $oneMinute = 60;
        $oneHour = $oneMinute*60;
        $oneDay = $oneHour*24;
        $oneMonth = $oneDay*30;
        $oneYear = $oneMonth*12;
        $str = '';
        if($passTime>$oneYear){
            $n = $passTime/$oneYear;
            $n = intval($n);
            $str = $n.'年前';
        }elseif($passTime>$oneMonth){
            $n = $passTime/$oneMonth;
            $n = intval($n);
            $str = $n.'月前';
        }elseif($passTime>$oneDay){
            $n = $passTime/$oneDay;
            $n = intval($n);
            $str = $n.'天前';
        }elseif($passTime>$oneHour){
            $n = $passTime/$oneHour;
            $n = intval($n);
            $str = $n.'小时前';
        }elseif($passTime>$oneMinute){
            $n = $passTime/$oneMinute;
            $n = intval($n);
            $str = $n.'分钟前';
        }else{
            //            $str = $passTime.'秒前';
            $str = '刚刚...';
        }

        return $str;

    }
}
