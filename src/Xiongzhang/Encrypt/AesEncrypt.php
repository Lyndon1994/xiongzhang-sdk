<?php
/**
 * AesEncryptNew
 * aes 加减密
 * 支持php7.1，需要PHP>=5.3，需要开启openssl扩展
 * @author linyi
 * @date 2018/2/23 下午1:08
 */

namespace Xiongzhang\Encrypt;

use Exception as BaseException;
use Xiongzhang\Exception\XzhException;
use Xiongzhang\Server;

class AesEncrypt
{

    /**
     * @var int
     */
    public static $blockSize = 32;

    /**
     * @var
     */
    private $clientId;

    /**
     * aes key
     * @var bool|string
     */
    private $aesKey;

    /**
     * AesEncrypt constructor.
     * @param $clientId
     * @param $encodingAesKey
     */
    public function __construct($clientId, $encodingAesKey)
    {
        $this->clientId = $clientId;
        $this->aesKey = base64_decode($encodingAesKey . "=");
    }

    /**
     * 对明文进行加密
     * @param $text
     * @return bool|string
     * @throws XzhException
     */
    public function encrypt($text)
    {
        try {
            // 获得16位随机字符串，填充到明文之前
            $random = $this->getRandomStr();;
            $text = $random . pack("N", strlen($text)) . $text . $this->clientId;

            $iv = substr($this->aesKey, 0, 16);
            // 对明文进行补位填充
            $text = $this->encode($text);
            // 加密
            $encrypted = openssl_encrypt($text, 'aes-256-cbc', $this->aesKey, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);

            // 使用BASE64对加密后的字符串进行编码
            return base64_encode($encrypted);
        } catch (BaseException $e) {
            throw new XzhException("AesEncrypt AES加密失败; e:" . $e->getMessage());
        }
    }

    /**
     * 对密文进行解密
     * @param $encrypted
     * @return bool|string
     * @throws XzhException
     */
    public function decrypt($encrypted)
    {
        try {
            // 使用BASE64对需要解密的字符串进行解码
            $ciphertextDec = base64_decode($encrypted);
            $iv = substr($this->aesKey, 0, 16);

            // 解密
            $decrypted = openssl_decrypt($ciphertextDec, 'aes-256-cbc', $this->aesKey, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
        } catch (BaseException $e) {
            throw new XzhException("AesEncrypt AES解密失败; e:" . $e->getMessage());
        }

        try {
            // 去除补位字符
            $result = $this->decode($decrypted);
            // 去除16位随机字符串,网络字节序和clientId
            if (strlen($result) < 16) {
                Server::$log->debug("AesEncrypt AES解密串非法，小于16位;");
                throw new XzhException("AesEncrypt AES解密串非法，小于16位;");
            }
            $content = substr($result, 16, strlen($result));
            $lenList = unpack("N", substr($content, 0, 4));
            $xmlLen = $lenList[1];
            $xmlContent = substr($content, 4, $xmlLen);
            $fromClientId = substr($content, $xmlLen + 4);
        } catch (BaseException $e) {
            throw new XzhException("AesEncrypt 平台发送的xml不合法; e:" . $e->getMessage());
        }

        if ($fromClientId != $this->clientId) {
            throw new XzhException("AesEncrypt 校验ClientID失败;");
        }

        return $xmlContent;
    }

    /**
     * 对需要加密的明文进行填充补位
     * @param $text
     * @return string
     */
    private function encode($text)
    {
        $textLength = strlen($text);
        //计算需要填充的位数
        $amountToPad = self::$blockSize - ($textLength % self::$blockSize);
        if ($amountToPad == 0) {
            $amountToPad = self::$blockSize;
        }
        //获得补位所用的字符
        $padChr = chr($amountToPad);
        $tmp = "";
        for ($index = 0; $index < $amountToPad; $index++) {
            $tmp .= $padChr;
        }
        return $text . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     * @param $text
     * @return bool|string
     */
    private function decode($text)
    {
        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > self::$blockSize) {
            $pad = 0;
        }

        return substr($text, 0, (strlen($text) - $pad));
    }

    /**
     * 随机生成16位字符串
     * @return string
     */
    private function getRandomStr()
    {
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
        $str = "";
        for ($i = 0; $i < 16; $i++) {
            $str .= $strPol[mt_rand(0, $max)];
        }
        return $str;
    }
}