<?php
namespace backend\controllers;

use yii\web\Controller;
use common\models\data_level3\DataLevel3;

/**
 * DataLevel3 controller
 * Финализация данных
 */
class DataLevel3Controller extends Controller
{
    /**
     * Действие "Level3. Главная страница"
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Действие "Level3 - Обогащение данных"
     *
     * @return mixed
     */
    public function actionParser()
    {
        $data = DataLevel3::parser();

        return $this->render('parser', [
            'data' => $data,
        ]);
    }

    public function actionDelete()
    {
        $data = DataLevel3::delete(7);

        return $this->render('delete', [
            'data' => $data,
        ]);
    }
/*
    public function actionVsn()
    {
        $url = 'https://podolsk.vsn.ru/p-kuznechiki/for-rent-flat/one-room/45191939-40-0-m-etazh-13-14-27000-rub-ul-akademika-dollezhalya-14';
        $dataResult = DataResultRep::_findByUrl($url);
        $userAgent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $content = curl_exec($ch);
        curl_close($ch);
        $document = new Document($content);

        $data = DataLevel3Vsn::parser($document, $dataResult);

        return $this->render('data', [
            'data' => $data,
        ]);
    }
*/
}
