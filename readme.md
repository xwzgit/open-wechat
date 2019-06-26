###公众号第三方平台开发

####一、公众号第三方平台授权相关
```
    通用配置
    $config = [
        'open' => [
            'app_id' => 'wx3c7ae204**', //第三方平台appid
            'app_secret' => '52527e***a3acb55',//第三方平台appsecret
            'token' => 'op***', //第三方平台Token
            'encoding_aes_key' => '**a356e5949d', //数据处理秘钥（加密解密）
            'auth_redirect' => 'http://domain/open/auth/redirect',//授权回调地址
            'auth_page' => 'http://domain/', //授权发起页面地址（拥有回调成功后页面刷新）
        ],
        'log' => [//日志
            'file' => './Logs/log.log',
            'level' => 'debug',
            'type' => 'daily',
            'max_file' => 30
        ]
    ];
```
#####1，微信Verify Ticket处理（用于接收取消授权通通知、授权更新通知，也用于接收ticket，ticket是验证平台方的重要凭据。）
```
        注：verifyTicke微信服务器每隔10分机进行推送一次，请妥善保存verifyTicke
        

        $ticket = new Ticket($config);
        $verifyTicket = $ticket->verifyTicket();
        
        $verifyTicket格式：
        [
            "AppId" => "wx3c7ae2***",
            "CreateTime" => "1561347917",
            "InfoType" => "component_verify_ticket",
            "ComponentVerifyTicket" => "ticket@@@**RLZvrPHLD0AJq7oT_nqDrpa_xhO40sY08P77A",
        ]

```

#####2，通过Verify Ticket 获取或刷新Component Access Token
```
    注：component_access_token:是第三方平台的下文中接口的调用凭据，也叫做令牌（component_access_token）。
    每个令牌是存在有效期（2小时）的，且令牌的调用不是无限制的，请第三方平台做好令牌的管理，
    在令牌快过期时（比如1小时50分）再进行刷新
    
    $open = new Open($config);
    //设置请求参数
    $open->setRequestParams(['verifyTicket' => 'ticket@@@L_1***cTw8tumj**e4g']);
    $componentAccessToken = $open->componentAccessToken();
    
    $componentAccessToken格式：
    [
        "errcode" => 0, //errcode 不为0的时候说明接口请求出错了，详细看errmsg
        "expires_in" => 7200,
        "component_access_token" => "oOm6bXeEVzYJed0eOXlI",
    ]
    
```


#####3，获取Pre Auth Code预授权码
```
    注：pre_auth_code 预授权码用于公众号或小程序授权时的第三方平台方安全验证。
    
    $open = new Open($config);
    //设置请求参数
    $open->setRequestParams(['componentAccessToken' => 'ticket@@@L_1***cTw8tumj**e4g']);
    $preAuthCode = $open->preAuthCode();
    
    $preAuthCode格式：
    [
        "errcode" => 0, //errcode 不为0的时候说明接口请求出错了，详细看errmsg
        "expires_in" => 1800,
        "pre_auth_code" => "preauthcode@@@6Kf8G",
    ]
    
```

####二、第三方平台授权地址生成

```
    注：pre_auth_code 预授权码用于公众号或小程序授权时的第三方平台方安全验证。
    
    $open = new Open($config);
    //设置请求参数
    $open->setRequestParams(['preAuthCode' => 'preauthcode@@@L_1***cTw8tumj**e4g']);
    $preAuthCode = $open->createAuthUrl();
    
    $preAuthCode格式：
    [
        "errcode" => 0, //errcode 不为0的时候说明接口请求出错了，详细看errmsg
        "authUrl" => "https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=wx3c7a**&pre_auth_code=preauthcode-_WZYBLfHQZC8mrYCrhdlNSrNM_CX2BJP5F&auth_type=1&redirect_uri=%2Fopen%2Fauth%2Fredirect",
    ]
    
```

####三、授权公众号相关消息处理

