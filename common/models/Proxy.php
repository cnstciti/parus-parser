<?php
namespace common\models;

use GuzzleHttp\Client as HttpClient;
use Yii;

class Proxy
{
    /*
    public static function test() : string
    {
        $apiURL = 'http://proxy.my/test';

        try {
            $response = (new HttpClient())->get(
                $apiURL
            );
            $body = $response->getBody()->getContents();
        } catch (\Exception $e) {
            $body = 'Error';
        }

        return $body;
    }

    public static function load(string $type) : string
    {
        $apiURL = 'http://proxy.my/address/load-file';
        try {
            $response = (new HttpClient())->post(
                $apiURL,
                [
                    'multipart' => [
                        [
                            'name'     => 'type',
                            'contents' => $type,
                        ],
                        [
                            'name'     => 'uploadFile',
                            'contents' => fopen(Yii::getAlias('@proxy') . '/proxy-list.txt', 'r'),
                        ],
                    ],
                ]
            );
            $body = $response->getBody()->getContents();
        } catch (\Exception $e) {
            $body = $e->getMessage();
        }

        return $body;
    }
*/
    public static function readData(string $url) : string
    {
        $apiURL = Params::proxyApi() . '/read-data';
        $params['url'] = $url;
        try {
            $response = (new HttpClient())->post(
                $apiURL,
                [
                    'form_params' => $params
                ]
            );
            $body = $response->getBody()->getContents();
        } catch (\Exception $e) {
            $body = 'Error. Code=' . $e->getCode() . '(' . $e->getMessage() .')';
        }

        return $body;
    }

}
