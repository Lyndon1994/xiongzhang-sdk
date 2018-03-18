<?php
/**
 * SHA1Util
 * sha1工具
 *
 * @author wangzhongyou
 * @date 2018/2/23 下午2:47
 */

namespace Xiongzhang\Util;

class SHA1Util
{
    /**
     * sha1 加密
     * @param $strToken
     * @param $intTimeStamp
     * @param $strNonce
     * @param string $strEncryptMsg
     * @return string
     */
    public static function getSHA1($strToken, $intTimeStamp, $strNonce, $strEncryptMsg = '')
    {
        $arrParams = array(
            $strToken,
            $intTimeStamp,
            $strNonce,
        );
        if (!empty($strEncryptMsg)) {
            array_unshift($arrParams, $strEncryptMsg);
        }
        sort($arrParams, SORT_STRING);
        $strParam = implode($arrParams);
        return sha1($strParam);
    }
}