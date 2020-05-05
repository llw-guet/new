<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

use PHPMailer\PHPMailer\PHPMailer;
use Exception;

// 应用公共文件，当前目录下的所有函数在 application目录下均可被调用

/**
 * 发送http请求
 * @param $url  get请求地址
 * @param int $httpCode   返回状态码
 * @return bool|string
 */
function curl_get($url, &$httpCode = 0){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //不做整数校验，部署在linux环境下请改为true
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    $file_contents = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $file_contents;
}

function getRandChars($length){
    $str = null;
    $charPool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789abcdefghijklmnopqrstuvwxyz';
    $max = strlen($charPool) - 1;

    for($i=0;$i<$length;$i++){
        $str .= $charPool[rand(0,$max)];
    }

    return $str;
}

function sendMail($to, $title, $content){
    $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
    try {
        //服务器配置
        $mail->CharSet ="UTF-8";                     //设定邮件编码
        $mail->SMTPDebug = 0;                        // 调试模式输出
        $mail->isSMTP();                             // 使用SMTP
        $mail->Host = 'smtp.163.com';                // SMTP服务器
        $mail->SMTPAuth = true;                      // 允许 SMTP 认证
        $mail->Username = 'llw18589833435@163.com';                // SMTP 用户名  即邮箱的用户名
        $mail->Password = 'ZRGBKNYYYPIMVUOG';             // SMTP 密码  部分邮箱是授权码(例如163邮箱)
        $mail->SMTPSecure = 'ssl';                    // 允许 TLS 或者ssl协议
        $mail->Port = 25;                            // 服务器端口 25 或者465 具体要看邮箱服务器支持

        $mail->setFrom('llw18589833435@163.com', 'Mailer');  //发件人
        $mail->addAddress('921953846@qq.com', 'Joe');  // 收件人
        //$mail->addAddress('ellen@example.com');  // 可添加多个收件人
//        $mail->addReplyTo('xxxx@163.com', 'info'); //回复的时候回复给哪个邮箱 建议和发件人一致
        //$mail->addCC('cc@example.com');                    //抄送
        //$mail->addBCC('bcc@example.com');                    //密送

        //发送附件
        // $mail->addAttachment('../xy.zip');         // 添加附件
        // $mail->addAttachment('../thumb-1.jpg', 'new.jpg');    // 发送附件并且重命名

        //Content
        $mail->isHTML(true);                                  // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
        $mail->Subject = '这里是邮件标题' . time();
        $mail->Body    = '<h1>这里是邮件内容</h1>' . date('Y-m-d H:i:s');
        $mail->AltBody = '如果邮件客户端不支持HTML则显示此内容';

        $mail->send();
        return '邮件发送成功';
    } catch (Exception $e) {
        return '邮件发送失败: ' . $mail->ErrorInfo;
    }
//    $mail = new PHPMailer();
//    //设置为要发邮件
//    $mail->isSMTP();
//    //是否允许发送HTML代码作为邮件的内容
//    $mail->isHTML(true);
//    $mail->CharSet = 'utf-8';
//    //是否需要身份认证
//    $mail->SMTPAuth = true;
//    //邮件服务器上的账号是什么:  后台发送邮箱
//    $mail->From = "llw18589833435@163.com";
//    // smtp登录的账号 163邮箱即可
//    $mail->Username = "llw18589833435@163.com";
//    // smtp登录的密码 使用生成的授权码
//    $mail->Password = "ZRGBKNYYYPIMVUOG";
//    $mail->FromName = "lingShiShangFan";
//    // 发送邮件的服务协议地址
//    $mail->Host = "smtp.163.com";
//    // 设置ssl连接smtp服务器的远程服务器端口号
//    $mail->Port = 25;
//
//    // 收件人
//    $mail->addAddress($to);
//    // 邮件标题
//    $mail->Subject = $title;
//    // 邮件内容
//    $mail->Body = $content;
//    $mail->send();
}
