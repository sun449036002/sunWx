<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/23
 * Time: 13:04
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestCommand extends Command
{

    protected $signature = "sym:test";

    protected $description = "测试使用的";

    public function handle() {
        $this->info("Test In This Time:" . date("Y-m-d H:i:s"));
    }
}