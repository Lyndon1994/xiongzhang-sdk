## SDK概要
### 目标：提供熊掌号开放平台的基本能力，提升熊掌号及TP的接入效率
#### 本版本支持的功能
##### [针对熊掌号]
* 熊掌号开放平台相关加减密算法 [完毕]
    * 消息加减密 aes封装 【支持】
    * server校验 sha1封装 【支持】
* PUSH 熊掌号消息处理 [完毕]
    * 文本类型消息接收、发送 【支持】
    * 图片类型消息接收、发送 【支持】
    * 语音类型消息接收、发送 [待开放功能]
    * 事件类型消息接收、发送 【支持】
        * 关注/取消关注
        * 菜单点击/跳转
    * PS:消息明文、密文格式都可用，兼容格式代调式
* PUSH 熊掌号支付消息处理 [完毕]
    * 支付事件消息接收、发送 【支持】
        * 支付成功回调
        * 退款成功回调
        
##### [针对TP]
* PUSH TP 消息处理 [完毕]
    * 接收熊掌号平台推送的 ticket 【支持】
    * B2B授权-授权结果（授权、更新授权、取消授权）通知【支持】
    * TP代开号-熊掌号审核结果通知 [待开放功能]
    * 接收已授权熊掌号的事件消息 [待开发]
        * PUSH 熊掌号消息处理 [完毕]
            * 文本类型消息接收、发送 【支持】
            * 图片类型消息接收、发送 【支持】
            * 语音类型消息接收、发送 [待开放功能]
            * 事件类型消息接收、发送 【支持】
                * 关注/取消关注
                * 菜单点击/跳转

## 安装
```bash
composer require lyndon1994/xiongzhang-sdk
```

## 使用
### 示例
```php
/**
 * 开发者设置
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
$xzhLib = Server::init($init);
$msgType = $xzhLib->getRevType();
$msgData = $xzhLib->getRevData();
$xzhLib::$log->notice("Rev msgType: {$msgType} msgData:" . json_encode($msgData));

// 根据消息类型，做业务响应
switch ($msgType) {
    case SdkConfig::MSGTYPE_TEXT:
        // 文本类型 $content 开发者根据$msgData自己组织回复
        $xzhLib->text(SdkConfig::REV_TEXT_DEFAULT_REPLY)->reply();
        break;
    case SdkConfig::MSGTYPE_EVENT:
        // 事件类型（如不需跟开发者交互，返回success即可）
        $event = $msgData['Event'];
        switch ($event) {
            case SdkConfig::EVENT_SUBSCRIBE:
                $xzhLib->text(SdkConfig::EVENT_SUBSCRIBE)->reply();
                break;
            case SdkConfig::EVENT_UNSUBSCRIBE:
                $xzhLib->text(SdkConfig::EVENT_UNSUBSCRIBE)->reply();
                break;
            case SdkConfig::EVENT_MENU_VIEW:
                $xzhLib->text(SdkConfig::EVENT_MENU_VIEW)->reply();
                break;
            case SdkConfig::EVENT_MENU_CLICK:
                $xzhLib->text(SdkConfig::EVENT_MENU_CLICK)->reply();
                break;
            default:
                // TODO 如有新增事件，在补充
                $xzhLib->text(SdkConfig::REV_NOT_KNOW_MSGTYPE_REPLY)->reply();
        }
        break;
    case SdkConfig::MSGTYPE_IMAGE:
        // 返回一张图片，注意回复的图片 mediaId 需要先上传
        $xzhLib->image(691654)->reply();
        break;
    case SdkConfig::MSGTYPE_VOICE:
//        $xzhLib->voice(691654)->reply();
        $xzhLib->text('waiting!!!')->reply();
        break;
    case SdkConfig::MSGTYPE_PAY:
        $payEvent = $msgData['Event'];
        switch ($payEvent) {
            case SdkConfig::EVENT_PAY_PAY:
                $xzhLib->pay(1)->reply();
                break;
            case SdkConfig::EVENT_PAY_REFUND:
                $xzhLib->refund()->reply();
                break;
            default:
                $xzhLib::$log->notice('pay event error; event:' . $payEvent);
                $xzhLib->text('')->reply();
        }
        break;
    default:
        // TODO 如有新增消息类型，在补充
        $xzhLib->text(SdkConfig::REV_SUCCESS_REPLY)->reply();
}
```