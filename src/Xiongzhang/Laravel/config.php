<?php
/**
 * 熊掌号SDK配置
 * User: linyi(linyi05@baidu.com)
 * Date: 2018/3/19
 * Time: 9:44
 */
return [
    'token' => env('XZH_TOKEN'),
    'encodingAesKey' => env('XZH_AES_KEY'),
    'clientId' => env('XZH_CLIENTID'),
    'clientSecret' => env('XZH_CLIENTSECRET'),
    'packType' => 'xml',

    'log' => [
        'level' => 'debug',
        'file' => storage_path('logs/xzh.log'),
    ],
];