<?php
// | Create: 2017/2/11 
// +----------------------------------------------------------------------
// | Author: 海枯 <haiku888@foxmail.com> 
// +----------------------------------------------------------------------
// | Description:  
// +----------------------------------------------------------------------

include_once 'WechatSubscribedAccountSDK.class.php';

$wechat = new WechatSubscribedAccountSDK('weixin');
$wechat->checkSignature();

$type = $wechat->getMsg()->getRecType();
switch ($type) {
    case WechatSubscribedAccountSDK::MSGTEXT:
        $cont = $wechat->getRecContent();
        if ($cont == 'multi') {
            $arrs = array(
                array(
                    'Title' => '多图文1标题',
                    'Description' => '描述',
                    'PicUrl' => 'http://7xrnlf.com1.z0.glb.clouddn.com/run/act1.jpg',
                    'Url' => 'www.baidu.com'
                ),
                array(
                    'Title' => '多图文2标题',
                    'Description' => 'b',
                    'PicUrl' => 'http://7xrnlf.com1.z0.glb.clouddn.com/run/act2.jpg',
                    'Url' => 'http://volunteer.hkuboss.cn/'
                )
            );
            $wechat->news($arrs)->reply();
        } else {
            $wechat->text('未匹配关键词')->reply();
        }
        break;

    case WechatSubscribedAccountSDK::MSGEVENT:

        $ent = $wechat->getRecEvent();
        if ($ent == 'subscribe') {
            $wechat->text('欢迎关注xxx公众号');
        }
        break;
}
