<?php
/**
 *
 * User: linyi(linyi05@baidu.com)
 * Date: 2018/3/18
 * Time: 17:07
 */

namespace Xiongzhang;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Xiongzhang\Constant\SdkConfig;
use Xiongzhang\Encrypt\AesEncrypt;
use Xiongzhang\Util\SHA1Util;
use Xiongzhang\Util\XML;

class Server
{
    private $token;
    private $encodingAesKey;
    private $clientId;
    private $clientSecret;
    private $packType;
    public static $log;

    private $encryptType;
    private $postxml;
    private $revMsgContent;

    private $sendMsg;

    /**
     * 单例
     * @var null
     */
    static private $_instance = null;

    /**
     * Server constructor.
     * @param $config
     */
    private function __construct($config)
    {
        //server config
        $this->token = isset($config['token']) ? $config['token'] : '';
        $this->encodingAesKey = isset($config['encodingAesKey']) ? $config['encodingAesKey'] : '';
        $this->clientId = isset($config['clientId']) ? $config['clientId'] : '';
        $this->clientSecret = isset($config['clientSecret']) ? $config['clientSecret'] : '';
        $this->packType = isset($config['packType']) && $config['packType'] == SdkConfig::PACK_TYPE_JSON
            ? SdkConfig::PACK_TYPE_JSON : SdkConfig::PACK_TYPE_XML;

        //log config
        $logLevel = isset($config['log']['level']) ? $config['log']['level'] : Logger::DEBUG;
        $logFile = isset($config['log']['file']) ? $config['log']['file'] : __DIR__ . '/xzh.log';
        self::$log = new Logger('xiongzhang');
        self::$log->pushHandler(new StreamHandler($logFile, $logLevel));

        // 校验请求有效性
        $this->validXzhRequest();
    }

    /**
     * 初始化
     * @param $config
     * @return null|Server
     */
    static public function init($config)
    {
        if (!(self::$_instance instanceof Server)) {
            self::$_instance = new self($config);
        }
        return self::$_instance;
    }


    /**
     * xzh请求校验
     * @return string
     */
    public function validXzhRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $postStr = file_get_contents('php://input');
            self::$log->notice('get request content:' . $postStr);
            $xmlMsgArray = $this->unpack($postStr);
            $this->encryptType = isset($_GET["encrypt_type"]) ? $_GET["encrypt_type"] : '';
            // aes加密
            $encryptStr = '';
            if (SdkConfig::ENCRYPT_TYPE_AES == $this->encryptType) {
                $encryptStr = $xmlMsgArray['Encrypt'];
                $pc = new AesEncrypt($this->clientId, $this->encodingAesKey);
                $decryptStr = $pc->decrypt($encryptStr);
                if (!$decryptStr) {
                    die('decrypt error!');
                }

                // 加密后的xml
                $this->postxml = $decryptStr;
            } else {
                // 明文xml
                $this->postxml = $postStr;
            }

            $this->revMsgContent = $this->unpack($this->postxml);

