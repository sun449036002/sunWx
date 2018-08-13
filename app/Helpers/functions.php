<?php
/**
 * 生成 URI 标识
 * @param int $len
 * @return string
 */
function generateUri($len = 16) {
    $str = "";
    $prev = date("ymd");
    $words = "qwertyuioplkjhgfdsazxcvbnm1234567890";
    for($i = 0; $i < $len - strlen($prev); $i ++) {
        $str .= $words[mt_rand(0, strlen($words) - 1)];
    }
    return $prev . $str;
}

/**
 * 获取微信配置信息
 * @return array
 */
function getWxConfig() {
    return [
        'app_id' => 'wx11fe145bfca2b25e',
        'secret' => 'b8fdd5d132a3cc9c550ba40d001c6907',

        //网页Oauth授权
        'oauth' => [
            'scopes'   => ['snsapi_userinfo'],
            'callback' => '/oauth-callback',
        ],

        'response_type' => 'array',

        'token'   => 'weiphp',// Token
//            'aes_key' => 'j87GWXELylXpJuxVGSZrvIm4jqEfYFZHAjm2A56nqAz',// EncodingAESKey，兼容与安全模式下请一定要填写！！！

        'log' => [
            'level' => 'debug',
            'file' => storage_path() . '/wechat.log',
        ],
    ];
}

/**
 * 返回 给客户端的 JSON 信息
 * @param $code
 * @param string $msg
 * @param array $data
 * @return string
 */
function ResultClientJson($code, $msg = '', $data = []) {
    return json_encode(['code' => $code, 'msg' => $msg, 'data' => $data]);
}

/**
 * 根据时间值，获取在多少时间前
 * @param $time int
 * @return string
 */
function beforeWhatTime($time) {
    $t = time() - $time;
    $m = intval($t / 60) % 60;
    $h = intval($t / 3600) % 60;
    $s = $t % 60;

    $str = $s . "秒前";
    if ($m > 0) {
        $str = $m . "分" . $str;
    }
    if ($h > 0) {
        $str = $h . "小时" . $str;
    }

    return $str;
}

/**
 * 处理头像地址
 * @param $headImgUrl
 * @return string
 */
function headImgUrl($headImgUrl) {
    if (strpos($headImgUrl, 'images/wxUserHead') === false) {
        return $headImgUrl;
    } else {
        return env('HEAD_IMG_DOMAIN') . "/" . ltrim($headImgUrl, '/');
    }
}