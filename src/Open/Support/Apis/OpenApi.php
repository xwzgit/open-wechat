<?php
/**
 * 公众号第三方平台接口调用服务
 *
 */
namespace Open\Support\Apis;

use Open\Support\Config\Config;
use Open\Support\Log\Log;
use Open\Support\Request\ApiRequest;

class OpenApi
{
    protected $config;
    protected $params;
    protected $authUrl = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage';
    protected $comAccTk = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
    protected $repAccTk = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode';
    protected $authAccTk = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth';
    protected $refAuthAccTk = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token';
    protected $authInfo = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info';

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * 请求参数配置
     *
     * @param $params
     */
    public function setRequestParams($params)
    {
        $this->params = $params;
    }

    /**
     * 获取参数
     *
     * @param $action
     * @param $index
     * @return bool
     */
    protected function getRequestParams($action ,$index)
    {
        if(isset($this->params[$index]) && $this->params[$index]) {
            return $this->params[$index];
        }
        Log::error($action,['errcode' => 40000,'errmsg' => $index.' 参数不存在']);
        return false;
    }

    /**
     * 按需获取公共参数
     *
     * @param $params
     * @return array
     */
    public function globalParams($params)
    {
        $origin = [
            'component_appid' => $this->config->get('open.app_id'),
            'component_appsecret' => $this->config->get('open.app_secret'),
        ];

        return array_intersect_key($origin,$params);

    }

    /**
     * 刷新第三方平台Access Token
     *
     *
     * @param $ticket
     * @return array|bool|mixed
     */
    public function componentAccessToken()
    {
        if($ticket = $this->getRequestParams('componentAccessToken','verifyTicket')) {
            $params = $this->globalParams(['component_appid' => '','component_appsecret'=>'']);
            $params['component_verify_ticket'] = $ticket;

            return ApiRequest::postRequest('componentAccessToken',$this->comAccTk,$params);

        }
        return false;
    }


    /**
     * 获取预授权码
     *
     * @return array|bool|mixed
     */
    public function preAuthCode()
    {
        if($comAccTk = $this->getRequestParams('componentAccessToken','componentAccessToken')) {

            $params = $this->globalParams(['component_appid' => '']);
            $url = $this->repAccTk .'?component_access_token='.$comAccTk;

            return ApiRequest::postRequest('preAuthCode',$url,$params);
        }
        return false;
    }

    /**
     * 生成授权链接
     *
     * @param $preAuthCode
     * @param $redirectUrl
     * @return string
     */
    public function createAuthUrl()
    {
        $params = $this->globalParams(['component_appid' => '']);

        $params['pre_auth_code'] = $this->getRequestParams('createAuthUrl','preAuthCode');
        $params['auth_type'] = 1;
        $params['redirect_uri'] = $this->config->get('open.auth_redirect','');
        $query = http_build_query($params);
        if(!$this->getRequestParams('createAuthUrl','preAuthCode') ||
            !$this->config->get('open.auth_redirect','')) {
            return [
                'errcode' => 40000 ,
                'authUrl' => 'preAuthCode 和auth_redirect 都不能为空'
            ];
        }
        return [
            'errcode' => 0 ,
            'authUrl' => $this->authUrl.'?'.$query
        ];
    }


    /**
     * 获取公众号授权信息
     *
     * @return array|bool|mixed
     */
    public function authAccessToken()
    {
        if($comAccTk = $this->getRequestParams('authAccessToken','componentAccessToken')) {

            $params = $this->globalParams(['component_appid' => '']);
            $params['authorization_code'] = $this->getRequestParams('authAccessToken','authCode');
            $url = $this->authAccTk . '?component_access_token=' . $comAccTk;

            return ApiRequest::postRequest('authAccessToken', $url, $params);
        }

        return false;
    }

    /**
     * 刷新授权Access Token
     *
     * @return bool
     */
    public function refreshAuthAccessToken()
    {
        if($comAccTk = $this->getRequestParams('refreshAuthAccessToken','componentAccessToken')) {

            $params = $this->globalParams(['component_appid' => '']);
            $params['authorizer_appid'] = $this->getRequestParams('refreshAuthAccessToken','authAppId');
            $params['authorizer_refresh_token'] = $this->getRequestParams('refreshAuthAccessToken','refreshToken');
            $url = $this->refAuthAccTk.'?component_access_token=' . $comAccTk;;
            return ApiRequest::postRequest('refreshAuthAccessToken', $url, $params);
        }

        return false;
    }

    /**
     * 获取授权方的帐号基本信息
     * @return array|bool|mixed
     */
    public function authorizeInfo()
    {
        if($comAccTk = $this->getRequestParams('authorizeInfo','componentAccessToken')) {

            $params = $this->globalParams(['component_appid' => '']);
            $params['authorizer_appid'] = $this->getRequestParams('authorizeInfo','authAppId');
            $url = $this->authInfo.'?component_access_token=' . $comAccTk;;
            return ApiRequest::postRequest('authAccessToken', $url, $params);
        }

        return false;
    }
}