<?php
namespace common\models\data_level1;

use DiDom\Document;
use common\models\data_level1\exception\ActionParamException;
use common\models\data_level1\exception\BaseException;
use common\models\data_level1\exception\CheckException;
use common\models\data_level1\exception\CityParamException;
use common\models\data_level1\exception\DiDomException;
use common\models\data_level1\exception\ProxyException;
use common\models\data_level1\exception\TypeParamException;
use common\models\MainConst;
use common\models\Proxy;
use common\models\rep\DataLevel1Rep;
use common\models\rep\DataLevel1LogRep;
use common\models\rep\ParamRep;
use yii\helpers\Json;

/**
 * Базовый класс для парсера каталога сайта (разбор "ссылок на страницы")
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
abstract class DataLevel1Base
{
    const CODE_NO_ERROR = 0;
    const MSG_NO_ERROR = '';
    const CODE_ERROR_DATA = 1;
    const MSG_ERROR_DATA = 'Proxy. Данные не прочитаны.';
    const CODE_ERROR_NOT_RUN = 2;
    const MSG_ERROR_NOT_RUN = 'Парсер не запущен.';
    const CODE_ERROR_DIDOM = 3;
    const MSG_ERROR_DIDOM = 'Ошибка чтения DiDom\Document';
    const CODE_ERROR_TYPE = 4;
    const MSG_ERROR_TYPE = 'Неверное значение параметра "type"';
    const CODE_ERROR_CITY = 5;
    const MSG_ERROR_CITY = 'Неверное значение параметра "city"';
    const CODE_ERROR_ACTION = 6;
    const MSG_ERROR_ACTION = 'Неверное значение параметра "action"';


    /**
     * Возвращает параметры из БД
     *
     * @param array $params
     * @return array
     */
    abstract protected static function getParams(array $params) : array;

    /**
     * Возвращает форматированый URL каталога
     *
     * @param string $url
     * @param int $numPage
     * @return string
     */
    abstract protected static function getCatalogURL(string $url, int $numPage) : string;

    /**
     * Возвращает количество элементов на странице $document
     *
     * @param Document $document
     * @return int
     */
    //abstract protected static function getCountItems(Document $document) : int;

    /**
     * Возвращает элементы со страницы $document
     *
     * @param Document $document
     * @return array
     */
    abstract protected static function getItems(Document $document) : array;

    /**
     * Возвращает полный URL элемента страницы
     *
     * @param $item
     * @return string
     */
    abstract protected static function getItemURL($item) : string;

    /**
     * Возвращает максимальное количество элементов на странице каталога
     *
     * @return int
     */
    abstract protected static function getMaxNumPage() : int;

    /**
     * Возвращает массив соответствий между параметрами (en) и значениями в БД (rus)
     * (для города)
     *
     * @return array
     *  [
     *      {
     *          'en'  => <string>,
     *          'rus' => <string>,
     *      }
     *  ]
     */
    abstract public static function getRegions() : array;

    /**
     * Возвращает массив ссылок страницы каталога
     *
     * Вход:
     *  [
     *      'type'   => <string>,   // тип каталога ОН (flat | room | house | land)
     *      'city'   => <string>,   // населенный пункт (klim | vosk | msk | pod | sher | vid | znam)
     *      'action' => <string>,   // сделка каталога ОН (sell | rent)
     *      'site'   => <string>,   // сайт (avito | vsn)
     *  ]
     * @return array
     *  [
     *      'error' => [
     *          'code' => <int>,
     *          'description' => <string>
     *      ],
            'result' => [
                'param' => [
                    'type'   => <string>,
                    'city'   => <string>,
                    'action' => <string>,
                    'url'    => <string>,
                ],
                'data' => [
                    'url'    => <string>,
                    'type'   => <string>,
                    'action' => <string>,
                    'site'   => <string>,
                ],
            ],
        ]
     */
    final public static function parser(array $params, $isLog=false) : array
    {
        try {
            $convertParam = self::_convertParam($params);
            $paramDB      = static::getParams($convertParam);
            $numPage      = self::_getNumPage($paramDB['numPage']);
            $catalogURL   = static::getCatalogURL($paramDB['url'], $numPage);
            self::_checkRun($paramDB['run']);
            $data         = self::_getPage($catalogURL);
            $document     = self::_getDocument($data['result']['data']['content']);
            //$countItems   = static::getCountItems($document);
            $items        = static::getItems($document);
            if ($isLog) {
                $log[] = [
                    'url'    => $catalogURL,
                    'result' => 'type=' . $params['type'] . ', city=' . $params['city'] . ', action=' . $params['action'],
                ];
                DataLevel1LogRep::batchInsert($log);
            }
            $tmp = [];
            //if ($countItems && !empty($items)) {
            if (empty($items)) {
                // для уменьшения холостых проходов
                $numPage = 0;
            } else {
                $log = [];
                foreach ($items as $item) {
                    $tmp[] = [
                        'url'    => static::getItemURL($item),
                        'type'   => $convertParam['typeDB'],
                        'action' => $convertParam['actionDB'],
                        'site'   => $params['site'],
                    ];
                    if ($isLog) {
                        $log[] = [
                            'url'    => $catalogURL,
                            'result' => static::getItemURL($item),
                        ];
                    }
                }
                DataLevel1Rep::batchInsert($tmp);
                if ($isLog) {
                    DataLevel1LogRep::batchInsert($log);
                }
            }
            ParamRep::setNumPage($paramDB['id'], $numPage);

            $ret = self::_retNoError($catalogURL, $params, $convertParam, $tmp);
        } catch (BaseException | \Exception $e) {
            $ret = self::_retError($catalogURL, $params, $convertParam, $e);
        }

        return $ret;
    }

    /**
     * Возвращает результат в случае ОТСУТСТВИЯ ошибки
     *
     * @param string $catalogURL
     * @param array $params
     * @param array $convertParam
     * @param array $tmp
     * @return array
     */
    private static function _retNoError(string $catalogURL, array $params, array $convertParam, array $tmp) : array
    {
        return [
            'error'  => [
                'code'        => self::CODE_NO_ERROR,
                'description' => self::MSG_NO_ERROR
            ],
            'result' => [
                'param' => [
                    'url'    => $catalogURL,
                    'type'   => $params['type'] . ' (' . $convertParam['typeDB'] . ')',
                    'city'   => $params['city'] . ' (' . $convertParam['cityDB'] . ')',
                    'action' => $params['action'] . ' (' . $convertParam['actionDB'] . ')',
                    'site'   => $params['site'],
                ],
                'data'  => $tmp,
            ],
        ];
    }

    /**
     * Возвращает результат в случае ошибки
     *
     * @param string $catalogURL
     * @param array $params
     * @param array $convertParam
     * @param \Exception $e
     * @return array
     */
    private static function _retError(string $catalogURL, array $params, array $convertParam, \Exception $e) : array
    {
        $date = new \DateTime();
        return [
            'error'  => [
                'code'        => $e->getCode(),
                'description' => $e->getMessage()
            ],
            'result' => [
                'param' => [
                    'url'      => $catalogURL,
                    'type'     => $params['type'] . ' (' . $convertParam['typeDB'] . ')',
                    'city'     => $params['city'] . ' (' . $convertParam['cityDB'] . ')',
                    'action'   => $params['action'] . ' (' . $convertParam['actionDB'] . ')',
                    'site'     => $params['site'],
                    'dateTime' => $date->format('Y-m-d H:i:s'),
                ],
            ],
        ];
    }

    /**
     * Получаем Document по контексту страницы
     *
     * @param string $content
     * @return Document
     * @throws DiDomException
     */
    private static function _getDocument(string $content) : Document
    {
        try {
            $document = new Document($content);
        } catch (\Exception $e) {
            throw new DiDomException(self::MSG_ERROR_DIDOM, self::CODE_ERROR_DIDOM, $e);
        }

        return $document;
    }

    /**
     * Читаем данные страницы $url через Прокси
     *
     * @param string $url
     * @return array
     *  [
     *      'error' => [
     *          'code' => <int>,            // код ошибки. Если нет ошибки, то 0
     *          'description' => <string>   // описание ошибки
     *      ],
     *      'result' => [
     *          'data' => [                 // присутствует, если не было выброшено исключение
     *              'content' => <string>,  // HTML-код страницы (url - см.вход)
     *              'httpCode' => <int>     // HTTP код ответа на запрос страницы
     *          ],
     *      ],
     *  ]
     * @throws ProxyException
     */
    private static function _getPage(string $url) : array
    {
/*
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

        $data['result']['data']['content'] = $content;
        return $data;
*/
        $data = Json::decode(Proxy::readData($url));
        if ($data['error']['code']) {
            throw new ProxyException(self::MSG_ERROR_DATA, self::CODE_ERROR_DATA);
        }

        return $data;
    }

    /**
     * Проверяем, есть ли разрешение на запуск парсера
     *
     * @param int $upload
     * @throws CheckException
     */
    private static function _checkRun(int $run) : void
    {
        if (!$run) {
            throw new CheckException(self::MSG_ERROR_NOT_RUN, self::CODE_ERROR_NOT_RUN);
        }
    }

    /**
     * Конвертация входных параметров в формат БД
     *
     * @param array $params
     *  [
     *      'type'   => <string>,
     *      'city'   => <string>,
     *      'action' => <string>,
     *      'site'   => <string>,
     *  ]
     * @return array
        [
            'typeDB'   => <string>,
            'cityDB'   => <string>,
            'actionDB' => <string>,
        ]
     * @throws ActionParamException
     * @throws CityParamException
     * @throws TypeParamException
     */
    private static function _convertParam(array $params) : array
    {
        return [
            'typeDB'   => self::_typeDB($params['type']),
            'cityDB'   => self::_cityDB($params['city']),
            'actionDB' => self::_actionDB($params['action']),
        ];
    }

    /**
     * Конвертация типа ОН
     *
     * @param string $type
     * @return string
     * @throws TypeParamException
     */
    private static function _typeDB(string $type) : string
    {
        switch ($type) {
            case 'flat':  return MainConst::TYPE_FLAT;
            case 'room':  return MainConst::TYPE_ROOM;
            case 'house': return MainConst::TYPE_HOUSE;
            case 'land':  return MainConst::TYPE_LAND;
            default:      throw new TypeParamException(self::MSG_ERROR_TYPE, self::CODE_ERROR_TYPE);
        }
    }

    /**
     * Конвертация города/области ОН
     *
     * @param string $city
     * @return string
     * @throws CityParamException
     */
    private static function _cityDB(string $city) : string
    {
        $regions = static::getRegions();
        foreach ($regions as $region) {
            if ($city == $region['en']) {
                return $region['rus'];
            }
        }

        throw new CityParamException(self::MSG_ERROR_CITY, self::CODE_ERROR_CITY);
        /*
        switch ($city) {
            case 'klim':    return 'Климовск';
            case 'vosk':    return 'Воскресенское';
            case 'msk':     return 'Москва';
            case 'pod':     return 'Подольск';
            case 'sher':    return 'Щербинка';
            case 'vid':     return 'Видное';
            case 'znam':    return 'Знамя Октября';
            case 'dom':     return 'Домодедово';
            case 'komm':    return 'Коммунарка';
            case 'kon':     return 'Константиново';
            case 'lv':      return 'Львовский';
            case 'tr':      return 'Троицк';
            case 'shl':     return 'Шишкин лес';
            case 'vat':     return 'Ватутинки';
            case 'sector1': return 'Cектор1';
            case 'sector2': return 'Cектор2';
            case 'sector3': return 'Cектор3';
            case 'sector4': return 'Cектор4';
            case 'sector5': return 'Cектор5';
            case 'sector6': return 'Cектор6';
            case 'sector7': return 'Cектор7';
            case 'sector8': return 'Cектор8';
            default:        throw new CityParamException(self::MSG_ERROR_CITY, self::CODE_ERROR_CITY);
        }
        */
    }

    /**
     * Конвертация действия с ОН
     *
     * @param string $action
     * @return string
     * @throws ActionParamException
     */
    private static function _actionDB(string $action) : string
    {
        switch ($action) {
            case 'sell': return MainConst::ACTION_SELL;
            case 'rent': return MainConst::ACTION_RENT;
            default:     throw new ActionParamException(self::MSG_ERROR_ACTION, self::CODE_ERROR_ACTION);
        }
    }

    /**
     * Возвращает следующую страницу каталога или 1 (если начало)
     *
     * @param array $upload
     * @return int
     */
    private static function _getNumPage(int $numPage) : int
    {
        //$ret = ++$numPage;
        $maxNum = static::getMaxNumPage();
        /*
        $maxNum = 10;
        switch ($upload['site']) {
            case 'avito':
                $maxNum = 100;
                break;
            default:
                break;
        }
        */
        if (++$numPage >= $maxNum) {
            $numPage = 1;
        }

        return $numPage;
    }

}
