<?php
/**
 *
 * User: linyi(linyi05@baidu.com)
 * Date: 2018/3/18
 * Time: 19:26
 */

namespace Xiongzhang;

use Xiongzhang\Encrypt\AesEncrypt;

class AesEncryptTest extends \PHPUnit_Framework_TestCase
{
    public function testAes()
    {
        $clientId = 'kUz$nDAqgOtkD@15n@!#O866lWhWdemo';
        $aesKey = 'thisisademonotusethsbaidumsitetestnotuseths';
        $crypto = new AesEncrypt($clientId, $aesKey);

        $xml = <<<EOF
    <xml>
    <ToUserName><![CDATA[熊掌号ID]]></ToUserName>
    <FromUserName><![CDATA[openId]]></FromUserName>
    <CreateTime><![CDATA[1500449499]]></CreateTime>
    <MsgType><![CDATA[event]]></MsgType>
    <Event><![CDATA[subscribe]]></Event>
    </xml>
EOF;
        echo 'xml: ' . $xml . PHP_EOL;

        $eXml = $crypto->encrypt($xml);
        echo 'encode: ' . $eXml . PHP_EOL;

        $dXml = $crypto->decrypt($eXml);
        echo 'decode: ' . $dXml . PHP_EOL;
    }
}
