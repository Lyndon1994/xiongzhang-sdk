<?php
/**
 * sdk TP入口
 *
 * @author wangzhongyou
 * @date 2018/2/23 下午1:59
 */

require '../vendor/autoload.php';

use Xiongzhang\Constant\SdkConfig;
use Xiongzhang\Server;


/**
 * TP 开发者设置
 */
$init = array(
    'token' => 'TOKEN',
    'encodingAesKey' => 'ENCODINGAESKEY',
    'clientId' => 'CLIENTID',
    'clientSecret' => 'CLIENTSECRET',
    'packType' => 'json',

    'log' => [
        'level' => 'debug',
        'file' => 'xzh.log',
    ],
);

$tpLib = Server::init($init);
$infoType = $tpLib->getRevType();
$infoData = $tpLib->getRevData();
$xzhLib::$log->notice("Rev msgType: {$msgType} msgData:" . json_encode($msgData));

// 根据消息类型，做业务响应
switch($infoType) {
    case SdkConfig::INFOTYPE_TP_VERIFY_TICKET:
    case SdkConfig::INFOTYPE_AUTHORIZED:
    case SdkConfig::INFOTYPE_UPDATEAUTHORIZED:
    case SdkConfig::INFOTYPE_UNAUTHORIZED:
        $tpLib->replySuccess(SdkConfig::REV_SUCCESS_REPLY);
        break;
    default:
        $tpLib->text(SdkConfig::REV_SUCCESS_REPLY)->reply();
}
