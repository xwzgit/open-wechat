<?php
/**
 * Created by PhpStorm.
 * User: owner
 * Date: 2019-06-25
 * Time: 11:30
 * Project Name: openWeChat
 */

namespace Open\Support\Request;


use GuzzleHttp\Client;
use Open\Support\Log\Log;

class ApiRequest
{


    /**
     * 第三方平台POST清过
     *
     * @param $action
     * @param $url
     * @param $params
     * @return array|mixed
     */
    public static function postRequest($action,$url,$params)
    {
        try{
            $client = new Client();
            $response = $client->post($url,[
                'timeout' => 5,
                'json' => $params
            ]);
            $content = self::responseProcess($response);

        } catch (\Exception $exception) {
            $content = [
                'errcode' => 40000,
                'errmsg' => '['.$exception->getCode().']'.$exception->getMessage()
            ];
        }

        if($content['errcode'] == 0) {
            return $content;
        } else{
            throw new \Exception($content['errmsg'],$content['errcode']);
        }
        Log::error($action,$content);
        return false;
    }

    /**
     * 处理请求结果
     *
     * @param $response
     * @return array|mixed
     */
    public static function responseProcess($response)
    {
        $response = $response->getBody()->getContents();
        if($content = json_decode($response,true)) {
            if(!isset($content['errcode'])) {
                $content['errcode'] = 0;
            }
            return $content;
        } else {
            return [
                'errcode' => 40000,
                'errmsg' => '请求解析失败'
            ];
        }

    }
}