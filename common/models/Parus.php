<?php
namespace common\models;

use GuzzleHttp\Client as HttpClient;
use Yii;

class Parus
{
    public static function clientRentAddOn(int $id) : string
    {
        $apiURL = Params::parusApi() . '/client-rent/add-on';
        $params['id'] = $id;
        try {
            Yii::error('id='.$id, $apiURL);
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

    public static function clientRentDeleteOn(int $id) : string
    {
        $apiURL = Params::parusApi() . '/client-rent/delete-on';
        $params['id'] = $id;
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
