<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;

class Register extends BaseController {
    public function sendMail() {
        $to = "921953846@qq.com";
        $title = "PHPMailer测试";
        $content = "看看PHPMailer能不能成功使用";
        return sendMail($to, $title, $content);
    }
}