            // 校验请求来自xzh server有效性
            if (!$this->checkXzhServerSign($encryptStr)) {
                die('no access');
            }
        } else {
            $params = $_GET;
            self::$log->notice("GET:" . json_encode($params));

            // 校验请求来自xzh server有效性，验证通过返回 echostr
            $echoStr = $_GET["echostr"];
            if (isset($echoStr) && $this->checkXzhServerSign()) {
                die($echoStr);
            } else {
                die('no access');
            }
        }

        return true;
    }

    /**
     * 校验为xzh server的请求
     * @param string $str
     * @return bool
     */
    private function checkXzhServerSign($str = '')
    {
        $signature = isset($_GET["signature"]) ? $_GET["signature"] : '';
        // 如果存在加密验证则用加密验证字段
        $signature = isset($_GET["msg_signature"]) ? $_GET["msg_signature"] : $signature;
        $timestamp = isset($_GET["timestamp"]) ? $_GET["timestamp"] : '';
        $nonce = isset($_GET["nonce"]) ? $_GET["nonce"] : '';
        $sha1Str = SHA1Util::getSHA1($this->token, $timestamp, $nonce, $str);

        if ($sha1Str == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getRevData()
    {
        return $this->revMsgContent;
    }

    /**
     * @return bool
     */
    public function getRevType()
    {
        if (isset($this->revMsgContent['MsgType'])) {
            // PUSH给xzh的消息类型
            return $this->revMsgContent['MsgType'];
        } else if (isset($this->revMsgContent['msgType'])) {
            // TODO 支付消息类型，兼容逻辑，已经修复
            return $this->revMsgContent['msgType'];
        } else if (isset($this->revMsgContent['InfoType'])) {
            // PUSH给TP的消息类型
            return $this->revMsgContent['InfoType'];
        } else {
            return false;
        }
    }

    /**
     * 设置回复消息
     * wiki https://xiongzhang.baidu.com/open/wiki/chapter4/section4.2.4.html?t=1517897112422
     * @param string $text
     * @return $this
     */
    public function text($text = '')
    {
        $msg = array(
            'ToUserName' => $this->revMsgContent['FromUserName'],
            'FromUserName' => $this->revMsgContent['ToUserName'],
            'MsgType' => SdkConfig::MSGTYPE_TEXT,
            'Content' => $this->autoTextFilter($text),
            'CreateTime' => time(),
        );

        $this->Message($msg);
        return $this;
    }

    /**
     * 设置图片消息
     * wiki https://xiongzhang.baidu.com/open/wiki/chapter4/section4.2.4.html?t=1517897112422
     * @param string $mediaid
     * @return $this
     */
    public function image($mediaid = '')
    {
        $msg = array(
            'ToUserName' => $this->revMsgContent['FromUserName'],
            'FromUserName' => $this->revMsgContent['ToUserName'],
            'MsgType' => SdkConfig::MSGTYPE_IMAGE,
            'Image' => array('MediaId' => $mediaid),
            'CreateTime' => time(),
        );
        $this->Message($msg);
        return $this;
    }

    /**
     * 支付成功回调
     * wiki https://xiongzhang.baidu.com/open/wiki/chapter7/section4.3.2.html?t=1517897112422
     * @param int $isConsumed
     * @return $this
     */
    public function pay($isConsumed = 1)
    {
        $msg = array(
            'ToUserName' => $this->revMsgContent['FromUserName'],
            'FromUserName' => $this->revMsgContent['ToUserName'],
            'MsgType' => SdkConfig::MSGTYPE_PAY,
            // 1.未核销（不结算) 2.已核销（需结算）
            'isConsumed' => $isConsumed,
        );

        $this->Message($msg);
        return $this;
    }

    /**
     * 退款成功回调
     * wiki https://xiongzhang.baidu.com/open/wiki/chapter7/section4.3.5.html?t=1517897112422
     * @return $this
     */
    public function refund()
    {
        $msg = array(
            'ToUserName' => $this->revMsgContent['FromUserName'],
            'FromUserName' => $this->revMsgContent['ToUserName'],
            'MsgType' => SdkConfig::MSGTYPE_PAY,
        );

        $this->Message($msg);
        return $this;
    }

    /**
     * 过滤文字回复\r\n换行符
     * @param $text
     * @return mixed
     */
    private function autoTextFilter($text)
    {
        return str_replace("\r\n", "\n", $text);
    }

    /**
     * 设置发送消息
     * @param string $msg
     */
    public function Message($msg = '')
    {
        if (is_array($msg)) {
            $this->sendMsg = $msg;
        } else {
            $this->sendMsg = array();
        }
    }

    /**
     * 回复消息格式化
     * @param array $msg
     * @return bool
     */
    public function reply($msg = array())
    {
        // 防止不先设置回复内容，直接调用reply方法导致异常
        if (empty($msg) && empty($this->sendMsg)) {
            return false;
        }

        $msg = $this->sendMsg;
        $packStr = $this->pack($msg);
        // 如果来源消息为加密方式
        if (SdkConfig::ENCRYPT_TYPE_AES == $this->encryptType) {
            $pc = new AesEncrypt($this->clientId, $this->encodingAesKey);
            $encryptStr = $pc->encrypt($packStr);
            if (!$encryptStr) {
                self::$log->warning('reply encrypt err!');
                return false;
            }

            $timestamp = time();
            $nonce = rand(77, 999) * rand(605, 888) * rand(11, 99);
            // 密文方式，sha1 多一个加密密文字段
            $signature = SHA1Util::getSHA1($this->token, $timestamp, $nonce, $encryptStr);

            self::$log->notice('Reply before encrypt:' . $packStr);
            $packStr = $this->generatePackStr($encryptStr, $signature, $timestamp, $nonce);
        }
        self::$log->notice('Reply:' . $packStr);
        // 明文择直接返回
        die($packStr);
    }

    /**
     * Text消息类型，密文方式返回结构
     * @param $encrypt
     * @param $signature
     * @param $timestamp
     * @param $nonce
     * @return string
     */
    private function generatePackStr($encrypt, $signature, $timestamp, $nonce)
    {
        // 格式化加密信息
        $format = array(
            'Encrypt' => $encrypt,
            'MsgSignature' => $signature,
            'TimeStamp' => $timestamp,
            'Nonce' => $nonce,
        );

        return $this->pack($format);
    }

    /**
     * @param string $text
     */
    public function replySuccess($text = '')
    {
        self::$log->notice('Reply:' . $text);
        die($text);
    }


    /**
     * pack data to xml or json
     * @param array $data
     * @return mixed|string
     */
    private function pack($data)
    {
        if ($this->packType == SdkConfig::PACK_TYPE_XML) {
            return XML::build($data);
        }
        return json_encode($data);
    }

    /**
     * unpack data to array
     * @param string $data
     * @return array|mixed|\SimpleXMLElement
     */
    private function unpack($data)
    {
        if ($this->packType == SdkConfig::PACK_TYPE_XML) {
            return XML::parse($data);
        }
        return json_decode($data, true);
    }
}