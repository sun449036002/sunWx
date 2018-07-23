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