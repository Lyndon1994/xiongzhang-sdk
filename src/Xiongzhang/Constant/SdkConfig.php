<?php
/**
 * SdkConfig
 * sdk常量配置
 *
 * @author wangzhongyou
 * @date 2018/2/23 下午1:03
 */

namespace Xiongzhang\Constant;

class SdkConfig
{
    /**
     * 常量
     */
    const REV_SUCCESS_REPLY = 'success';
    const REV_TEXT_DEFAULT_REPLY = '为文本消息类型，我是测试哥！';
    const REV_NOT_KNOW_MSGTYPE_REPLY = '不认识的消息类型，该怎么回复你呢！';

    /**
     * 加密类型
     */
    const ENCRYPT_TYPE_AES = 'aes';

    /**
     * 消息打包格式
     */
    const PACK_TYPE_XML = 'xml';
    const PACK_TYPE_JSON = 'json';

    /**
     * 消息类型
     */
    const MSGTYPE_TEXT = 'text';
    const MSGTYPE_IMAGE = 'image';
    const MSGTYPE_EVENT = 'event';
    const MSGTYPE_VOICE = 'voice';
    // 支付消息
    const MSGTYPE_PAY = 'mch';

    /**
     * TP 消息类型
     */
    const INFOTYPE_TP_VERIFY_TICKET = 'tp_verify_ticket';
    const INFOTYPE_AUTHORIZED = 'authorized';
    const INFOTYPE_UPDATEAUTHORIZED  = 'updateauthorized';
    const INFOTYPE_UNAUTHORIZED = 'unauthorized';

    /**
     * 事件类型
     */
    const EVENT_SUBSCRIBE = 'subscribe';       //订阅
    const EVENT_UNSUBSCRIBE = 'unsubscribe';   //取消订阅
    const EVENT_MENU_VIEW = 'VIEW';            //菜单 - 点击菜单跳转链接
    const EVENT_MENU_CLICK = 'CLICK';          //菜单 - 点击菜单拉取消息

    // 支付事件
    const EVENT_PAY_PAY = 'pay';              //支付成功
    const EVENT_PAY_REFUND = 'refund';        //退款成功

    /**
     * OpenApi 素材管理
     */
    const OPENAPI_MEDIA_UPLOADIMG = '/rest/2.0/cambrian/media/uploadimg';

}