#####1、获取公众号授权信息包括Access Token
```
        请求access token 的接口有调用次数限制，所以请妥善保管和处理好access token,全局处理
        注：请妥善保管access_token(有效期2小时)和refresh_access_token，当access_token将要过期的时候可以通过refresh_access_tokek
        刷新token,请提前几分钟刷新token
        
        $open = new Open($config);
        $open->setRequestParams([
            'componentAccessToken' => '22_dNcKsCIq_7irQx6RGiZg9ftwMh_o4cK8JGQaAFAAXH',
            'authCode' => 'RGiZg9ftwMh_o4cK8JGQaAFAAXH'
        ]);

        $authAccessToken = $open->authAccessToken();
        $authAccessToken返回结构：(参数格式等同于开发文档)
        [
            "errcode" => 0, //errcode 不为0的时候说明接口请求出错了，详细看errmsg
            "authorization_info" => [
                "authorizer_appid" => "appId",
                "authorizer_access_token" => "authorizer_access_token",
                "expires_in" => 7200,
                "authorizer_refresh_token" => "authorizer_refresh_token",
                "func_info" => [
                    ["funcscope_category=>["id":1]],
                    ["funcscope_category=>["id":2]],
                    ["funcscope_category=>["id":3]],
                ]
                
            ]
        ]
```

#####2、刷新公众号的Access Token
```
        注：请妥善保管access_token(有效期2小时)和refresh_access_token，当access_token将要过期的时候可以通过refresh_access_tokek
        刷新token,请提前几分钟刷新token
        
        $open = new Open($config);
        $open->setRequestParams([
            'componentAccessToken' => '22_dNcKsCIq_7irQx6RGiZg9ftwMh_o4cK8JGQaAFAAXH',
            'authAppId' => 'RGiZg9ftwMh_o4cK8JGQaAFAAXH',
            'refreshToken' => '22_dNcKsCIq_7irQx6RGiZg9ftwMh_o4cK8JGQaAFAAXH,
        ]);

        $authAccessToken = $open->authAccessToken();
        $authAccessToken返回结构：(参数格式等同于开发文档)
        [
            "errcode" => 0, //errcode 不为0的时候说明接口请求出错了，详细看errmsg
            "authorizer_access_token" => "authorizer_access_token",
            "expires_in" => 7200,
            "authorizer_refresh_token" => "authorizer_refresh_token",
        ]
```

#####3、获取授权公众号信息
```
        $open = new Open($config);
        $open->setRequestParams([
            'componentAccessToken' => '22_dNcKsCIq_7irQx6RGiZg9ftwMh_o4cK8JGQaAFAAXH',
            'authAppId' => 'RGiZg9ftwMh_o4cK8JGQaAFAAXH',
        ]);

        $authorizeInfo = $open->authorizeInfo();
        $authorizeInfo返回结构：(参数格式等同于开发文档)
        [
            "errcode" => 0, //errcode 不为0的时候说明接口请求出错了，详细看errmsg
            "authorizer_info" => [
                "nick_name"=> "微信SDK Demo Special", 
                "head_img"=> "http://wx.qlogo.cn/mmopen/GPy", 
                "service_type_info"=> [ "id"=> 2 ], 
                "verify_type_info"=> [ "id"=> 0 ],
                "user_name"=>"gh_eb5e3a772040",
                "principal_name"=>"腾讯计算机系统有限公司",
                "business_info"=> [
                    "open_store"=> 0, 
                    "open_scan"=> 0, 
                    "open_pay"=> 0, 
                    "open_card"=> 0, 
                    "open_shake"=> 0
                ],
                "alias"=>"paytest01"
                "qrcode_url"=>"URL",
            ]
            "authorization_info" => [
                "authorizer_appid" => "appId",
                "func_info" => [
                   ["funcscope_category=>["id":1]],
                   ["funcscope_category=>["id":2]],
                   ["funcscope_category=>["id":3]],
                ]
                
            ]
        ]
```
