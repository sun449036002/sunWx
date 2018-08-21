<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/1
 * Time: 22:04
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImgController
{

    /**
     * 图片上传
     * @param Request $request
     * @return false|string|array
     */
    public function upload(Request $request) {
        $result = ['code' => 0, 'msg' => 'ok', 'imgs' => []];

        $data = $request->all();
        $destinationPath = "/images/cash-back/" . date("Ymd");
//        dd(!empty($data['mfile']));
        if (!empty($data['mfile'])) {
            $filePath = $request->file("mfile")->store($destinationPath);
            $result['imgs'][] = "/" . ltrim($filePath, "/");
            return $result;
        } else if (!empty($data['imgs'])) {
            $filePath = [];
            foreach ($data['imgs'] as $key => $img) {
                // 判断图片上传中是否出错
                if (!$img->isValid()) {
                    $result['code'] = 100;
                    $result['msg'] = '上传图片出错，请重试';
                }
                if(!empty($img)){//此处防止没有多文件上传的情况
                    $allowed_extensions = ["png", "jpg", "gif"];
                    if ($img->getClientOriginalExtension() && !in_array($img->getClientOriginalExtension(), $allowed_extensions)) {
                        $result['code'] = 100;
                        $result['msg'] = '您只能上传PNG、JPG或GIF格式的图片！';
                    }
                    $extension = $img->getClientOriginalExtension();   // 上传文件后缀
                    $fileName = date('YmdHis').mt_rand(100,999) . '.'.$extension; // 重命名
                    $ok = $img->move(storage_path() . "/app" . $destinationPath, $fileName); // 保存图片
                    $result['isOk'][] = $ok;
                    $filePath[] = $destinationPath.'/'.$fileName;
                }
            }
            $result['imgs'] = $filePath;
            return $result;
        } else if(!empty($data['base64str'])) {
            $file = $data['base64str'];
            try {
                if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $file, $match)) {
                    //创建目录
                    Storage::makeDirectory($destinationPath);

                    //上传文件
                    $type = $match[2];
                    $new_file = $destinationPath . date("/YmdHis-") . mt_rand(10000, 99999) . "." . $type;
                    $realPath = storage_path() . "/app" . $new_file;
                    if (file_put_contents($realPath, base64_decode(str_replace($match[1], '', $file)))) {
                        $result['imgs'][] = $new_file;
                        return $result;
                    }
                }
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
        }
        return $result;
    }
}