<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/12
 * Time: 17:11
 */

namespace App\Http\Controllers;


class wxController
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    //验证消息
    public function api()
    {
        file_put_contents("/data/www/sunWx/storage/logs/wx-api.log", json_encode($_GET, JSON_UNESCAPED_UNICODE), FILE_APPEND);
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){    
            echo $echoStr;
            exit;
        }
    }
    //检查签名
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = "weiphp";
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if($tmpStr == $signature){
            return true;

        }else{
            return false;
        }
    }
}