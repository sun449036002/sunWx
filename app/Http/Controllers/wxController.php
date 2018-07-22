<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/12
 * Time: 17:11
 */

namespace App\Http\Controllers;

use EasyWeChat\Factory;


class wxController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function api() {
        $config = [
            'app_id' => 'wx11fe145bfca2b25e',
            'secret' => 'b8fdd5d132a3cc9c550ba40d001c6907',

            'response_type' => 'array',

            'log' => [
                'level' => 'debug',
                'file' => storage_path() . '/wechat.log',
            ],
        ];


        $app = Factory::officialAccount($config);

        $response = $app->server->serve();

        // 将响应输出
        return $response;
    }

    //验证消息
    public function api2()
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