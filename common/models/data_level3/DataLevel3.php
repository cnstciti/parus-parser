<?php
namespace common\models\data_level3;

use common\models\Parus;
use common\models\rep\DataLevel2Rep;
use common\models\rep\DataLevel3Rep;
use common\models\rep\DelObjectRep;
use common\models\Proxy;
use common\models\data_level3\exception\BaseException;
use common\models\data_level1\exception\DiDomException;
use common\models\data_level3\exception\GeoException;
use common\models\data_level3\exception\ProxyException;
use DiDom\Document;
use yii\helpers\Json;
use common\models\Params;
use Yii;

/**
 *  Финализация данных
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel3
{
    const NO_ERROR = 0;
    const MSG_NO_ERROR = '';
    const CODE_ERROR_DATA = 1;
    const MSG_ERROR_DATA = 'Proxy. Данные не прочитаны.';
    const CODE_ERROR_DIDOM = 2;
    const MSG_ERROR_DIDOM = 'Ошибка чтения DiDom\Document.';
    const CODE_ERROR_RECORD_BLOCK = 3;
    const MSG_ERROR_RECORD_BLOCK = 'Запись заблокирована.';
    const CODE_ERROR_MAP_NOT_FOUND = 4;
    const MSG_ERROR_MAP_NOT_FOUND = 'Карты не найдено на странице.';


    /**
     * Возвращает массив ссылок страницы каталога
     *
     * @return array
     *  [
     *      'error' => [
     *          'code' => <int>,
     *          'description' => <string>
     *      ],
            'result' => [
                'data' => <string>
                'url' => <string>,
            ],
        ]
     *
     */
    public static function parser($isLog=false) : array
    {
        if (YII_ENV != 'dev') {
            // рандомная задержка в пределах минуты
            sleep(rand(0, 59));
        }

        $ret = [];
        try {
            $dataLevel2 = DataLevel2Rep::findByStatus(DataLevel2Rep::STATUS_LOADED);
            if (is_null($dataLevel2)) {
                // если нет записей для разбора
                throw new BaseException(self::MSG_ERROR_DATA, self::CODE_ERROR_DATA);
            }
            if ($dataLevel2['block']) {
                // если стоит блокировка записи
                throw new BaseException(self::MSG_ERROR_RECORD_BLOCK, self::CODE_ERROR_RECORD_BLOCK);
            }
            DataLevel2Rep::updateBlock($dataLevel2['id'], 1);
            $data       = self::_getPage($dataLevel2['url']);
            if ($data['error']['code']) {
                throw new ProxyException(self::MSG_ERROR_DATA, self::CODE_ERROR_DATA);
            }
            $document   = self::_getDocument($data['result']['data']['content']);
            switch ($dataLevel2['site']) {
                case 'avito': $ret = DataLevel3Avito::parser($document, $dataLevel2, $isLog); break;
                case 'vsn':   $ret = DataLevel3Vsn::parser($document, $dataLevel2, $isLog); break;
                default: break;
            }
        } catch (BaseException | \Exception $e) {
            $date = new \DateTime();
            $ret = [
                'error'  => [
                    'code'        => $e->getCode(),
                    'description' => $e->getMessage()
                ],
                'result' => [
                    'url'      => $dataLevel2['url'],
                    'dateTime' => $date->format('Y-m-d H:i:s'),
                ]
            ];
        }
        // снятие блокировки происходит всегда!

        // правильно ли это? снятие может быть в другом скрипте!
        // нужна блокировка на уровне записи

        DataLevel2Rep::updateBlock($dataLevel2['id'], 0);

        return $ret;
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
        $data['result']['data']['content'] = '

<!DOCTYPE html>














<html>
<head>
    <script nonce="mhoCYK+FKeM88AwX4bYjRw==">
        try {
            window.firstHiddenTime = document.visibilityState === \'hidden\' ? 0 : Infinity;

            document.addEventListener(\'visibilitychange\', function (event) {
                window.firstHiddenTime = Math.min(window.firstHiddenTime, event.timeStamp);
            }, { once: true });


            if (\'PerformanceLongTaskTiming\' in window) {
                var globalStats = window.__statsLongTasks = { tasks: [] };
                globalStats.observer = new PerformanceObserver(function(list) {
                    globalStats.tasks = globalStats.tasks.concat(list.getEntries());
                });
                globalStats.observer.observe({ entryTypes: [\'longtask\'] });
            }

            if (PerformanceObserver && (PerformanceObserver.supportedEntryTypes || []).some(function(e) {
                return e === \'element\'
            })) {
                if (!window.oet) {
                    window.oet = [];
                }

                new PerformanceObserver(function(l) {
                    window.oet.push.apply(window.oet, l.getEntries());
                }).observe({ entryTypes: [\'element\'] });
            }
        } catch (e) {
            console.error(e);
        }
    </script>

                        <script nonce="mhoCYK+FKeM88AwX4bYjRw==">
        window.dataLayer = [{"dynx_user":"a","dynx_region":"moskva","dynx_prodid":1768618038,"dynx_price":17000,"dynx_category":"komnaty","dynx_vertical":1,"dynx_pagetype":"item"},{"pageType":"Item","itemID":1768618038,"userAuth":1,"userId":90200356,"vertical":"RE","categoryId":23,"categorySlug":"komnaty","microCategoryId":3834,"locationId":637640,"isShop":0,"isClientType1":0,"itemPrice":17000,"withDelivery":1,"bezopasnyi_prosmotr":"Не хочу","deposit":"0","commission":"Собственник","offer_type":"Сдам","area":"15 м²","rooms":"3","floor":"2","floors_count":"5","house_type":"Кирпичный","lease_period":"На длительный срок","sellerId":138191850}];
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                \'gtm.start\': new Date().getTime(),
                event: \'gtm.js\'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != \'dataLayer\' ? \'&l=\' + l : \'\';
            j.async = true;
            j.src = \'//www.googletagmanager.com/gtm.js?id=\' + i + dl;
            var n=d.querySelector(\'[nonce]\');
            n&&j.setAttribute(\'nonce\',n.nonce||n.getAttribute(\'nonce\'));
            f.parentNode.insertBefore(j, f);
        })(window, document, \'script\', \'dataLayer\', \'GTM-KP9Q9H\');
    </script>

    
    <meta charset="utf-8">
            <meta name="format-detection" content="telephone=no">     <meta name="google-site-verification" content="7iEzRRMJ2_0p66pVS7wTYYvhZZSFBdzL5FVml4IKUS0" />

                                                                                             
                                                    <link rel="alternate" media="only screen and (max-width: 640px)" href="https://m.avito.ru/moskva/komnaty/komnata_15m_v_3-k._25et._1768618038">
                                        
        <title>Комната 15 м² в 3-к., 2/5 эт. в аренду в Москве | Снять комнату в Москве | Авито</title>

    <!--NOTE: если вносите изменения в этот файл, поддержите их в @avito/bx-ads -->
            <link rel="dns-prefetch" href="//yandex.ru/">
        <link rel="preload" nonce="mhoCYK+FKeM88AwX4bYjRw==" href="//yandex.ru/ads/system/context.js" as="script">
        <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="//yandex.ru/ads/system/context.js" async></script>
    
    
            <link rel="dns-prefetch" href="//securepubads.g.doubleclick.net/">
        <link rel="preload" nonce="mhoCYK+FKeM88AwX4bYjRw==" href="//securepubads.g.doubleclick.net/tag/js/gpt.js" as="script">
        <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="//securepubads.g.doubleclick.net/tag/js/gpt.js" async></script>
        <script nonce="mhoCYK+FKeM88AwX4bYjRw==">
            window.googletag = window.googletag || {cmd: []};
            googletag.cmd.push(function() {
                googletag.pubads().setForceSafeFrame(true);
            });
        </script>
    
    
    
    
    
    
    <meta name="yandex-verification" content="499bdc75d3636c55" /><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/8a020c93842cf56debcf.css"><script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/219e2c5a0e419ac6a088.js" ></script><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/ad26b420a4e32ec18e4e.css"><script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/7eb2c9c9d923f7f14feb.js" ></script><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/c26c09de9c626b64d45f.css"><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/d65909e0ffa699c486b6.css"><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/adacc0ad529147d2eee2.css"><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/12d40f81b7e415ffe00e.css"><script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/4a0853731d18f3b91799.js" ></script><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/7a1ee2a151af71ca5ee9.css"><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/c3298eb836d19670aaed.css"><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/1b1573ffcb206c47f037.css"><script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/5e8544f905a63fe6c7dc.js" ></script><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/fb24d8e578ad65e8301b.css"><script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/8e4d7d88dd7b6ac9bae3.js" ></script><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/921b40ea6b835a03a123.css"><script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/a6014e87d33ae585a68e.js" ></script><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/909a107b39faef2b89f1.css"><script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/473e84e4f62d7e45d5b5.js" ></script><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/68da3287e9017bc6b3b3.css"><script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/75850711581861d0050b.js" ></script><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/0e4338761429b4eb16ac.css"><script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/ffd3cd2f343c1ab50098.js" ></script><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/874d9627bb440fb67b69.css"><script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/23a3fc4421187e08c3ef.js" ></script><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/9f79063bd9b93af23d78.css"><script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/de93d5e395a3e0cb7121.js" ></script><link rel="stylesheet" href="https://static.avito.ru/@avito/au-discount/1.0.0/prod/web/styles/6ce2928fb77334bfa97c.css"><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/919a2e4c1da96b20b3fe.css"><script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/e38dd3d4c3e25d9884e2.js" ></script><link rel="apple-touch-icon-precomposed" sizes="180x180" href="https://www.avito.st/s/common/touch-icons/common/apple-touch-icon-180x180-precomposed.png?57be3fb" /><link rel="apple-touch-icon-precomposed" sizes="152x152" href="https://www.avito.st/s/common/touch-icons/common/apple-touch-icon-152x152-precomposed.png?cac4f2a" /><link rel="apple-touch-icon-precomposed" sizes="144x144" href="https://www.avito.st/s/common/touch-icons/common/apple-touch-icon-144x144-precomposed.png?9615e61" /><link rel="apple-touch-icon-precomposed" sizes="120x120" href="https://www.avito.st/s/common/touch-icons/common/apple-touch-icon-120x120-precomposed.png?2a32f09" /><link rel="apple-touch-icon-precomposed" sizes="114x114" href="https://www.avito.st/s/common/touch-icons/common/apple-touch-icon-114x114-precomposed.png?174e153" /><link rel="apple-touch-icon-precomposed" sizes="76x76" href="https://www.avito.st/s/common/touch-icons/common/apple-touch-icon-76x76-precomposed.png?28e6cfb" /><link rel="apple-touch-icon-precomposed" sizes="72x72" href="https://www.avito.st/s/common/touch-icons/common/apple-touch-icon-72x72-precomposed.png?aeb90b3" /><link rel="apple-touch-icon-precomposed" sizes="57x57" href="https://www.avito.st/s/common/touch-icons/common/apple-touch-icon-57x57-precomposed.png?fd7ac94" /><meta name="msapplication-TileColor" content="#000000"><meta name="msapplication-TileImage" content="/s/common/touch-icons/common/mstile-144x144.png"><meta name="msapplication-config" content="browserconfig.xml" /><link href="https://www.avito.st/favicon.ico?9de48a5" rel="shortcut icon" type="image/x-icon" /><link href="https://www.avito.st/ya-tableau-manifest-ru.json?5ac8b8a" rel="yandex-tableau-widget" /><link href="https://www.avito.st/open_search_ru.xml?4b0fd3d" rel="search" type="application/opensearchdescription+xml" title="Авито" /><meta property="og:description" content="Комната 15 м² в 3-к., 2/5 эт. сдаётся в Москве. Объявление на Авито. Сдаю комнату в 3х комнатной квартире комната с балконом. Девушкам или женщинам!! Без залога!!! Читайте внимательно!!! На длительный срок!!!! Тихо, спокойно. Квартира чистая, уютная, большая, вся необходимая мебель и техника имеется, также в большой комнате балкон, застеклен. Проживание без хозяев. Порядочным и платежеспособным только девушкам и женщинам!!! Без вредных привычек!!!! Уважающих и понимающих чужой труд!!! Любящих чистоту и порядок!!! Уважающих личное пространство. Комната светлая , в квартире имеется всё для прожи..." /><meta name="description" content="Комната 15 м² в 3-к., 2/5 эт. сдаётся в Москве. Объявление на Авито. Сдаю комнату в 3х комнатной квартире комната с балконом. Девушкам или женщинам!! Без залога!!! Читайте внимательно!!! На длительный срок!!!! Тихо, спокойно. Квартира чистая, уютная, большая, вся необходимая мебель и техника имеется, также в большой комнате балкон, застеклен. Проживание без хозяев. Порядочным и платежеспособным только девушкам и женщинам!!! Без вредных привычек!!!! Уважающих и понимающих чужой труд!!! Любящих чистоту и порядок!!! Уважающих личное пространство. Комната светлая , в квартире имеется всё для прожи..." /><meta name="mrc__share_title" content="Комната 15 м² в 3-к., 2/5 эт. в аренду в Москве | Снять комнату в Москве | Авито" /><meta name="mrc__share_description" content="Комната 15 м² в 3-к., 2/5 эт. сдаётся в Москве. Объявление на Авито. Сдаю комнату в 3х комнатной квартире комната с балконом. Девушкам или женщинам!! Без залога!!! Читайте внимательно!!! На длительный срок!!!! Тихо, спокойно. Квартира чистая, уютная, большая, вся необходимая мебель и техника имеется, также в большой комнате балкон, застеклен. Проживание без хозяев. Порядочным и платежеспособным только девушкам и женщинам!!! Без вредных привычек!!!! Уважающих и понимающих чужой труд!!! Любящих чистоту и порядок!!! Уважающих личное пространство. Комната светлая , в квартире имеется всё для прожи..." /><link rel="image_src" href="https://www.avito.ru/img/share/auto/12175596009" /><meta property="og:title" content="Комната 15 м² в 3-к., 2/5 эт. в аренду в Москве | Снять комнату в Москве | Авито" /><meta property="og:type" content="website" /><meta property="og:url" content="https://www.avito.ru/moskva/komnaty/komnata_15m_v_3-k._25et._1768618038" /><meta property="og:site_name" content="Авито" /><meta property="og:locale" content="ru_RU" /><meta property="fb:app_id" content="472292516308756" /><meta property="product:price:amount" content="17000" /><meta property="product:price:currency" content="RUB"/><meta property="og:image" content="https://www.avito.ru/img/share/auto/12175596009" /><meta property="og:image:alt" content="Комната 15 м² в 3-к., 2/5 эт." /><meta property="og:image" content="https://www.avito.ru/img/share/auto/12175596030" /><meta property="og:image:alt" content="Комната 15 м² в 3-к., 2/5 эт." /><meta property="og:image" content="https://www.avito.ru/img/share/auto/12175595997" /><meta property="og:image:alt" content="Комната 15 м² в 3-к., 2/5 эт." /><meta property="og:image" content="https://www.avito.ru/img/share/auto/12175596026" /><meta property="og:image:alt" content="Комната 15 м² в 3-к., 2/5 эт." /><meta property="og:image" content="https://www.avito.ru/img/share/auto/9830843082" /><meta property="og:image:alt" content="Комната 15 м² в 3-к., 2/5 эт." /><meta property="og:image" content="https://www.avito.ru/img/share/auto/9830843088" /><meta property="og:image:alt" content="Комната 15 м² в 3-к., 2/5 эт." /><meta property="og:image" content="https://www.avito.ru/img/share/auto/9830843089" /><meta property="og:image:alt" content="Комната 15 м² в 3-к., 2/5 эт." /><meta property="og:image" content="https://www.avito.ru/img/share/auto/9798177448" /><meta property="og:image:alt" content="Комната 15 м² в 3-к., 2/5 эт." /><meta property="og:image" content="https://www.avito.ru/img/share/auto/9798177447" /><meta property="og:image:alt" content="Комната 15 м² в 3-к., 2/5 эт." /><meta property="og:image" content="https://www.avito.ru/img/share/auto/12175596009" /><meta property="og:image:alt" content="Комната 15 м² в 3-к., 2/5 эт." /><link rel="canonical" href="https://www.avito.ru/moskva/komnaty/komnata_15m_v_3-k._25et._1768618038" /><link rel="alternate" href="android-app&#x3A;&#x2F;&#x2F;com.avito.android&#x2F;ru.avito&#x2F;1&#x2F;items&#x2F;1768618038" /><link rel="alternate" href="ios-app&#x3A;&#x2F;&#x2F;417281773&#x2F;ru.avito&#x2F;1&#x2F;items&#x2F;1768618038" />
            <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~df485e34331ffe15da321cbe18cad04a.b877537549316d8be519.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/default~d446c9efe1935fbf69cc24bbe2635c3b~df485e34331ffe15da321cbe18cad04a.cdfc139f3fd15a40840e.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/2d2a779fe37ef913d898.js" ></script>

        <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~e0704b583294c2ecf03d33677df35305.3dc7ed4d69fb327d5cde.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/2e4d19cb50a760334877.js" ></script>
    
                <script nonce="mhoCYK+FKeM88AwX4bYjRw==">
            window.capturedErrors = [];

            window.addEventListener(\'error\', function(error) {
                window.capturedErrors.push(error);
            });
        </script>

        <script nonce="mhoCYK+FKeM88AwX4bYjRw==">
            window.avito = window.avito || {};
            window.avito.platform = \'desktop\';
            window.avito.siteName = \'Авито\';
            window.avito.staticPrefix = \'https://www.avito.st\';
            window.avito.supportPrefix = \'https://support.avito.ru\';

            window.avito.pageId = \'item\';
            window.avito.categoryId = 23;
            window.avito.microCategoryId = 3834;
            window.avito.locationId = null;

            window.avito.routeName = \'item\';

            window.avito.fromPage = \'item\';

            window.avito.sentry = {
                dsn: \'https://1ede3b886c8b4efd9230509682fe2f12@sntr.avito.ru/41\',
                release: "rc-202201261101-164608"
            };
            window.avito.clickstream = {
                buildId: "rc-202201261101-164608",
                buildUid: null,
                srcId: 96
            };
            window.avito.isAuthenticated = \'1\' === \'1\';
            window.avito.metrics = {};
            window.avito.metrics.categoryId = 23;
            window.avito.metrics.browser = \'chrome.97\';
            window.avito.filtersGroup = \'desktop_catalog_filters\';
            window.avito.experiments = window.avito.experiments || {};
            window.avito.abFeatures = window.avito.abFeatures || {};

            // Messenger config section
            window.avito.socketserver = \'wss://socket.avito.ru/socket\';
            window.avito.httpfallback = \'https://socket.avito.ru/fallback\';
            window.avito.socketImageUploadUrl = \'https://socket.avito.ru/images\';
            window.avito.socketMetricsUrl = \'https://socket.avito.ru/metrics\';
            window.avito.fileStorageUploadUrl= \'https://files.avito.ru/upload/\';
            window.avito.isTestAccount = \'\' === \'1\';
            window.avito.userId = \'90200356\';
            window.avito.hashedUserId = \'ac7e05d6d9648d72d8d17cdf9951527f\';
            
            window.avito.messenger = window.avito.messenger || {};
            window.avito.messenger.experiments = window.avito.messenger.experiments || {};

            window.avito.messenger.experiments.id_versions_test = \'\';

            window.avito.isVerificationTest = \'\' === \'1\';
        </script>

                <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="/s/a/j/dfp/px.js?ch=1"></script>
        <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="/s/a/j/dfp/px.js?ch=2"></script>

                                        
        <script nonce="mhoCYK+FKeM88AwX4bYjRw==">
            window.avito.ads = {
                userGroup: 15
            };
        </script>

                    <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~3d7e85d2c8b51bd652a72c38a1bfd251.a4b7fca3539a4ce65d65.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/543f467044015754bd11.js" ></script>
        
        <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/2baae20aa3782897a22a.js" ></script>

                    <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/4d5c41ee3cdbd5e9d257.js" ></script>
        
                    <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/7d16c5dda6928b0ac23c.js" ></script>
        
                    <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/d3d49725c9ad487c9ed2.js" ></script>
        
        <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/edb75ec2410aa9acf486.js" ></script>

        <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~1bfe38ef2b168dbbd1023a349ed74c5a~c27e601a48ab334d5402ca130b5019f1~d19a39f8e3a030445096479f5fdda4e9.efe0c5724d17683a4efb.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/98f59b7f7b7d4389f78f.js" ></script>

            

    <script nonce="mhoCYK+FKeM88AwX4bYjRw==">
        var avito = avito || {};
        var isMyItem = 0;

        if (isMyItem) {
            avito.pageId = \'profile-item\';
        }

        avito.item = avito.item || {};
        avito.abFeatures = avito.abFeatures || {};
        avito.item.url = \'/moskva/komnaty/komnata_15m_v_3-k._25et._1768618038\';
        avito.item.id = \'1768618038\';
        avito.item.rootCategoryId = 4;
        avito.item.price = \'17000\';
        avito.item.countryHost = \'www.avito.ru\';
        avito.item.siteName = \'Авито\';
        avito.item.isRealty = 1;
        avito.item.isMyItem = isMyItem;
        avito.item.hasCvPackage = 0;
        avito.item.hasEmployeeBalanceForCv = 0
        avito.item.tokenName = \'token[8994913488711]\';
        avito.item.tokenValue = \'1643220804.2400.8e694df294c8b2ce6aa869f64e46994d734f77a7e2e7f1cdc4828ddfa9f7ea8e\';
        avito.item.searchHash = \'\';
        avito.item.userHashId = \'138191850\';
        avito.item.image = \'https://09.img.avito.st/image/1/1.NOwmZraymAUwxiIFQiJc3MrFmAOGx5o.HlXr62X_7pu7VPxSLG-fPKoBT6bP_uX4ipa-4ejrgNM\';
        avito.item.location = \'\u041C\u043E\u0441\u043A\u0432\u0430\';
        avito.item.title = \'\u041A\u043E\u043C\u043D\u0430\u0442\u0430\u002015\u00A0\u043C\u00B2\u0020\u0432\u00203\u002D\u043A.,\u00202\/5\u00A0\u044D\u0442.\';
        avito.item.priceFormatted = \'17 000&nbsp;₽ в месяц\';
        avito.item.vin = \'\';
        avito.item.locationId = 637640;
        avito.item.categoryId = 23;
        avito.item.comparisonId = 0;
        avito.item.comparisonModelId = 0;
        avito.item.comparisonVendorId = 0;

        avito.item.gaEcommerceParams = {"item_id":"1768618038","item_name":"Комната 15 м² в 3-к., 2\/5 эт.","price":"17000","category":"komnaty","vertical":"RE","microCategoryId":"3834","withDelivery":"0"};
                
                                                                        avito.isAuthenticated = true;                
                                        
         avito.isAuthorized = 1;          avito.item.imageAspectRatio = \'4:3\';                             </script>

    
            
    <link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/3c0aeabd8a6dcfa207a7.css">
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/c4cbff75549391a06cfa.js" ></script>

    <script nonce="mhoCYK+FKeM88AwX4bYjRw==">
        window.avito = window.avito || {};
        window.avito.banners =  {"brandspace_button_contacts":[{"sellingSystem":"DFP","parameters":{"bannerCode":"brandspace_button_contacts","alid":"127db00b2c352c82b5781f596a5a2ef6","locationId":"637640","categoryId":"23","microCategoryId":"3834","sizes":[[309,46],[300,46],[1,1]],"dfp_slot":"\/7870\/AR\/nedvizhimost\/komnaty\/Item\/brandspace_button_contacts","count":1,"targetings":{"isrmp":"1","page_type":"item","bp":"brandspace_button_contacts","abp":"1","cat_interests":["h23","h24","n26","n87"],"params_interests":["1059"],"g_metro":["107"],"item_property":["5306","5342","5407","6203","16284","438944","446874","446873","1055","16178","16273","5439"],"socd":[],"g_city":"637640","g_country":"621540","g_reg":"637640","g_domofond_reg":"moskva-c3584","master_category":"4","slave_category":"23","body_type":"","company_id":"0","ip":"62.176.8.58","phcodedesc":"15","price":"15000-19999","stcompany":"1","stlogin":"1","has_shop":"0","par_title":"Комната 15 м² в 3-к., 2\/5 эт.","par_price":"17000","par_picture":"https:\/\/09.img.avito.st\/image\/1\/1.NOwmZraymAUQz1oAQiJc3MrFmAOGx5o.VpWY8HV0YI9KJAG1ePDx-gSuXymD4F_WNbCS-w_1NKc","pmin":null,"pmax":null,"keyword":"","Uname_Get4C":"","AudRandom":"15"}}}],"brandspace_button_info":[{"sellingSystem":"DFP","parameters":{"bannerCode":"brandspace_button_info","alid":"d7bbb59b3fc57bf96f8a93be2467e7bd","locationId":"637640","categoryId":"23","microCategoryId":"3834","sizes":[[374,60],[1,1]],"dfp_slot":"\/7870\/AR\/nedvizhimost\/komnaty\/Item\/brandspace_button_info","count":1,"targetings":{"isrmp":"1","page_type":"item","bp":"brandspace_button_info","abp":"1","cat_interests":["h23","h24","n26","n87"],"params_interests":["1059"],"g_metro":["107"],"item_property":["5306","5342","5407","6203","16284","438944","446874","446873","1055","16178","16273","5439"],"socd":[],"g_city":"637640","g_country":"621540","g_reg":"637640","g_domofond_reg":"moskva-c3584","master_category":"4","slave_category":"23","body_type":"","company_id":"0","ip":"62.176.8.58","phcodedesc":"15","price":"15000-19999","stcompany":"1","stlogin":"1","has_shop":"0","par_title":"Комната 15 м² в 3-к., 2\/5 эт.","par_price":"17000","par_picture":"https:\/\/09.img.avito.st\/image\/1\/1.NOwmZraymAUQz1oAQiJc3MrFmAOGx5o.VpWY8HV0YI9KJAG1ePDx-gSuXymD4F_WNbCS-w_1NKc","pmin":null,"pmax":null,"keyword":"","Uname_Get4C":"","AudRandom":"15"}}}],"brandspace_button_title":[{"sellingSystem":"DFP","parameters":{"bannerCode":"brandspace_button_title","alid":"9dcf04b03745c96b1f93029c4a7227a4","locationId":"637640","categoryId":"23","microCategoryId":"3834","sizes":[[300,20],[1,1]],"dfp_slot":"\/7870\/AR\/nedvizhimost\/komnaty\/Item\/brandspace_button_title","count":1,"targetings":{"isrmp":"1","page_type":"item","bp":"brandspace_button_title","abp":"1","cat_interests":["h23","h24","n26","n87"],"params_interests":["1059"],"g_metro":["107"],"item_property":["5306","5342","5407","6203","16284","438944","446874","446873","1055","16178","16273","5439"],"socd":[],"g_city":"637640","g_country":"621540","g_reg":"637640","g_domofond_reg":"moskva-c3584","master_category":"4","slave_category":"23","body_type":"","company_id":"0","ip":"62.176.8.58","phcodedesc":"15","price":"15000-19999","stcompany":"1","stlogin":"1","has_shop":"0","par_title":"Комната 15 м² в 3-к., 2\/5 эт.","par_price":"17000","par_picture":"https:\/\/09.img.avito.st\/image\/1\/1.NOwmZraymAUQz1oAQiJc3MrFmAOGx5o.VpWY8HV0YI9KJAG1ePDx-gSuXymD4F_WNbCS-w_1NKc","pmin":null,"pmax":null,"keyword":"","Uname_Get4C":"","AudRandom":"15"}}}],"btni":[{"sellingSystem":"DFP","parameters":{"bannerCode":"btni","alid":"8b5e83b7a336692ead6476b406d81d81","locationId":"637640","categoryId":"23","microCategoryId":"3834","sizes":[[263,23],[263,25],[288,30],[287,30],[342,30],[264,30],[342,40],[309,25]],"dfp_slot":"\/7870\/AR\/nedvizhimost\/komnaty\/Item\/btni","count":1,"targetings":{"isrmp":"1","page_type":"item","bp":"btni","abp":"1","cat_interests":["h23","h24","n26","n87"],"params_interests":["1059"],"g_metro":["107"],"item_property":["5306","5342","5407","6203","16284","438944","446874","446873","1055","16178","16273","5439"],"socd":[],"g_city":"637640","g_country":"621540","g_reg":"637640","g_domofond_reg":"moskva-c3584","master_category":"4","slave_category":"23","body_type":"","company_id":"0","ip":"62.176.8.58","phcodedesc":"15","price":"15000-19999","stcompany":"1","stlogin":"1","has_shop":"0","par_title":"Комната 15 м² в 3-к., 2\/5 эт.","par_price":"17000","par_picture":"https:\/\/09.img.avito.st\/image\/1\/1.NOwmZraymAUQz1oAQiJc3MrFmAOGx5o.VpWY8HV0YI9KJAG1ePDx-gSuXymD4F_WNbCS-w_1NKc","pmin":null,"pmax":null,"keyword":"","Uname_Get4C":"","AudRandom":"15"}}}],"ldr_low":[{"sellingSystem":"Yandex RTB","parameters":{"bannerCode":"ldr_low","alid":"1148b241259405a8c7867eb33f69e8de","locationId":"637640","categoryId":"23","microCategoryId":"3834","location":{"lat":55.783195,"lng":37.719423},"block_id":"R-189903-121","stat_id":"100002314"}}],"ldr_top":[{"sellingSystem":"Yandex RTB","parameters":{"bannerCode":"ldr_top","alid":"952395f3d9d9cde3dbdbaacbd0bfcd38","locationId":"637640","categoryId":"23","microCategoryId":"3834","location":{"lat":55.783195,"lng":37.719423},"block_id":"R-189903-120","stat_id":"100002314"}}]} || null;
        window.avito.rmp = window.avito.rmp || {};
        window.avito.rmp.enabledBanners = {"brandspace_button_contacts":{"code":"brandspace_button_contacts"},"brandspace_button_info":{"code":"brandspace_button_info"},"brandspace_button_title":{"code":"brandspace_button_title"},"btni":{"code":"btni"},"ldr_low":{"code":"ldr_low"},"ldr_top":{"code":"ldr_top"}};
        window.avito.rmp.enableEventSampling = false;
        window.avito.rmp.newYandexSearchBanner = null;
        window.avito.rmp.nonce = document.currentScript.nonce;
        window.avito.rmp.abBrandspaceItem = null;
    </script>

            <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~298c5fea921f3cd193d606987723eb40.66e95ff88f7ff4d99725.js" async></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/972951bbbfc7dc6c07f9.js" async></script>
    
            <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/bb665cb51fd580a101cd.js" async></script>
    
    <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/7ae2ef0af10807401b92.js" ></script>
    </head>

<body class="windows windows chrome chrome-chrome "  >
                        <noscript>
        <iframe src=//www.googletagmanager.com/ns.html?id=GTM-KP9Q9H height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
        
    
                

    
                    

<div class="js-header-container header-container header-responsive   header-container_no-bottom-margin">
                                                    <div class=\'js-header\' data-state=\'{"responsive":true,"alternativeCategoryMenu":null,"addButtonText":"Разместить объявление","servicesClassName":"header-services","logoData":[{"title":"об автомобилях"},{"title":"об недвижимости"},{"title":"об работе"},{"title":"об услугах"}],"inTNS8114test":true,"currentPage":"item","country":{"host":"www.avito.ru","country_slug":"rossiya","site_name":"Авито","currency_sign":"₽"},"headerInfo":{"unreadFavoritesCount":0,"rating":{"summary":"Нет отзывов","score":0,"scoreFloat":0,"activeReviewsCount":0,"useStarsReviewCount":null,"hideStars":true},"showExtendedProfileLink":false,"showBasicProfileLink":true},"luri":"moskva","menu":{"business":{"title":"Для бизнеса","link":"business","absoluteLink":null},"shops":{"title":"Магазины","link":"shops","absoluteLink":null},"support":{"title":"Помощь","link":null,"absoluteLink":"support.avito.ru"}},"messenger":{"unreadChatCount":0,"socketFallbackUrl":"https:\/\/socket.avito.ru\/fallback"},"isNCEnabled":true,"isShowAvitoPro":true,"user":{"isAuthenticated":true,"id":90200356,"name":"Пользователь","hasShopSubscription":false,"hasTariffOnBudget":false,"isLegalPerson":false,"avatar":"https:\/\/www.avito.st\/stub_avatars\/%D0%9F\/5_256x256.png"},"user_location_id":637640,"userAccount":{"balance":{"bonus":"","real":"0","total":"0"},"isSeparationBalance":false},"hierarchy":{"isEmployee":false,"companyName":null},"now":1643220804,"_dashboard":{},"nonce":"mhoCYK+FKeM88AwX4bYjRw=="}\'><div
    class="header-root-1FCTt header-services header-responsive-yeqX8"
    data-marker="header/navbar"><div class="header-inner-3iFNe header-clearfix-kI6fL"><ul class="header-list-IUZFq header-nav-wQVeb header-clearfix-kI6fL"><li class="header-nav-item-1OJG-"><a
                            class="header-link-TLsAU header-nav-link-126h3"
                            href="/business"
                                                        
                            >Для бизнеса</a></li><li class="header-nav-item-1OJG-"><a
                            class="header-link-TLsAU header-nav-link-126h3"
                            href="/shops/moskva"
                                                        
                            >Магазины</a></li><li class="header-nav-item-1OJG-"><a
                            class="header-link-TLsAU header-nav-link-126h3"
                            href="//support.avito.ru"
                                                        target="_blank" rel="noopener noreferrer"
                            >Помощь</a></li></ul><div class="header-services-menu-2tz5y"><div class="header-services-menu-item-3H7kQ" data-marker="header/favorites"><a class="header-services-menu-link-fsJlE"
                        href="/favorites"
                        title="Избранное"
                       ><span class="header-services-menu-icon-wrap-STcWG"><span class="header-services-menu-icon-PXhUE"><svg width="21" height="24" xmlns="http://www.w3.org/2000/svg"><path d="M10.918 5.085a5.256 5.256 0 0 1 7.524 0c2.077 2.114 2.077 5.541 0 7.655l-7.405 7.534a.75.75 0 0 1-1.074 0L2.558 12.74c-2.077-2.114-2.077-5.54 0-7.655a5.256 5.256 0 0 1 7.524 0c.15.152.289.312.418.479.13-.167.269-.327.418-.479z" fill="#CCC" fill-rule="nonzero"/></svg></span><i class="header-icon-count-2EGgu header-icon-count_red-3f61L header-icon-count_hidden-3av6Y"></i></span></a></div><div class="header-services-menu-item-3H7kQ"><a class="header-services-menu-link-fsJlE"
                                href="/profile/notifications"
                                title="Уведомления"><span class="header-services-menu-icon-wrap-STcWG"><span class="header-services-menu-icon-PXhUE"><svg width="21" height="24" xmlns="http://www.w3.org/2000/svg"><g fill="#CDCDCD" fill-rule="evenodd"><path d="M1.816 17.744L4 14V9a6.5 6.5 0 1 1 13 0v5l2.184 3.744A1.5 1.5 0 0 1 17.888 20H3.112a1.5 1.5 0 0 1-1.296-2.256z"/><circle cx="10.5" cy="20" r="2.5"/></g></svg></span><i class="header-icon-count-2EGgu header-icon-count_red-3f61L header-icon-count_hidden-3av6Y"></i></span></a></div><div class="header-services-menu-item-3H7kQ"><a class="header-services-menu-link-fsJlE js-site-header-messenger-icon"
                            href="/profile/messenger"
                            title="Сообщения"
                           ><span class="header-services-menu-icon-wrap-STcWG"><span class="header-services-menu-icon-PXhUE"><svg width="24" height="24" xmlns="http://www.w3.org/2000/svg"><g fill="#CCC" fill-rule="evenodd"><path d="M8.827 3.28A19.839 19.839 0 0 1 12 3c.97 0 2.027.093 3.173.28A8.135 8.135 0 0 1 22 11.309c0 3.804-2.84 7.01-6.617 7.468A28.26 28.26 0 0 1 12 19c-1.028 0-2.156-.074-3.383-.223A7.523 7.523 0 0 1 2 11.309 8.135 8.135 0 0 1 8.827 3.28z"/><path d="M4.5 15l-1.773 4.963a.8.8 0 0 0 1.15.964L9 18l-4.5-3z"/></g></svg></span><i class="header-icon-count-2EGgu header-icon-count_red-3f61L header-icon-count_hidden-3av6Y"></i></span></a></div><div class="header-services-menu-item-3H7kQ header-services-menu-item_profile-31Mes"><a class="header-services-menu-link-fsJlE"
                        href="/profile"
                       >Мои объявления</a></div><div class="header-services-menu-item_username-32omV header-services-menu-item_avatar-2ZKhs"><div class="header-services-menu-dropdown-11aq2"
                       ><a href="/profile"
                            class="header-services-menu-link-fsJlE ym-hide-content"
                            data-marker="header/username-button"><div class="header-services-menu-avatar-RNUau"><img src="https://www.avito.st/stub_avatars/%D0%9F/5_256x256.png"
                                        class="header-services-menu-avatar-image-PhSzP"
                                        alt="Аватар"></div><span>Пользователь</span></a><div class="header-services-menu-dropdown-popup-Cuy78
                            header-services-menu-dropdown-popup_left-3Jdq8"
                                data-marker="header/username-tooltip"><ul class="header-list-IUZFq header-profile-nav-3Uden"
                                data-marker="header/tooltip-list"><div class="header-profile-nav-group-3M1xl"><li class="header-profile-nav-item-1FXQp"></li></div><div class="header-profile-nav-group-3M1xl"><li class="header-profile-nav-item-1FXQp"
                                        data-item-id="6"
                                        data-marker="header/tooltip-list-item"><a class="header-link-TLsAU header-profile-nav-link-3ZWkp"
                                            href="/profile">Мои объявления</a></li><li class="header-profile-nav-item-1FXQp"
                                        data-item-id="18"
                                        data-marker="header/tooltip-list-item"><a class="header-link-TLsAU header-profile-nav-link-3ZWkp"
                                            href="/orders/sales?source=shortcut">Заказы</a></li><li class="header-profile-nav-item-1FXQp"
                                        data-item-id="1"><a class="header-link-TLsAU header-profile-nav-link-3ZWkp"
                                            href="/profile/contacts">Мои отзывы</a></li><li class="header-profile-nav-item-1FXQp"
                                        data-item-id="4"
                                        data-marker="header/tooltip-list-item"><a class="header-link-TLsAU header-profile-nav-link-3ZWkp"
                                            href="/favorites"
                                            data-marker="header/username-tooltip/favorites">Избранное</a></li></div><div class="header-profile-nav-group-3M1xl"><li class="header-profile-nav-item-1FXQp"
                                        data-item-id="2"
                                        data-marker="header/tooltip-list-item"><a class="header-link-TLsAU header-profile-nav-link-3ZWkp"
                                            href="/profile/messenger"
                                           >Сообщения</a></li><li class="header-profile-nav-item-1FXQp"
                                            data-item-id="3"
                                            data-marker="header/tooltip-list-item"><a class="header-link-TLsAU header-profile-nav-link-3ZWkp"
                                                href="/profile/notifications">Уведомления</a></li></div><div class="header-profile-nav-group-3M1xl"><li class="header-profile-nav-item-1FXQp"
                                        data-item-id="7"
                                        data-marker="header/tooltip-list-item"><a class="header-link-TLsAU header-profile-nav-link-3ZWkp"
                                            href="/account">Кошелёк</a><div class="header-profile-nav-item_value-3FdhP"><div data-marker="header/wallet-value">0<span class="font_arial-rub ">&nbsp;₽</span></div></div></li><li class="header-profile-nav-item-1FXQp"
                                        data-item-id="16"
                                        data-marker="header/tooltip-list-item"><a class="header-link-TLsAU header-profile-nav-link-3ZWkp"
                                            href="/paid-services/listing-fees">Платные услуги</a></li><li class="header-profile-nav-item-1FXQp"
                                            data-item-id="8"
                                            data-marker="header/tooltip-list-item"><span><a class="header-link-TLsAU header-profile-nav-link-3ZWkp header-profile-nav-link-external-NtgH4"
                                                    href="https://pro.avito.ru/?utm_source=avito&amp;utm_medium=organic&amp;utm_content=top_menu_link"
                                                    target="_blank"
                                                    rel="noopener noreferrer">Авито Pro</a></span></li></div><div class="header-profile-nav-group-3M1xl"><li class="header-profile-nav-item-1FXQp"
                                            data-item-id="19"
                                            data-marker="header/tooltip-list-item"><a class="header-link-TLsAU header-profile-nav-link-3ZWkp"
                                                href="/profile/basic">Управление профилем</a></li><li class="header-profile-nav-item-1FXQp"
                                        data-item-id="13"
                                        data-marker="header/tooltip-list-item"><a class="header-link-TLsAU header-profile-nav-link-3ZWkp"
                                            href="/profile/settings">Настройки</a></li></div><div class="header-profile-nav-group-3M1xl"><li class="header-profile-nav-item-1FXQp header-profile-nav-item_exit-gIxDk"
                                        data-item-id="14"
                                        data-marker="header/tooltip-list-item"><a class="header-link-TLsAU header-profile-nav-link-3ZWkp"
                                            href="/profile/exit">Выйти</a></li></div></ul></div></div></div></div><div class="header-button-wrapper-2UC-r"><a class="button-button-Dtqx2 button-button-origin-12oVr button-button-origin-blue-358Vt"
                    href="/additem">Разместить объявление</a></div></div></div></div>
                        <div class=\'js-header-navigation\' data-state=\'{"responsive":true,"alternativeCategoryMenu":null,"categoryMenu":[{"title":"Авто","categoryId":1},{"title":"Недвижимость","categoryId":4},{"title":"Работа","categoryId":110},{"title":"Услуги","categoryId":113}],"orderAllCategories":[{"id":0,"values":[1,2,8]},{"id":1,"values":[4,6]},{"id":2,"values":[110,114,7]},{"id":3,"values":[5,35]}],"categoryTree":{"1":{"id":25984,"mcId":2,"name":"Транспорт","subs":[{"id":25985,"mcId":14,"name":"Автомобили","subs":[],"url":"\/moskva\/avtomobili?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":9,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_9","customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":25986,"mcId":15,"name":"Мотоциклы и мототехника","subs":[],"url":"\/moskva\/mototsikly_i_mototehnika?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":14,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_14","customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26025,"mcId":16,"name":"Грузовики и спецтехника","subs":[],"url":"\/moskva\/gruzoviki_i_spetstehnika?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":81,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_81","customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26040,"mcId":12,"name":"Водный транспорт","subs":[],"url":"\/moskva\/vodnyy_transport?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":11,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_11","customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":25999,"mcId":17,"name":"Запчасти и аксессуары","subs":[],"url":"\/moskva\/zapchasti_i_aksessuary?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":10,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_10","customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/transport?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":1,"params":[],"count":6,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_1","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"4":{"id":26113,"mcId":5,"name":"Недвижимость","subs":[{"id":26125,"mcId":30,"name":"Квартиры","subs":[],"url":"\/moskva\/kvartiry?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":24,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26115,"mcId":31,"name":"Комнаты","subs":[],"url":"\/moskva\/komnaty?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":23,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26116,"mcId":32,"name":"Дома, дачи, коттеджи","subs":[],"url":"\/moskva\/doma_dachi_kottedzhi?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":25,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26119,"mcId":34,"name":"Гаражи и машиноместа","subs":[],"url":"\/moskva\/garazhi_i_mashinomesta?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":85,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26118,"mcId":33,"name":"Земельные участки","subs":[],"url":"\/moskva\/zemelnye_uchastki?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":26,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26114,"mcId":35,"name":"Коммерческая недвижимость","subs":[],"url":"\/moskva\/kommercheskaya_nedvizhimost\/sdam-ASgBAgICAUSwCNRW?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":42,"params":{"536":5546},"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26120,"mcId":36,"name":"Недвижимость за рубежом","subs":[],"url":"\/moskva\/nedvizhimost_za_rubezhom?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":86,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":30344,"mcId":30,"name":"Ипотечный калькулятор","subs":[],"url":"\/ipoteka\/calculator","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":24,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":"\/ipoteka\/calculator","developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/nedvizhimost?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":4,"params":[],"count":9,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_4","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"110":{"id":26400,"mcId":10,"name":"Работа","subs":[{"id":26427,"mcId":61,"name":"Вакансии","subs":[],"url":"\/moskva\/vakansii?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":111,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26401,"mcId":62,"name":"Резюме","subs":[],"url":"\/moskva\/rezume?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":112,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/rabota?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":110,"params":[],"count":3,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_110","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"114":{"id":26486,"mcId":63,"name":"Услуги","subs":[],"url":"\/moskva\/predlozheniya_uslug?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":114,"params":[],"count":24,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_114","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"5":{"id":26127,"mcId":6,"name":"Личные вещи","subs":[{"id":26128,"mcId":37,"name":"Одежда, обувь, аксессуары","subs":[],"url":"\/moskva\/odezhda_obuv_aksessuary?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":27,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26153,"mcId":38,"name":"Детская одежда и обувь","subs":[],"url":"\/moskva\/detskaya_odezhda_i_obuv?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":29,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26173,"mcId":39,"name":"Товары для детей и игрушки","subs":[],"url":"\/moskva\/tovary_dlya_detey_i_igrushki?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":30,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26187,"mcId":41,"name":"Красота и здоровье","subs":[],"url":"\/moskva\/krasota_i_zdorove?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":88,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26183,"mcId":40,"name":"Часы и украшения","subs":[],"url":"\/moskva\/chasy_i_ukrasheniya?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":28,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/lichnye_veschi?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":5,"params":[],"count":6,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_5","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"2":{"id":26047,"mcId":3,"name":"Для дома и дачи","subs":[{"id":26088,"mcId":23,"name":"Ремонт и строительство","subs":[],"url":"\/moskva\/remont_i_stroitelstvo?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":19,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26073,"mcId":21,"name":"Мебель и интерьер","subs":[],"url":"\/moskva\/mebel_i_interer?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":20,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26048,"mcId":20,"name":"Бытовая техника","subs":[],"url":"\/moskva\/bytovaya_tehnika?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":21,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26087,"mcId":18,"name":"Продукты питания","subs":[],"url":"\/moskva\/produkty_pitaniya?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":82,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26097,"mcId":19,"name":"Растения","subs":[],"url":"\/moskva\/rasteniya?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":106,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26084,"mcId":22,"name":"Посуда и товары для кухни","subs":[],"url":"\/moskva\/posuda_i_tovary_dlya_kuhni?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":87,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/dlya_doma_i_dachi?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":2,"params":[],"count":7,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_2","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"10":{"id":30757,"mcId":17,"name":"Запчасти и аксессуары","subs":[{"id":30773,"mcId":211,"name":"Запчасти","subs":[],"url":"\/moskva\/zapchasti_i_aksessuary\/zapchasti-ASgBAgICAUQKJA?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":10,"params":{"5":18},"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":30774,"mcId":210,"name":"Шины, диски и колёса","subs":[],"url":"\/moskva\/zapchasti_i_aksessuary\/shiny_diski_i_kolesa-ASgBAgICAUQKJg?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":10,"params":{"5":19},"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":30775,"mcId":209,"name":"Аудио- и видеотехника","subs":[],"url":"\/moskva\/zapchasti_i_aksessuary\/audio-_i_videotehnika-ASgBAgICAUQKKA?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":10,"params":{"5":20},"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":30776,"mcId":206,"name":"Аксессуары","subs":[],"url":"\/moskva\/zapchasti_i_aksessuary\/aksessuary-ASgBAgICAUQKnk0?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":10,"params":{"5":4943},"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":30777,"mcId":208,"name":"Тюнинг","subs":[],"url":"\/moskva\/zapchasti_i_aksessuary\/tyuning-ASgBAgICAUQKLA?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":10,"params":{"5":22},"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":30778,"mcId":201,"name":"Багажники и фаркопы","subs":[],"url":"\/moskva\/zapchasti_i_aksessuary\/bagazhniki_i_farkopy-ASgBAgICAUQKyE0?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":10,"params":{"5":4964},"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":30779,"mcId":204,"name":"Инструменты","subs":[],"url":"\/moskva\/zapchasti_i_aksessuary\/instrumenty-ASgBAgICAUQKxk0?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":10,"params":{"5":4963},"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":30780,"mcId":200,"name":"Прицепы","subs":[],"url":"\/moskva\/zapchasti_i_aksessuary\/pritsepy-ASgBAgICAUQKyk0?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":10,"params":{"5":4965},"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":30781,"mcId":203,"name":"Экипировка","subs":[],"url":"\/moskva\/zapchasti_i_aksessuary\/ekipirovka-ASgBAgICAUQKoGQ?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":10,"params":{"5":6416},"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":30782,"mcId":207,"name":"Автокосметика и автохимия","subs":[],"url":"\/moskva\/zapchasti_i_aksessuary\/avtokosmetika_i_avtohimiya-ASgBAgICAUQKnE0?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":10,"params":{"5":4942},"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":30783,"mcId":205,"name":"Противоугонные устройства","subs":[],"url":"\/moskva\/zapchasti_i_aksessuary\/protivougonnye_ustroystva-ASgBAgICAUQKoE0?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":10,"params":{"5":4944},"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":30784,"mcId":202,"name":"GPS-навигаторы","subs":[],"url":"\/moskva\/zapchasti_i_aksessuary\/gps-navigatory-ASgBAgICAUQKKg?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":10,"params":{"5":21},"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/zapchasti_i_aksessuary?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":10,"params":[],"count":13,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_10","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"6":{"id":26195,"mcId":7,"name":"Электроника","subs":[{"id":26249,"mcId":49,"name":"Телефоны","subs":[],"url":"\/moskva\/telefony?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":84,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26196,"mcId":43,"name":"Аудио и видео","subs":[],"url":"\/moskva\/audio_i_video?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":32,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26292,"mcId":42,"name":"Товары для компьютера","subs":[],"url":"\/moskva\/tovary_dlya_kompyutera?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":101,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26216,"mcId":44,"name":"Игры, приставки и программы","subs":[],"url":"\/moskva\/igry_pristavki_i_programmy?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":97,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26222,"mcId":46,"name":"Ноутбуки","subs":[],"url":"\/moskva\/noutbuki?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":98,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26221,"mcId":45,"name":"Настольные компьютеры","subs":[],"url":"\/moskva\/nastolnye_kompyutery?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":31,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26209,"mcId":50,"name":"Фототехника","subs":[],"url":"\/moskva\/fototehnika?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":105,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26236,"mcId":48,"name":"Планшеты и электронные книги","subs":[],"url":"\/moskva\/planshety_i_elektronnye_knigi?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":96,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26223,"mcId":47,"name":"Оргтехника и расходники","subs":[],"url":"\/moskva\/orgtehnika_i_rashodniki?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":99,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/bytovaya_elektronika?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":6,"params":[],"count":10,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_6","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"7":{"id":26315,"mcId":8,"name":"Хобби и отдых","subs":[{"id":26316,"mcId":51,"name":"Билеты и путешествия","subs":[],"url":"\/moskva\/bilety_i_puteshestviya?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":33,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26339,"mcId":53,"name":"Велосипеды","subs":[],"url":"\/moskva\/velosipedy?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":34,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26345,"mcId":54,"name":"Книги и журналы","subs":[],"url":"\/moskva\/knigi_i_zhurnaly?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":83,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26349,"mcId":55,"name":"Коллекционирование","subs":[],"url":"\/moskva\/kollektsionirovanie?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":36,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26373,"mcId":52,"name":"Музыкальные инструменты","subs":[],"url":"\/moskva\/muzykalnye_instrumenty?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":38,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26324,"mcId":56,"name":"Охота и рыбалка","subs":[],"url":"\/moskva\/ohota_i_rybalka?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":102,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26325,"mcId":57,"name":"Спорт и отдых","subs":[],"url":"\/moskva\/sport_i_otdyh?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":39,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/hobbi_i_otdyh?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":7,"params":[],"count":8,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_7","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"35":{"id":26098,"mcId":4,"name":"Животные","subs":[{"id":26099,"mcId":24,"name":"Собаки","subs":[],"url":"\/moskva\/sobaki?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":89,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26100,"mcId":25,"name":"Кошки","subs":[],"url":"\/moskva\/koshki?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":90,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26101,"mcId":26,"name":"Птицы","subs":[],"url":"\/moskva\/ptitsy?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":91,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26102,"mcId":27,"name":"Аквариум","subs":[],"url":"\/moskva\/akvarium?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":92,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26103,"mcId":28,"name":"Другие животные","subs":[],"url":"\/moskva\/drugie_zhivotnye?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":93,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26112,"mcId":29,"name":"Товары для животных","subs":[],"url":"\/moskva\/tovary_dlya_zhivotnyh?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":94,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/zhivotnye?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":35,"params":[],"count":7,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_35","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"8":{"id":26382,"mcId":9,"name":"Готовый бизнес и оборудование","subs":[{"id":26383,"mcId":59,"name":"Готовый бизнес","subs":[],"url":"\/moskva\/gotoviy_biznes?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":116,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26393,"mcId":60,"name":"Оборудование для бизнеса","subs":[],"url":"\/moskva\/oborudovanie_dlya_biznesa?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":40,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/dlya_biznesa?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":8,"params":[],"count":3,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_8","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false}},"commonCategories":{"0":{"slug":null,"id":0},"1":{"slug":"transport","id":1},"2":{"slug":"dlya_doma_i_dachi","id":2},"3":{"slug":null,"id":3},"4":{"slug":"nedvizhimost","id":4},"110":{"slug":"rabota","id":110},"111":{"slug":"vakansii","id":111},"112":{"slug":"rezume","id":112},"113":{"slug":"uslugi","id":113},"9":{"slug":"avtomobili","id":9}},"constant":{"Obj_Category_VERTICAL_AUTO":0,"Obj_Category_VERTICAL_REALTY":1,"Obj_Category_VERTICAL_JOB":2,"Obj_Category_VERTICAL_SERVICES":3,"Obj_Category_ROOT_TRANSPORT":1,"Obj_Category_ROOT_REAL_ESTATE":4,"Obj_Category_ROOT_JOB":110,"Obj_Category_JOB_VACANCIES":111,"Obj_Category_JOB_RESUME":112,"Obj_Category_ROOT_SERVICES":113,"Obj_Category_TRANSPORT_CARS":9},"allCategoriesLink":"\/moskva","country":{"host":"www.avito.ru","country_slug":"rossiya","site_name":"Авито","currency_sign":"₽"},"luri":"moskva","verticalId":1,"now":1643220804,"_dashboard":{},"nonce":"mhoCYK+FKeM88AwX4bYjRw=="}\'><div
    class="header-navigation-basic-i28MZ header-container-basic header-navigation-responsive-2_5tJ"
    data-marker="header/navigation"><div class="header-navigation-basic-inner-226Ce  header-container-basic-inner"><div class="header-navigation-logo-2aaur"><span class="logo-root-fxfjv"><a class="logo-logo-2YITg" href="/" title=&quot;Авито &amp;mdash; сайт объявлений&quot;></a></span></div><div class="header-navigation-categories-87Lbp"><div><ul class="simple-with-more-rubricator-category-list-1B8Ve"><li class="simple-with-more-rubricator-category-item-1oRcq "><a class="simple-with-more-rubricator-link-27kbj simple-with-more-rubricator-category-link-3ngHO"
                    href="/moskva/transport"
                    data-marker="navigation/link"
                    data-category-id="1"
                >Авто</a></li><li class="simple-with-more-rubricator-category-item-1oRcq "><a class="simple-with-more-rubricator-link-27kbj simple-with-more-rubricator-category-link-3ngHO"
                    href="/moskva/nedvizhimost"
                    data-marker="navigation/link"
                    data-category-id="4"
                >Недвижимость</a></li><li class="simple-with-more-rubricator-category-item-1oRcq "><a class="simple-with-more-rubricator-link-27kbj simple-with-more-rubricator-category-link-3ngHO"
                    href="/moskva/rabota"
                    data-marker="navigation/link"
                    data-category-id="110"
                >Работа</a></li><li class="simple-with-more-rubricator-category-item-1oRcq "><a class="simple-with-more-rubricator-link-27kbj simple-with-more-rubricator-category-link-3ngHO"
                    href="/moskva/uslugi"
                    data-marker="navigation/link"
                    data-category-id="113"
                >Услуги</a></li><li class="simple-with-more-rubricator-category-item-1oRcq"><button class="simple-with-more-rubricator-link-27kbj simple-with-more-rubricator-category-link-3ngHO simple-with-more-rubricator-category-link_more-3cOco"
                data-marker="navigation/more-button"
                type="button" data-location-id="">ещё</button></li></ul><div
        class="simple-with-more-rubricator-more-popup-2fDTp"
        data-marker="navigation/more-popup"><div
            class="simple-with-more-rubricator-more-popup-arrow-13hlF"
           ></div><div><div class="simple-with-more-rubricator-header-categories-all-2Yo_9 js-header-more-content"><div class="simple-with-more-rubricator-header-categories-all__all-1ElCY"><a href="/moskva">Все категории</a></div><div
                    class="simple-with-more-rubricator-header-categories-all__column-wrapper-Ognfc"
                   ><div class="simple-with-more-rubricator-header-categories-all__column-3KQAH"><ul class="simple-with-more-rubricator-header-categories-all__list-3UY03"><li class=" simple-with-more-rubricator-header-categories-all__item_parent-yGrsI"><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                            href="/moskva/transport?cd=1"
                                            data-category-id="25984"
                                        >Транспорт</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/avtomobili?cd=1"
                                                    data-category-id="25985"
                                                >Автомобили</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/mototsikly_i_mototehnika?cd=1"
                                                    data-category-id="25986"
                                                >Мотоциклы и мототехника</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/gruzoviki_i_spetstehnika?cd=1"
                                                    data-category-id="26025"
                                                >Грузовики и спецтехника</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/vodnyy_transport?cd=1"
                                                    data-category-id="26040"
                                                >Водный транспорт</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/zapchasti_i_aksessuary?cd=1"
                                                    data-category-id="25999"
                                                >Запчасти и аксессуары</a></li></ul><ul class="simple-with-more-rubricator-header-categories-all__list-3UY03"><li class=" simple-with-more-rubricator-header-categories-all__item_parent-yGrsI"><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                            href="/moskva/dlya_doma_i_dachi?cd=1"
                                            data-category-id="26047"
                                        >Для дома и дачи</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/remont_i_stroitelstvo?cd=1"
                                                    data-category-id="26088"
                                                >Ремонт и строительство</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/mebel_i_interer?cd=1"
                                                    data-category-id="26073"
                                                >Мебель и интерьер</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/bytovaya_tehnika?cd=1"
                                                    data-category-id="26048"
                                                >Бытовая техника</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/produkty_pitaniya?cd=1"
                                                    data-category-id="26087"
                                                >Продукты питания</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/rasteniya?cd=1"
                                                    data-category-id="26097"
                                                >Растения</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/posuda_i_tovary_dlya_kuhni?cd=1"
                                                    data-category-id="26084"
                                                >Посуда и товары для кухни</a></li></ul><ul class="simple-with-more-rubricator-header-categories-all__list-3UY03"><li class=" simple-with-more-rubricator-header-categories-all__item_parent-yGrsI"><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                            href="/moskva/dlya_biznesa?cd=1"
                                            data-category-id="26382"
                                        >Готовый бизнес и оборудование</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/gotoviy_biznes?cd=1"
                                                    data-category-id="26383"
                                                >Готовый бизнес</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/oborudovanie_dlya_biznesa?cd=1"
                                                    data-category-id="26393"
                                                >Оборудование для бизнеса</a></li></ul></div><div class="simple-with-more-rubricator-header-categories-all__column-3KQAH"><ul class="simple-with-more-rubricator-header-categories-all__list-3UY03"><li class=" simple-with-more-rubricator-header-categories-all__item_parent-yGrsI"><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                            href="/moskva/nedvizhimost?cd=1"
                                            data-category-id="26113"
                                        >Недвижимость</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/kvartiry?cd=1"
                                                    data-category-id="26125"
                                                >Квартиры</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/komnaty?cd=1"
                                                    data-category-id="26115"
                                                >Комнаты</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/doma_dachi_kottedzhi?cd=1"
                                                    data-category-id="26116"
                                                >Дома, дачи, коттеджи</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/garazhi_i_mashinomesta?cd=1"
                                                    data-category-id="26119"
                                                >Гаражи и машиноместа</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/zemelnye_uchastki?cd=1"
                                                    data-category-id="26118"
                                                >Земельные участки</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/kommercheskaya_nedvizhimost/sdam-ASgBAgICAUSwCNRW?cd=1"
                                                    data-category-id="26114"
                                                >Коммерческая недвижимость</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/nedvizhimost_za_rubezhom?cd=1"
                                                    data-category-id="26120"
                                                >Недвижимость за рубежом</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/ipoteka/calculator"
                                                    data-category-id="30344"
                                                >Ипотечный калькулятор</a></li></ul><ul class="simple-with-more-rubricator-header-categories-all__list-3UY03"><li class=" simple-with-more-rubricator-header-categories-all__item_parent-yGrsI"><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                            href="/moskva/bytovaya_elektronika?cd=1"
                                            data-category-id="26195"
                                        >Электроника</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/telefony?cd=1"
                                                    data-category-id="26249"
                                                >Телефоны</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/audio_i_video?cd=1"
                                                    data-category-id="26196"
                                                >Аудио и видео</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/tovary_dlya_kompyutera?cd=1"
                                                    data-category-id="26292"
                                                >Товары для компьютера</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/igry_pristavki_i_programmy?cd=1"
                                                    data-category-id="26216"
                                                >Игры, приставки и программы</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/noutbuki?cd=1"
                                                    data-category-id="26222"
                                                >Ноутбуки</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/nastolnye_kompyutery?cd=1"
                                                    data-category-id="26221"
                                                >Настольные компьютеры</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/fototehnika?cd=1"
                                                    data-category-id="26209"
                                                >Фототехника</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/planshety_i_elektronnye_knigi?cd=1"
                                                    data-category-id="26236"
                                                >Планшеты и электронные книги</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/orgtehnika_i_rashodniki?cd=1"
                                                    data-category-id="26223"
                                                >Оргтехника и расходники</a></li></ul></div><div class="simple-with-more-rubricator-header-categories-all__column-3KQAH"><ul class="simple-with-more-rubricator-header-categories-all__list-3UY03"><li class=" simple-with-more-rubricator-header-categories-all__item_parent-yGrsI"><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                            href="/moskva/rabota?cd=1"
                                            data-category-id="26400"
                                        >Работа</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/vakansii?cd=1"
                                                    data-category-id="26427"
                                                >Вакансии</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/rezume?cd=1"
                                                    data-category-id="26401"
                                                >Резюме</a></li></ul><ul class="simple-with-more-rubricator-header-categories-all__list-3UY03"><li class=" simple-with-more-rubricator-header-categories-all__item_parent-yGrsI"><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                            href="/moskva/predlozheniya_uslug?cd=1"
                                            data-category-id="26486"
                                        >Услуги</a></li></ul><ul class="simple-with-more-rubricator-header-categories-all__list-3UY03"><li class=" simple-with-more-rubricator-header-categories-all__item_parent-yGrsI"><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                            href="/moskva/hobbi_i_otdyh?cd=1"
                                            data-category-id="26315"
                                        >Хобби и отдых</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/bilety_i_puteshestviya?cd=1"
                                                    data-category-id="26316"
                                                >Билеты и путешествия</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/velosipedy?cd=1"
                                                    data-category-id="26339"
                                                >Велосипеды</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/knigi_i_zhurnaly?cd=1"
                                                    data-category-id="26345"
                                                >Книги и журналы</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/kollektsionirovanie?cd=1"
                                                    data-category-id="26349"
                                                >Коллекционирование</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/muzykalnye_instrumenty?cd=1"
                                                    data-category-id="26373"
                                                >Музыкальные инструменты</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/ohota_i_rybalka?cd=1"
                                                    data-category-id="26324"
                                                >Охота и рыбалка</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/sport_i_otdyh?cd=1"
                                                    data-category-id="26325"
                                                >Спорт и отдых</a></li></ul></div><div class="simple-with-more-rubricator-header-categories-all__column-3KQAH"><ul class="simple-with-more-rubricator-header-categories-all__list-3UY03"><li class=" simple-with-more-rubricator-header-categories-all__item_parent-yGrsI"><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                            href="/moskva/lichnye_veschi?cd=1"
                                            data-category-id="26127"
                                        >Личные вещи</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/odezhda_obuv_aksessuary?cd=1"
                                                    data-category-id="26128"
                                                >Одежда, обувь, аксессуары</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/detskaya_odezhda_i_obuv?cd=1"
                                                    data-category-id="26153"
                                                >Детская одежда и обувь</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/tovary_dlya_detey_i_igrushki?cd=1"
                                                    data-category-id="26173"
                                                >Товары для детей и игрушки</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/krasota_i_zdorove?cd=1"
                                                    data-category-id="26187"
                                                >Красота и здоровье</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/chasy_i_ukrasheniya?cd=1"
                                                    data-category-id="26183"
                                                >Часы и украшения</a></li></ul><ul class="simple-with-more-rubricator-header-categories-all__list-3UY03"><li class=" simple-with-more-rubricator-header-categories-all__item_parent-yGrsI"><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                            href="/moskva/zhivotnye?cd=1"
                                            data-category-id="26098"
                                        >Животные</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/sobaki?cd=1"
                                                    data-category-id="26099"
                                                >Собаки</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/koshki?cd=1"
                                                    data-category-id="26100"
                                                >Кошки</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/ptitsy?cd=1"
                                                    data-category-id="26101"
                                                >Птицы</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/akvarium?cd=1"
                                                    data-category-id="26102"
                                                >Аквариум</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/drugie_zhivotnye?cd=1"
                                                    data-category-id="26103"
                                                >Другие животные</a></li><li class=""><a class="simple-with-more-rubricator-header-categories-all__link-k_Jr3 js-header-categories-all__link"
                                                    href="/moskva/tovary_dlya_zhivotnyh?cd=1"
                                                    data-category-id="26112"
                                                >Товары для животных</a></li></ul></div></div></div></div></div></div></div></div></div></div>            </div>
    
        <div class="layout-internal layout-responsive">
        
    
    

<div class="b-search-form  b-search-form_item b-search-form_realty">
    <form
        id="search_form"
        class="search-form__form js-search-form-catalog js-search-form"
        autocomplete="off"
        action="/search"
        method="post"
        data-initial-request-counter=\'1\'
        data-hide-counter=""
        data-total-count=\'0\'
        data-marker="search-form">
        <div class="search-form-main-controls js-search-form-main-controls">
                        <input type="hidden" class="js-search-map" name="map" value="">

                                                                <input type="hidden" class="js-token" name="token[8994913488711]" value="1643220804.2400.8e694df294c8b2ce6aa869f64e46994d734f77a7e2e7f1cdc4828ddfa9f7ea8e">
            
            
            <div class="search-form__row search-form__row_1 clearfix">
                                <div class="search-form__category">
                    <div class="form-select-v2">
                        
                        <select id="category" name="category_id"
                                class="js-search-form-category "
                                data-marker="search-form/category">
                            <option value="">Любая категория</option>
                                                                                                <option value="1" class="opt-group"  >Транспорт</option>
                                                                                                                                <option value="9" >Автомобили</option>
                                                                                                                                <option value="14" >Мотоциклы и мототехника</option>
                                                                                                                                <option value="81" >Грузовики и спецтехника</option>
                                                                                                                                <option value="11" >Водный транспорт</option>
                                                                                                                                <option value="10" >Запчасти и аксессуары</option>
                                                                                                                                <option value="4" class="opt-group"  >Недвижимость</option>
                                                                                                                                <option value="24" >Квартиры</option>
                                                                                                                                <option value="23"  selected>Комнаты</option>
                                                                                                                                <option value="25" >Дома, дачи, коттеджи</option>
                                                                                                                                <option value="26" >Земельные участки</option>
                                                                                                                                <option value="85" >Гаражи и машиноместа</option>
                                                                                                                                <option value="42" >Коммерческая недвижимость</option>
                                                                                                                                <option value="86" >Недвижимость за рубежом</option>
                                                                                                                                <option value="110" class="opt-group"  >Работа</option>
                                                                                                                                <option value="111" >Вакансии</option>
                                                                                                                                <option value="112" >Резюме</option>
                                                                                                                                <option value="113" class="opt-group"  >Услуги</option>
                                                                                                                                <option value="114" >Предложение услуг</option>
                                                                                                                                <option value="5" class="opt-group"  >Личные вещи</option>
                                                                                                                                <option value="27" >Одежда, обувь, аксессуары</option>
                                                                                                                                <option value="29" >Детская одежда и обувь</option>
                                                                                                                                <option value="30" >Товары для детей и игрушки</option>
                                                                                                                                <option value="28" >Часы и украшения</option>
                                                                                                                                <option value="88" >Красота и здоровье</option>
                                                                                                                                <option value="2" class="opt-group"  >Для дома и дачи</option>
                                                                                                                                <option value="21" >Бытовая техника</option>
                                                                                                                                <option value="20" >Мебель и интерьер</option>
                                                                                                                                <option value="87" >Посуда и товары для кухни</option>
                                                                                                                                <option value="82" >Продукты питания</option>
                                                                                                                                <option value="19" >Ремонт и строительство</option>
                                                                                                                                <option value="106" >Растения</option>
                                                                                                                                <option value="6" class="opt-group"  >Электроника</option>
                                                                                                                                <option value="32" >Аудио и видео</option>
                                                                                                                                <option value="97" >Игры, приставки и программы</option>
                                                                                                                                <option value="31" >Настольные компьютеры</option>
                                                                                                                                <option value="98" >Ноутбуки</option>
                                                                                                                                <option value="99" >Оргтехника и расходники</option>
                                                                                                                                <option value="96" >Планшеты и электронные книги</option>
                                                                                                                                <option value="84" >Телефоны</option>
                                                                                                                                <option value="101" >Товары для компьютера</option>
                                                                                                                                <option value="105" >Фототехника</option>
                                                                                                                                <option value="7" class="opt-group"  >Хобби и отдых</option>
                                                                                                                                <option value="33" >Билеты и путешествия</option>
                                                                                                                                <option value="34" >Велосипеды</option>
                                                                                                                                <option value="83" >Книги и журналы</option>
                                                                                                                                <option value="36" >Коллекционирование</option>
                                                                                                                                <option value="38" >Музыкальные инструменты</option>
                                                                                                                                <option value="102" >Охота и рыбалка</option>
                                                                                                                                <option value="39" >Спорт и отдых</option>
                                                                                                                                <option value="35" class="opt-group"  >Животные</option>
                                                                                                                                <option value="89" >Собаки</option>
                                                                                                                                <option value="90" >Кошки</option>
                                                                                                                                <option value="91" >Птицы</option>
                                                                                                                                <option value="92" >Аквариум</option>
                                                                                                                                <option value="93" >Другие животные</option>
                                                                                                                                <option value="94" >Товары для животных</option>
                                                                                                                                <option value="8" class="opt-group"  >Готовый бизнес и оборудование</option>
                                                                                                                                <option value="116" >Готовый бизнес</option>
                                                                                                                                <option value="40" >Оборудование для бизнеса</option>
                                                                                    </select>
                    </div>
                </div>

                                <div class="search-form__submit">
                    <input
                        type="submit"
                        value="Найти"
                        class="search button button-origin js-search-button"
                        data-marker="search-form/submit-button">
                </div>

                                                                                                                                                
                                
                                                                                
                                            
                        
                                                                                                                                                                                                                        
                                                                                                                                    
                                                                                                                            
                                            
                        
                                                                                                                                                                                                                        
                                                                            
                                                            
                
                                    
                    <div class="hidden js-show-elements" data-show-elements="[&quot;metro&quot;,&quot;districts&quot;]"></div>

                    <div class="search-form__direction">
                        <div id="directions" class="form-select-v2 param " data-marker="search-form/directions">
                            <select
                                                                name="metro[]" id="directions-select"
                                class="directions"                                 data-filter="1">
                                <option data-prev-alias="metro" value="">Метро / Район</option>
                            </select>

                                                        <select multiple class="hidden-input-for-tab" id="directions-multiple"></select>
                        </div>
                        <div
                            class="search-form__change-filters disabled js-change-filters"
                                                        data-current-tab="metro"
                            data-selected-elements=\'[]\'
                        ></div>
                    </div>

                    
                                    
                
                                <div class="search-form__location">
                                                            <div class="form-select-v2">
                        <select
                            id="region"
                            name="location_id"
                            class="js-search-form-region"
                            data-marker="search-form/region">
                            <option
                                    value="621540"
                                    data-parent-id=""
                                                                                                        >По всей России</option><option
                                    value="637640"
                                    data-parent-id="621540"
                                     data-metro-map="1"                                     selected                                >Москва</option><option
                                    value="637680"
                                    data-parent-id="621540"
                                                                                                        >Московская область</option>                            <option value="0">Выбрать другой...</option>
                        </select>
                    </div>
                    <div
                        class="search-form__change-location disabled js-change-location"
                        data-location-id="637640"
                        data-location-name="Москва"
                        data-category-id="23"
                        data-local-priority=""
                    ></div>
                </div>

                                <div class="search-form__key-words">
                    <div id="search_holder" class="search-form__key-words__search-holder">
                        <input id="search"
                            type="text" name="name" value=""
                            placeholder="Поиск по объявлениям"
                                                        spellcheck="false"                            data-suggest="true"                            maxlength="100"
                            data-suggest-delay=""
                            data-marker="search-form/suggest">
                    </div>
                </div>
            </div>

            <div
                                                                                class="search-form__row search-form__row_2 js-pre-filters hidden"
                id="pre-filters">

                                <label class="form-checkbox" data-marker="search-form/by-title">
                    <input type="checkbox" class="js-by-title" name="bt" >
                    <span class="form-checkbox__label">только в названиях</span>
                </label>

                <label class="form-checkbox" data-marker="search-form/with-images">
                    <input type="checkbox" class="js-with-images" name="i" >
                    <span class="form-checkbox__label">только с фото</span>
                </label>

                            </div>
        </div>

            </form>

    </div>
    </div>
        
    <div class="item-view-page-layout item-view-page-layout_content item-view-page-layout_responsive">
        <div class="l-content clearfix">
                        

    

    <div class="avito-ads-container avito-ads-container_ldr_top ad_1000x120 js-ads-loading avito-ads-placeholder avito-ads-placeholder_ldr_top">
        <div id="template_ldr_top" class="avito-ads-template">
            <div class="js-banner-1000x120 item-view-ads-ldr_top avito-ads-content">
            </div>
        </div>
    </div>

<div
    class="item-view js-item-view  item-view__new-style"
    itemscope itemtype="http://schema.org/Product">

        
                    
<div class="sticky-header sticky-header_long-btn-bar js-sticky-header sticky-header_responsive" data-relative-node="toggle-sticker-header">
    <div class="item-view-page-layout">
        <div class="sticky-header-content">
            <div class="sticky-header-left">
                                    <div class="sticky-header-favorites">
                                                <a href="/web/1/profile/favorites/add/1768618038"
                            data-action
                            data-options="&#x7B;&quot;isFavorite&quot;&#x3A;false,&quot;categorySlug&quot;&#x3A;&quot;komnaty&quot;,&quot;compare&quot;&#x3A;true,&quot;searchHash&quot;&#x3A;null&#x7D;"
                            class="sticky-header-favorites-link add-favorite add-favorite_small js-add-favorite-header"></a>
                    </div>
                                            <div class="sticky-header-notes">
                            <span class="sticky-header-notes-link item-notes-icon_add item-notes-icon_add_header js-add-note-header"></span>
                        </div>
                                                    <div class="sticky-header-prop sticky-header-title">
                    Комната 15 м² в 3-к., 2/5 эт.
                </div>
                <div class="sticky-header-prop sticky-header-price">
                    



<div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="price-value price-value_side-header" id="price-value">
    <span class="price-value-string js-price-value-string">
            <meta itemprop="availability" content="https://schema.org/LimitedAvailability" />
    <meta itemprop="priceCurrency" content="RUB" />
            <meta itemprop="price" content="0" />    
    </span>

    </div>
                </div>
            </div>

            <div class="sticky-header-right">
                                                    <div class="sticky-header-prop sticky-header-seller">
                        <span class="sticky-header-seller-text" title="Юлия">Юлия</span>
                    </div>
                
                                    <div class="sticky-header-prop sticky-header-contacts">
                        
                        
                                                                                                                                                        

                    
    


    






        <a
        class="button item-phone-button js-item-phone-button button-origin contactBar_greenColor item-phone-button_header js-item-phone-button_header contactBar__header_height"
        href="javascript:void(0);"
        data-marker="item-phone-button/header"
        data-side="header" data-privet="privet">
                                                Показать телефон
                        
    </a>

                                                            
                                                                                            



<span class="js-messenger-button"
    data-props="&#x7B;&quot;isOpenInNewTab&quot;&#x3A;null,&quot;isAuthorized&quot;&#x3A;true,&quot;isFullWidth&quot;&#x3A;null,&quot;isMiniMessenger&quot;&#x3A;true,&quot;isIconButton&quot;&#x3A;null,&quot;isHidden&quot;&#x3A;null,&quot;parentSelector&quot;&#x3A;null,&quot;hasHidePhoneOnboarding&quot;&#x3A;false,&quot;sellerIdHash&quot;&#x3A;138191850,&quot;itemId&quot;&#x3A;1768618038,&quot;itemUrl&quot;&#x3A;&quot;&#x5C;&#x2F;moskva&#x5C;&#x2F;komnaty&#x5C;&#x2F;komnata_15m_v_3-k._25et._1768618038&quot;,&quot;itemCVViewed&quot;&#x3A;null,&quot;categoryId&quot;&#x3A;23,&quot;buttonText&quot;&#x3A;&quot;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x041D;&#x0430;&#x043F;&#x0438;&#x0441;&#x0430;&#x0442;&#x044C;&#x20;&#x0441;&#x043E;&#x043E;&#x0431;&#x0449;&#x0435;&#x043D;&#x0438;&#x0435;&#x5C;n&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&quot;,&quot;replyTime&quot;&#x3A;null,&quot;logParams&quot;&#x3A;&#x7B;&quot;userId&quot;&#x3A;90200356,&quot;wsrc&quot;&#x3A;&quot;item&quot;,&quot;s&quot;&#x3A;&quot;mi&quot;&#x7D;,&quot;experiments&quot;&#x3A;&#x7B;&quot;item&quot;&#x3A;&#x5B;&#x5D;&#x7D;,&quot;side&quot;&#x3A;&quot;header&quot;,&quot;contactlessView&quot;&#x3A;null&#x7D;"
    data-marker="messenger-button"
    data-side="header" >
    <a
        class="button button-origin contactBar_blueColor"
                style="">
                                                Написать сообщение
                                
    </a>
</span>
                                                                        </div>
                            </div>
        </div>
    </div>
</div>
    
                        
                
        
    
    <div class="item-navigation ">
        
    
                                                                
    <div itemscope itemtype="http://schema.org/BreadcrumbList" class="breadcrumbs js-breadcrumbs breadcrumbs_gray">
                    <span itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem">
                <a
                    itemprop="item"
                    itemtype="http://schema.org/ListItem"
                    class="js-breadcrumbs-link js-breadcrumbs-link-interaction"
                    href="/moskva"
                    title="Все объявления в Москве">
                        <span itemprop="name">Москва</span>
                </a>
                <meta itemprop="position" content="1">
            </span>

                            <span class="breadcrumbs-separator breadcrumbs-separator_gray">·</span>
                                <span itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem">
                <a
                    itemprop="item"
                    itemtype="http://schema.org/ListItem"
                    class="js-breadcrumbs-link js-breadcrumbs-link-interaction"
                    href="/moskva/nedvizhimost"
                    title="Недвижимость в Москве">
                        <span itemprop="name">Недвижимость</span>
                </a>
                <meta itemprop="position" content="2">
            </span>

                            <span class="breadcrumbs-separator breadcrumbs-separator_gray">·</span>
                                <span itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem">
                <a
                    itemprop="item"
                    itemtype="http://schema.org/ListItem"
                    class="js-breadcrumbs-link js-breadcrumbs-link-interaction"
                    href="/moskva/komnaty"
                    title="Комнаты в Москве">
                        <span itemprop="name">Комнаты</span>
                </a>
                <meta itemprop="position" content="3">
            </span>

                            <span class="breadcrumbs-separator breadcrumbs-separator_gray">·</span>
                                <span itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem">
                <a
                    itemprop="item"
                    itemtype="http://schema.org/ListItem"
                    class="js-breadcrumbs-link js-breadcrumbs-link-interaction"
                    href="/moskva/komnaty/sdam-ASgBAgICAUSQA74Q"
                    title="Снять">
                        <span itemprop="name">Снять</span>
                </a>
                <meta itemprop="position" content="4">
            </span>

                            <span class="breadcrumbs-separator breadcrumbs-separator_gray">·</span>
                                <span itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem">
                <a
                    itemprop="item"
                    itemtype="http://schema.org/ListItem"
                    class="js-breadcrumbs-link js-breadcrumbs-link-interaction"
                    href="/moskva/komnaty/sdam/na_dlitelnyy_srok-ASgBAgICAkSQA74QqAn2YA"
                    title="На длительный срок">
                        <span itemprop="name">На длительный срок</span>
                </a>
                <meta itemprop="position" content="5">
            </span>

                        </div>
</div>

    
    
        <div class="item-view-header">
            </div>
    <div class="banners-header-after-container">
            </div>

        
    <div class="item-view-content">
        <div class="item-view-content-left">
                                    
        
<div class="item-view-title-info js-item-view-title-info">
    <div class="title-info title-info_mode-with-favorite">
        <div class="title-info-main">
            <h1 class="title-info-title">
                                <span class="title-info-title-text" itemprop="name">Комната 15 м² в 3-к., 2/5 эт.</span>
            </h1>
        </div>
        
        <div id="toggle-sticker-header" class="js-toggle-sticker-header"></div>
                        <div class="title-info-metadata">
                    
                    
                                    </div>

                            <div class="title-info-actions">
                    <div class="title-info-actions-item">
                        

<a
    data-side=""
    data-action
    data-favorite-mode="button"
    data-options="&#x7B;&quot;isFavorite&quot;&#x3A;false,&quot;categorySlug&quot;&#x3A;&quot;komnaty&quot;,&quot;compare&quot;&#x3A;true,&quot;searchHash&quot;&#x3A;null&#x7D;"
    href="/web/1/profile/favorites/add/1768618038"
    class="button button-origin button-origin_small add-favorite-button js-add-favorite">
    <i class="add-favorite-button-icon"></i>
    <span class="add-favorite-button-text">Добавить в избранное</span>
</a>

                        
    
<a
    data-side=""
    href="javascript:void(0);"
    class="button button-origin button-origin_small item-notes-button js-item-add-note-button js-item-add-note hidden">
    <i class="item-notes-icon_add"></i>Добавить заметку
</a>

                                                    <div data-item-id="1768618038"
    data-category-id="23"
    class="item-view-notes js-item-view-notes hidden"></div>
                        
                                                    <div class="title-info-metadata-item-redesign">
                                
    сегодня в 10:37
                            </div>
                                            </div>
                </div>
                    
            </div>
</div>
    

                                            
                <div class="item-view-main js-item-view-main">
                                                                                            <div class="item-view-gallery" data-hero="true">
                                
            
                                                                                                                                                                                                                                                                

<div class="gallery gallery_state-clicked js-gallery" >
    <div class="gallery-imgs-wrapper js-gallery-imgs-wrapper  ">
                            <div class="gallery-imgs-container js-gallery-imgs-container">
                                                    <div class="gallery-img-wrapper js-gallery-img-wrapper">
                        <div class="gallery-img-frame js-gallery-img-frame"
                             data-url="https://09.img.avito.st/image/1/1.NOwmZraymAUQz1oAQiJc3MrFmAOGx5o.VpWY8HV0YI9KJAG1ePDx-gSuXymD4F_WNbCS-w_1NKc"
                             data-title="Комната 15 м² в 3-к., 2/5 эт.">
                        </div>
                    </div>
                                                        <div class="gallery-img-wrapper js-gallery-img-wrapper">
                        <div class="gallery-img-frame js-gallery-img-frame"
                             data-url="https://30.img.avito.st/image/1/1.I22en7ayj4SoNk2B1NtLXXI8j4I-Po0.iXN6H-zIk50sD3CLU7e4RBpq6Non8r5koeF5wWWynVA"
                             data-title="Комната 15 м² в 3-к., 2/5 эт.">
                        </div>
                    </div>
                                                        <div class="gallery-img-wrapper js-gallery-img-wrapper">
                        <div class="gallery-img-frame js-gallery-img-frame"
                             data-url="https://97.img.avito.st/image/1/1.ALvPr7ayrFL5Bm5Xw-toiyMMrFRvDq4.L4Avo-6YnhQZUN5-N8N2YUuW14JZ3KmW5EAh5RbeOuM"
                             data-title="Комната 15 м² в 3-к., 2/5 эт.">
                        </div>
                    </div>
                                                        <div class="gallery-img-wrapper js-gallery-img-wrapper">
                        <div class="gallery-img-frame js-gallery-img-frame"
                             data-url="https://26.img.avito.st/image/1/1.JyPUILayi8riiUnPlmRPEziDi8x0gYk.vXXJNM3_VwH1c7gDiRdLrL7IWl4_sippBjKljHUqWU4"
                             data-title="Комната 15 м² в 3-к., 2/5 эт.">
                        </div>
                    </div>
                                                        <div class="gallery-img-wrapper js-gallery-img-wrapper">
                        <div class="gallery-img-frame js-gallery-img-frame"
                             data-url="https://82.img.avito.st/image/1/1.FxTsvLayu_3aFXn4zpQPYBMfu_tMHbk.G-ppZhrjpZK5JGvxGvXgyH1M751WXIY_4W0LwVXIdRo"
                             data-title="Комната 15 м² в 3-к., 2/5 эт.">
                        </div>
                    </div>
                                                        <div class="gallery-img-wrapper js-gallery-img-wrapper">
                        <div class="gallery-img-frame js-gallery-img-frame"
                             data-url="https://88.img.avito.st/image/1/1.DRbe3Layof_odWP6yPQVYiF_ofl-faM.ax8_I9ity9nC5FunCJ0vz_GEjbAze5AOBUCpkCPK19M"
                             data-title="Комната 15 м² в 3-к., 2/5 эт.">
                        </div>
                    </div>
                                                        <div class="gallery-img-wrapper js-gallery-img-wrapper">
                        <div class="gallery-img-frame js-gallery-img-frame"
                             data-url="https://89.img.avito.st/image/1/1.DBUCnLayoPw0NWL5FrQUYf0_oPqiPaI.eQezls51_E8Vpkdb6tTd9h8XNVD9UWCkEgGKUJeVRg0"
                             data-title="Комната 15 м² в 3-к., 2/5 эт.">
                        </div>
                    </div>
                                                        <div class="gallery-img-wrapper js-gallery-img-wrapper">
                        <div class="gallery-img-frame js-gallery-img-frame"
                             data-url="https://48.img.avito.st/image/1/1.dYxd3ray2WVrdxtgO7d556J92WP9f9s.JBSGNGtb0WMPS_ZuUp8axasD4_26SU23h4fhhw9R9f4"
                             data-title="Комната 15 м² в 3-к., 2/5 эт.">
                        </div>
                    </div>
                                                        <div class="gallery-img-wrapper js-gallery-img-wrapper">
                        <div class="gallery-img-frame js-gallery-img-frame"
                             data-url="https://47.img.avito.st/image/1/1.ehF5dbay1vhP3BT9ARx2eobW1v7Z1NQ.ZHKvZnHYnK8Pq5bXRLbEV3VCvJwLyoWhc68K02rTOqQ"
                             data-title="Комната 15 м² в 3-к., 2/5 эт.">
                        </div>
                    </div>
                                                </div>
        
                            <div class="gallery-navigation gallery-navigation_prev js-gallery-navigation" data-dir="prev">
                <span class="gallery-navigation-icon"></span>
            </div>
            <div class="gallery-navigation gallery-navigation_next js-gallery-navigation" data-dir="next">
                <span class="gallery-navigation-icon"></span>
            </div>
            </div>
                <div class="gallery-list-wrapper ">
            <ul class="gallery-list js-gallery-list">
                                                    <li class="gallery-list-item gallery-list-item_selected js-gallery-list-item" data-index="0" data-type="image">
                        <div class="gallery-list-item-link">
                            <img src="https://09.img.avito.st/image/1/1.NOwmZrawmAUGxPTVdwNBXZDHkg8GxPQHkA.zlqUbquI8umwmsJvSqWDacLMbGdQvq8HslYjixuFniA" alt="Комната 15 м² в 3-к., 2/5 эт."/>
                        </div>
                    </li>
                                                                            <li class="gallery-list-item  js-gallery-list-item" data-index="1" data-type="image">
                        <div class="gallery-list-item-link">
                            <img src="https://30.img.avito.st/image/1/1.I22en7awj4S-PeN6z_pW3Cg-hY6-PeOGKA.esBXVCJrNm8nyHl36HQTaTII6zmEa-gdmzvdLZZ7AG4" alt="Комната 15 м² в 3-к., 2/5 эт."/>
                        </div>
                    </li>
                                                                            <li class="gallery-list-item  js-gallery-list-item" data-index="2" data-type="image">
                        <div class="gallery-list-item-link">
                            <img src="https://97.img.avito.st/image/1/1.ALvPr7awrFLvDcDqnsp1CnkOpljvDcBQeQ._JgScvp2LZ177FEluLmqnxiLeURW3huZ6sl6847q6zs" alt="Комната 15 м² в 3-к., 2/5 эт."/>
                        </div>
                    </li>
                                                                            <li class="gallery-list-item  js-gallery-list-item" data-index="3" data-type="image">
                        <div class="gallery-list-item-link">
                            <img src="https://26.img.avito.st/image/1/1.JyPUILawi8r0guc8hUVSkmKBgcD0gufIYg.fREAdguXP18SZVi7RSYKgdUvLo48TGgv1t-k0Tq_adQ" alt="Комната 15 м² в 3-к., 2/5 эт."/>
                        </div>
                    </li>
                                                                            <li class="gallery-list-item  js-gallery-list-item" data-index="4" data-type="image">
                        <div class="gallery-list-item-link">
                            <img src="https://82.img.avito.st/image/1/1.FxTsvLawu_3MHtdr0akmtlodsffMHtf_Wg.hMvz03T62PrKsKjwEYsJpS1rtiWUHhcZooEemYiSygw" alt="Комната 15 м² в 3-к., 2/5 эт."/>
                        </div>
                    </li>
                                                                            <li class="gallery-list-item  js-gallery-list-item" data-index="5" data-type="image">
                        <div class="gallery-list-item-link">
                            <img src="https://88.img.avito.st/image/1/1.DRbe3Lawof_-fs1d48k8tGh9q_X-fs39aA.BXlvPtkaYhYrMJYGyQ2BU_W3xLyNZ3jtL1KvpiHvjtA" alt="Комната 15 м² в 3-к., 2/5 эт."/>
                        </div>
                    </li>
                                                                            <li class="gallery-list-item  js-gallery-list-item" data-index="6" data-type="image">
                        <div class="gallery-list-item-link">
                            <img src="https://89.img.avito.st/image/1/1.DBUCnLawoPwiPsxcP4k9t7Q9qvYiPsz-tA.CEuQovsVlhNH4UZqb-h56gtKhY_I2h1DEQn6DoIRe-g" alt="Комната 15 м² в 3-к., 2/5 эт."/>
                        </div>
                    </li>
                                                                            <li class="gallery-list-item  js-gallery-list-item" data-index="7" data-type="image">
                        <div class="gallery-list-item-link">
                            <img src="https://48.img.avito.st/image/1/1.dYxd3raw2WV9fLW3Id9bLut_0299fLVn6w.l0GtgXtaVzlUI4rWj3j68Q4GqHuy2gfEy23D8NVDABk" alt="Комната 15 м² в 3-к., 2/5 эт."/>
                        </div>
                    </li>
                                                                            <li class="gallery-list-item  js-gallery-list-item" data-index="8" data-type="image">
                        <div class="gallery-list-item-link">
                            <img src="https://47.img.avito.st/image/1/1.ehF5dbaw1vhZ17o0BXRUs8_U3PJZ17r6zw.wZy9okzuHq4rE7jAQnDJTWWKftuoFmKC0gb2QkMf2dg" alt="Комната 15 м² в 3-к., 2/5 эт."/>
                        </div>
                    </li>
                                                                    </ul>
        </div>
    
        </div>
                                                                    
            
                                                                                                                                                                                                                                                                                                                                                                                                                                                    




<div class="gallery-extended gallery-extended_state-clicked gallery-extended_state-hide js-gallery-extended" data-shop-id="" >
    <div class="gallery-extended-content-wrapper js-gallery-extended-content-wrapper">
        <div class="gallery-extended-content js-gallery-extended-content">
            <div class="gallery-extended-container js-gallery-extended-container">
                                <div class="gallery-extended-close js-gallery-extended-close"></div>
                <div class="gallery-extended-imgs-control">
                                                                <div class="gallery-extended-img-nav gallery-extended-img-nav_type-prev js-gallery-extended-img-nav" data-dir="prev">
                            <span class="gallery-extended-img-nav-icon"></span>
                        </div>
                        <div class="gallery-extended-img-nav gallery-extended-img-nav_type-next js-gallery-extended-img-nav" data-dir="next">
                            <span class="gallery-extended-img-nav-icon"></span>
                        </div>
                                                            <div class="gallery-extended-imgs-wrapper js-gallery-extended-imgs-wrapper">
                                                                                                        <div class="gallery-extended-img-frame gallery-extended-img-frame_state-selected js-gallery-extended-img-frame"
                                data-url="https://09.img.avito.st/image/1/1.NOwmZraymAUQ0RoIQiJc3MrFmAOGx5o.z4i9ENtyZGpNn-FBKw9Nu1shuU9yzdhmGE6gAZwAAb8"
                                data-image-id="12175596009"
                                data-alt-urls="[&quot;https:\/\/09.img.avito.st\/image\/1\/1.NOwmZraymAUQz1oAQiJc3MrFmAOGx5o.VpWY8HV0YI9KJAG1ePDx-gSuXymD4F_WNbCS-w_1NKc&quot;]"
                                data-title="Комната 15 м² в 3-к., 2/5 эт.">
                            </div>
                                                                                                            <div class="gallery-extended-img-frame js-gallery-extended-img-frame"
                                data-url="https://30.img.avito.st/image/1/1.I22en7ayj4SoKA2J1NtLXXI8j4I-Po0.aNxlK9nyiZJXwBFVutCyHX8Ke4y4tiVDheU5v9x7zNs"
                                data-image-id="12175596030"
                                data-alt-urls="[&quot;https:\/\/30.img.avito.st\/image\/1\/1.I22en7ayj4SoNk2B1NtLXXI8j4I-Po0.iXN6H-zIk50sD3CLU7e4RBpq6Non8r5koeF5wWWynVA&quot;]"
                                data-title="Комната 15 м² в 3-к., 2/5 эт.">
                            </div>
                                                                                                            <div class="gallery-extended-img-frame js-gallery-extended-img-frame"
                                data-url="https://97.img.avito.st/image/1/1.ALvPr7ayrFL5GC5fw-toiyMMrFRvDq4.YsiIfbtM7GsSqINa45-NId7OOnq-669qY_4nwq5EbmQ"
                                data-image-id="12175595997"
                                data-alt-urls="[&quot;https:\/\/97.img.avito.st\/image\/1\/1.ALvPr7ayrFL5Bm5Xw-toiyMMrFRvDq4.L4Avo-6YnhQZUN5-N8N2YUuW14JZ3KmW5EAh5RbeOuM&quot;]"
                                data-title="Комната 15 м² в 3-к., 2/5 эт.">
                            </div>
                                                                                                            <div class="gallery-extended-img-frame js-gallery-extended-img-frame"
                                data-url="https://26.img.avito.st/image/1/1.JyPUILayi8rilwnHlmRPEziDi8x0gYk.zyOzsg6EArfYA2ltzqj9tIkhWvzp3pKEz3amim7dBvM"
                                data-image-id="12175596026"
                                data-alt-urls="[&quot;https:\/\/26.img.avito.st\/image\/1\/1.JyPUILayi8riiUnPlmRPEziDi8x0gYk.vXXJNM3_VwH1c7gDiRdLrL7IWl4_sippBjKljHUqWU4&quot;]"
                                data-title="Комната 15 м² в 3-к., 2/5 эт.">
                            </div>
                                                                                                            <div class="gallery-extended-img-frame js-gallery-extended-img-frame"
                                data-url="https://82.img.avito.st/image/1/1.FxTsvLayu_3aCznwzpQPYBMfu_tMHbk.Zlia85YBE1cV9PecIHPlu80mVncA2sv9Lc6STtcVKhA"
                                data-image-id="9830843082"
                                data-alt-urls="[&quot;https:\/\/82.img.avito.st\/image\/1\/1.FxTsvLayu_3aFXn4zpQPYBMfu_tMHbk.G-ppZhrjpZK5JGvxGvXgyH1M751WXIY_4W0LwVXIdRo&quot;]"
                                data-title="Комната 15 м² в 3-к., 2/5 эт.">
                            </div>
                                                                                                            <div class="gallery-extended-img-frame js-gallery-extended-img-frame"
                                data-url="https://88.img.avito.st/image/1/1.DRbe3Layof_oayPyyPQVYiF_ofl-faM.mcAnIXY4d2VFi8m4cuBRuKXpvXd02bHO7jQeq8STM68"
                                data-image-id="9830843088"
                                data-alt-urls="[&quot;https:\/\/88.img.avito.st\/image\/1\/1.DRbe3Layof_odWP6yPQVYiF_ofl-faM.ax8_I9ity9nC5FunCJ0vz_GEjbAze5AOBUCpkCPK19M&quot;]"
                                data-title="Комната 15 м² в 3-к., 2/5 эт.">
                            </div>
                                                                                                            <div class="gallery-extended-img-frame js-gallery-extended-img-frame"
                                data-url="https://89.img.avito.st/image/1/1.DBUCnLayoPw0KyLxFrQUYf0_oPqiPaI.ZEWCn5d7-UVDW6Y0Dsfa3GRaFkwcsuNNzZhJEpWyXMw"
                                data-image-id="9830843089"
                                data-alt-urls="[&quot;https:\/\/89.img.avito.st\/image\/1\/1.DBUCnLayoPw0NWL5FrQUYf0_oPqiPaI.eQezls51_E8Vpkdb6tTd9h8XNVD9UWCkEgGKUJeVRg0&quot;]"
                                data-title="Комната 15 м² в 3-к., 2/5 эт.">
                            </div>
                                                                                                            <div class="gallery-extended-img-frame js-gallery-extended-img-frame"
                                data-url="https://48.img.avito.st/image/1/1.dYxd3ray2WVraVtoO7d556J92WP9f9s.QBk-FPkYh1ESotBJzbi9YqanwDN-tOnv__daRin3JaA"
                                data-image-id="9798177448"
                                data-alt-urls="[&quot;https:\/\/48.img.avito.st\/image\/1\/1.dYxd3ray2WVrdxtgO7d556J92WP9f9s.JBSGNGtb0WMPS_ZuUp8axasD4_26SU23h4fhhw9R9f4&quot;]"
                                data-title="Комната 15 м² в 3-к., 2/5 эт.">
                            </div>
                                                                                                            <div class="gallery-extended-img-frame js-gallery-extended-img-frame"
                                data-url="https://47.img.avito.st/image/1/1.ehF5dbay1vhPwlT1ARx2eobW1v7Z1NQ.hwqLMPgrhMyFyfvKKHPSQXUW_m4JLkl95yj7xlIWEXk"
                                data-image-id="9798177447"
                                data-alt-urls="[&quot;https:\/\/47.img.avito.st\/image\/1\/1.ehF5dbay1vhP3BT9ARx2eobW1v7Z1NQ.ZHKvZnHYnK8Pq5bXRLbEV3VCvJwLyoWhc68K02rTOqQ&quot;]"
                                data-title="Комната 15 м² в 3-к., 2/5 эт.">
                            </div>
                                                                        </div>
                </div>
            </div>
        </div>
    </div>

                <div class="gallery-extended-list-wrapper js-gallery-extended-list-wrapper">
            <ul class="gallery-extended-list js-gallery-extended-list">
                                                                        <li class="gallery-extended-list-item  gallery-extended-list-item_selected js-gallery-extended-list-item" data-index="0" data-type="image">
                        <span class="gallery-extended-list-item-link" title="Комната 15 м² в 3-к., 2/5 эт. &#8212; фотография №1" style="background-image: url(https://09.img.avito.st/image/1/1.NOwmZrawmAUGxPTVdwNBXZDHkg8GxPQHkA.zlqUbquI8umwmsJvSqWDacLMbGdQvq8HslYjixuFniA);"></span>
                    </li>
                                                                                                <li class="gallery-extended-list-item  js-gallery-extended-list-item" data-index="1" data-type="image">
                        <span class="gallery-extended-list-item-link" title="Комната 15 м² в 3-к., 2/5 эт. &#8212; фотография №2" style="background-image: url(https://30.img.avito.st/image/1/1.I22en7awj4S-PeN6z_pW3Cg-hY6-PeOGKA.esBXVCJrNm8nyHl36HQTaTII6zmEa-gdmzvdLZZ7AG4);"></span>
                    </li>
                                                                                                <li class="gallery-extended-list-item  js-gallery-extended-list-item" data-index="2" data-type="image">
                        <span class="gallery-extended-list-item-link" title="Комната 15 м² в 3-к., 2/5 эт. &#8212; фотография №3" style="background-image: url(https://97.img.avito.st/image/1/1.ALvPr7awrFLvDcDqnsp1CnkOpljvDcBQeQ._JgScvp2LZ177FEluLmqnxiLeURW3huZ6sl6847q6zs);"></span>
                    </li>
                                                                                                <li class="gallery-extended-list-item  js-gallery-extended-list-item" data-index="3" data-type="image">
                        <span class="gallery-extended-list-item-link" title="Комната 15 м² в 3-к., 2/5 эт. &#8212; фотография №4" style="background-image: url(https://26.img.avito.st/image/1/1.JyPUILawi8r0guc8hUVSkmKBgcD0gufIYg.fREAdguXP18SZVi7RSYKgdUvLo48TGgv1t-k0Tq_adQ);"></span>
                    </li>
                                                                                                <li class="gallery-extended-list-item  js-gallery-extended-list-item" data-index="4" data-type="image">
                        <span class="gallery-extended-list-item-link" title="Комната 15 м² в 3-к., 2/5 эт. &#8212; фотография №5" style="background-image: url(https://82.img.avito.st/image/1/1.FxTsvLawu_3MHtdr0akmtlodsffMHtf_Wg.hMvz03T62PrKsKjwEYsJpS1rtiWUHhcZooEemYiSygw);"></span>
                    </li>
                                                                                                <li class="gallery-extended-list-item  js-gallery-extended-list-item" data-index="5" data-type="image">
                        <span class="gallery-extended-list-item-link" title="Комната 15 м² в 3-к., 2/5 эт. &#8212; фотография №6" style="background-image: url(https://88.img.avito.st/image/1/1.DRbe3Lawof_-fs1d48k8tGh9q_X-fs39aA.BXlvPtkaYhYrMJYGyQ2BU_W3xLyNZ3jtL1KvpiHvjtA);"></span>
                    </li>
                                                                                                <li class="gallery-extended-list-item  js-gallery-extended-list-item" data-index="6" data-type="image">
                        <span class="gallery-extended-list-item-link" title="Комната 15 м² в 3-к., 2/5 эт. &#8212; фотография №7" style="background-image: url(https://89.img.avito.st/image/1/1.DBUCnLawoPwiPsxcP4k9t7Q9qvYiPsz-tA.CEuQovsVlhNH4UZqb-h56gtKhY_I2h1DEQn6DoIRe-g);"></span>
                    </li>
                                                                                                <li class="gallery-extended-list-item  js-gallery-extended-list-item" data-index="7" data-type="image">
                        <span class="gallery-extended-list-item-link" title="Комната 15 м² в 3-к., 2/5 эт. &#8212; фотография №8" style="background-image: url(https://48.img.avito.st/image/1/1.dYxd3raw2WV9fLW3Id9bLut_0299fLVn6w.l0GtgXtaVzlUI4rWj3j68Q4GqHuy2gfEy23D8NVDABk);"></span>
                    </li>
                                                                                                <li class="gallery-extended-list-item  js-gallery-extended-list-item" data-index="8" data-type="image">
                        <span class="gallery-extended-list-item-link" title="Комната 15 м² в 3-к., 2/5 эт. &#8212; фотография №9" style="background-image: url(https://47.img.avito.st/image/1/1.ehF5dbaw1vhZ17o0BXRUs8_U3PJZ17r6zw.wZy9okzuHq4rE7jAQnDJTWWKftuoFmKC0gb2QkMf2dg);"></span>
                    </li>
                                                                    </ul>
        </div>

                <div class="gallery-extended-list-navigation gallery-extended-list-navigation_state-hide js-gallery-extended-list-navigation" data-dir="up">
            <span class="gallery-extended-list-navigation-icon"></span>
        </div>
        <div class="gallery-extended-list-navigation gallery-extended-list-navigation_state-hide gallery-extended-list-navigation_bottom js-gallery-extended-list-navigation_bottom" data-dir="bottom">
            <span class="gallery-extended-list-navigation-icon"></span>
        </div>
    
        </div>
                                                            </div>
                        
                        
                                                                            
                        
                                                    <!-- Эксперимент: TNS-1500 -->
                            <div class="seller-information-may-vary js-seller-information-may-vary">
                                                                    Арендодатель
                                                                    допускает неточности в описании объявлений
                            </div>
                        
                                                
                                                
                                                                            
    <div class="item-view-block">
        <div class="item-params">
                            <div class="item-params-title">
                    О комнате
                </div>
                        <ul class="item-params-list">
                                <li class="item-params-list-item">
                    <span class="item-params-label">Площадь комнаты: </span>15&nbsp;м²                </li>
                                <li class="item-params-list-item">
                    <span class="item-params-label">Комнат в квартире: </span>3                </li>
                                <li class="item-params-list-item">
                    <span class="item-params-label">Этаж: </span>2 из 5                </li>
                                <li class="item-params-list-item">
                    <span class="item-params-label">Тип дома: </span>Кирпичный                </li>
                            </ul>

                    </div>

        
            </div>
                        
                                                
                        
                        
                        
                        
                    
                    
                    
                    
                    
                                        
                                        
                    
                                            

    <div class="item-view-block item-view-map js-item-view-map" itemscope itemtype="http://schema.org/Place">
        <div class="item-map js-item-map">
                            <div class="item-map-title">
                    Расположение
                </div>
                        <div class="item-map-location">

                                
<div class="item-address">
        
        
    
                <div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><span class="item-address__string">
                Москва
            </span><div class="item-address-georeferences"><span class="item-address-georeferences"><span class="item-address-georeferences-item"><span class="item-address-georeferences-item-icons"><i class="item-address-georeferences-item-icons__icon"
                               style="background-color: #0072BA"></i></span><span class="item-address-georeferences-item__content">Семёновская</span></span><span class="item-address-georeferences-item"><span class="item-address-georeferences-item-icons"><i class="item-address-georeferences-item-icons__icon"
                               style="background-color: #0072BA"></i></span><span class="item-address-georeferences-item__content">Электрозаводская</span><span>,</span><span class="item-address-georeferences-item__after"> 900 м</span></span><span class="item-address-georeferences-item"><span class="item-address-georeferences-item-icons"><i class="item-address-georeferences-item-icons__icon"
                               style="background-color: #E42313"></i></span><span class="item-address-georeferences-item__content">Преображенская площадь</span><span>,</span><span class="item-address-georeferences-item__after"> 1,5 км</span></span></span></div></div>

</div>

                <div class="item-map-location__control">
                                    <div class="item-map-control">
                        <a data-text-open="Показать карту"
                           data-text-close="Скрыть карту"
                           class="item-map-slider-toggle js-item-map-slider-toggle item-map-slider-toggle_open">
                            Скрыть карту
                        </a>
                    </div>
                                </div>
            </div>
                            <div class="b-search-map expanded item-map-wrapper js-item-map-wrapper"
                    data-map-zoom="16"
                    data-map-lat="55.783195"
                    data-map-lon="37.719423"
                    data-map-type="dynamic"
                    data-item-id="1768618038"
                    data-location-id="637640"
                    data-category-id="23"
                    data-shop-id="">
                    <div class="search-map" id="search-map"></div>
                </div>
                    </div>
    </div>
                    
                    
                                            
<div class="item-view-block">
    <div class="item-description">
                    <div class="item-description-title">
                Описание
            </div>
                <div class="item-description-text" itemprop="description">
                            <p>Сдаю комнату в 3х комнатной квартире комната с балконом. Девушкам или женщинам!! Без залога!!! Читайте внимательно!!! На длительный срок!!!! Тихо, спокойно. Квартира  чистая, уютная, большая, вся необходимая мебель и техника имеется, также в большой комнате балкон, застеклен. Проживание без хозяев.   Порядочным и платежеспособным только девушкам и женщинам!!! Без вредных привычек!!!! Уважающих и понимающих чужой труд!!! Любящих чистоту и порядок!!! Уважающих личное пространство. Комната светлая , в квартире имеется всё для проживания. Стиральная машина, интернет,телевизор...Без детей  и животных. Район тихий спокойный, удобный транспорт 5мин.хотьбы МЦК. Рядом остановки на автобус до м. Семеновская, Перово, шоссе Энтузиастов.(10мин.)Электрозавдская 15 мин. Сокольники 20 мин. Всё в шаговой доступности. Бесплатная парковка. В 10мин.хотьбы замечательный Измайловский парк!!Ком. Услуги входят в стоимость. Студенты и семейные пары не беспокоить!!!!! Агенты и посредники не беспокоить!!!!!!!!</p>
                    </div>
    </div>
</div>
                    
                    
                                        
                                                                

                    
                    
                    
                                            <div
                            class="js-icebreakers__wrapper  icebreakers__wrapper_borderBottom "
                            data-icebreakers="&#x7B;&quot;id&quot;&#x3A;1768618038,&quot;contact&quot;&#x3A;&quot;&#x0421;&#x043F;&#x0440;&#x043E;&#x0441;&#x0438;&#x0442;&#x0435;&#x20;&#x0443;&#x20;&#x0430;&#x0440;&#x0435;&#x043D;&#x0434;&#x043E;&#x0434;&#x0430;&#x0442;&#x0435;&#x043B;&#x044F;&quot;,&quot;texts&quot;&#x3A;&#x5B;&#x7B;&quot;id&quot;&#x3A;10701281,&quot;messageText&quot;&#x3A;&quot;&#x0417;&#x0434;&#x0440;&#x0430;&#x0432;&#x0441;&#x0442;&#x0432;&#x0443;&#x0439;&#x0442;&#x0435;&#x21;&#x20;&#x0415;&#x0449;&#x0451;&#x20;&#x0441;&#x0434;&#x0430;&#x0451;&#x0442;&#x0435;&#x20;&#x043A;&#x043E;&#x043C;&#x043D;&#x0430;&#x0442;&#x0443;&#x3F;&quot;,&quot;previewText&quot;&#x3A;&quot;&#x0415;&#x0449;&#x0451;&#x20;&#x0441;&#x0434;&#x0430;&#x0451;&#x0442;&#x0435;&#x3F;&quot;&#x7D;,&#x7B;&quot;id&quot;&#x3A;10701282,&quot;messageText&quot;&#x3A;&quot;&#x0417;&#x0434;&#x0440;&#x0430;&#x0432;&#x0441;&#x0442;&#x0432;&#x0443;&#x0439;&#x0442;&#x0435;&#x21;&#x20;&#x0421;&#x043A;&#x0430;&#x0436;&#x0438;&#x0442;&#x0435;,&#x20;&#x0442;&#x043E;&#x0440;&#x0433;&#x20;&#x0443;&#x043C;&#x0435;&#x0441;&#x0442;&#x0435;&#x043D;&#x3F;&quot;,&quot;previewText&quot;&#x3A;&quot;&#x0422;&#x043E;&#x0440;&#x0433;&#x20;&#x0443;&#x043C;&#x0435;&#x0441;&#x0442;&#x0435;&#x043D;&#x3F;&quot;&#x7D;,&#x7B;&quot;id&quot;&#x3A;10701283,&quot;messageText&quot;&#x3A;&quot;&#x0417;&#x0434;&#x0440;&#x0430;&#x0432;&#x0441;&#x0442;&#x0432;&#x0443;&#x0439;&#x0442;&#x0435;&#x21;&#x20;&#x041A;&#x043E;&#x0433;&#x0434;&#x0430;&#x20;&#x043C;&#x043E;&#x0436;&#x043D;&#x043E;&#x20;&#x043F;&#x043E;&#x0441;&#x043C;&#x043E;&#x0442;&#x0440;&#x0435;&#x0442;&#x044C;&#x20;&#x043A;&#x043E;&#x043C;&#x043D;&#x0430;&#x0442;&#x0443;&#x3F;&quot;,&quot;previewText&quot;&#x3A;&quot;&#x041A;&#x043E;&#x0433;&#x0434;&#x0430;&#x20;&#x043C;&#x043E;&#x0436;&#x043D;&#x043E;&#x20;&#x043F;&#x043E;&#x0441;&#x043C;&#x043E;&#x0442;&#x0440;&#x0435;&#x0442;&#x044C;&#x3F;&quot;&#x7D;&#x5D;&#x7D;"
                            data-new-style="1">
                            <div class="icebreakers__wrapper">
                                Спросите у арендодателя
                                <div class="icebreakerBubble__wrapper">
                                                                            <button class="icebreaker__bubble">
                                            Ещё сдаёте?
                                        </button>
                                                                            <button class="icebreaker__bubble">
                                            Торг уместен?
                                        </button>
                                                                            <button class="icebreaker__bubble">
                                            Когда можно посмотреть?
                                        </button>
                                                                    </div>
                            </div>
                        </div>
                    
                                    </div>

                
                                    <div class="item-view-socials">
                        <div class="item-socials">
    <div class="item-socials-actions clearfix">
                    <div class="item-socials-share">
                

<div class="js-social-share social-share"
    data-services="vkontakte,odnoklassniki,facebook,twitter,moimir,lj"
    data-title="Объявление на Авито - Комната 15 м² в 3-к., 2/5 эт."
    data-description="Сдаю комнату в 3х комнатной квартире комната с балконом. Девушкам или женщинам!! Без залога!!! Читайте внимательно!!! На длит..."
    data-url="https://www.avito.ru/moskva/komnaty/komnata_15m_v_3-k._25et._1768618038"
    data-image="https://www.avito.ru/img/share/auto/12175596009">
</div>
            </div>
        
                    <div class="item-socials-abuse">
                <button class="js-abuse-button button button-origin">Пожаловаться</button>
                <input class="js-token" type="hidden" name="token[8994913488711]" value="1643220804.2400.8e694df294c8b2ce6aa869f64e46994d734f77a7e2e7f1cdc4828ddfa9f7ea8e">
                <div id="abuse" data-abuse=\'{"itemId":1768618038,"isAuth":true}\'  data-recaptcha-enabled="1"></div>
            </div>
            </div>
</div>
                    </div>
                
                
                
                
                            

            
                            <div class="item-view-similars">
                        <div class="similars js-similars similars_column-4"
        data-show-more-btn="1">
        <div class="similars-inner similars-inner_hidden js-similars-inner">
            <div
                class="similars-list js-similars-list"
                data-serp-link=""
                data-serp-link-text=""
                data-from-page="item"
                >
            </div>
        </div>
    </div>
                </div>

                                    </div>

        <div class="item-view-content-right">
            <div class="item-view-info js-item-view-info js-sticky-fallback  ">
                                        <div class="item-view-contacts js-item-view-contacts">
                                                                                    
        <div class="item-view-price js-item-view-price">
            <div class="item-view-price-content js-item-view-price-content">
                <div class="item-price">
    <div class="item-price-wrapper">
                



<div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="price-value price-value_side-card" id="price-value">
    <span class="price-value-string js-price-value-string">
            <meta itemprop="availability" content="https://schema.org/LimitedAvailability" />
    <meta itemprop="priceCurrency" content="RUB" />
            <span class="price-value-main"><span class="js-item-price" itemprop="price" content="17000">17 000</span>&nbsp;₽
                <span class="price-value-additional">
                     в месяц
                                                        </span></span>    
    </span>

    </div>

                    </div>

        
                <div class="item-price-sub-price">
            без&nbsp;залога
        </div>
    
        
    </div>

                                    <div class="item-price-banner js-item-price-banner">
                        <div class="item-price-banner-holder">
                            

    <div class="avito-ads-container avito-ads-container_btni">
        <div id="template_btni" class="avito-ads-template">
            <div class="item-price-banner-content js-item-price-banner-content">
            </div>
        </div>
    </div>
                        </div>
                    </div>
                
                
                            </div>
        </div>
    
                            <script nonce="mhoCYK+FKeM88AwX4bYjRw==">
                                const titleNode = document.querySelector(\'.js-item-view-title-info\');
                                const priceBlockNode = document.querySelector(\'.js-item-view-price\');
                                const priceContentNode = document.querySelector(\'.js-item-view-price-content\');

                                const minPriceBlockHeight = titleNode.offsetHeight - 24;
                                const priceContentHeight = priceContentNode.offsetHeight;
                                const fixHeight = priceContentHeight > minPriceBlockHeight ?
                                    priceContentHeight : minPriceBlockHeight;

                                priceBlockNode.style.height = fixHeight  + \'px\';
                            </script>
                                                            <div
                                    class="js-item-price-buyer-style-calculate"
                                    data-browser="Chrome">
                                </div>
                                                                                                        


    <div class="item-view-actions  ">
        <div class="item-actions js-item-actions">
            
            
            
            
                                            
                                                            <div class="item-actions-line">
                                                                                        

                                
    

    







    <div class="item-phone js-item-phone"
                data-disabled="">
        <div class="item-phone-number js-item-phone-number greenContact_color ">
                <a
        class="button item-phone-button js-item-phone-button button-origin contactBar_greenColor button-origin_full-width button-origin_large-extra item-phone-button_hide-phone item-phone-button_card js-item-phone-button_card contactBar_height"
        href="javascript:void(0);"
        data-marker="item-phone-button/card"
        data-side="card" data-privet="privet">
                                                Показать телефон
                                    <span class="item-phone-button-sub-text">8 916 XXX-XX-XX</span>
                                        
    </a>

                
            <div class="js-verification-sdk-web-limits"></div>
        </div>

                                <div class="item-popup-content js-item-phone-popup-content">
                                                <div>
                <div class="item-phone-big-number js-item-phone-big-number">
                                    </div>
                <div class="item-phone-call-note">
                    Номер защищён: смс и сообщения в Viber, WhatsApp и других мессенджерах не будут доставлены.
                </div>
                <div class="item-phone-seller-wrap">
                    <div class="item-phone-seller-info">
                        


<div class="seller-info  js-seller-info ">
            <div class="seller-info-prop   js-seller-info-prop_seller-name seller-info-prop_layout-two-col">
            <div class="seller-info-col">                <div class="seller-info-value">
                    <div data-marker="seller-info/name" class="seller-info-name js-seller-info-name">
                                                        <a href="https://www.avito.ru/user/2f209f846fa6ad260914e8b69fc20a15533cbca360c846b89ac2871f21ba2517/profile?id=1768618038&src=item&page_from=from_item_card&iid=1768618038" title="Нажмите, чтобы перейти в профиль">
        Юлия
    </a>
                                            </div>

                                    </div>

                                    <div data-marker="seller-info/label">Арендодатель</div>
                
                <div class="seller-info-value">
                                                                <div>
                            На Авито c июня 2018                        </div>
                    
                                                                <div>
                            Завершено 6 объявлений
                        </div>
                                    </div>
            </div>
                                                                    <div class="seller-info-avatar js-seller-info-avatar ">
                                                    <a
                                class="seller-info-avatar-image  js-public-profile-link"
                                href="https://www.avito.ru/user/2f209f846fa6ad260914e8b69fc20a15533cbca360c846b89ac2871f21ba2517/profile?id=1768618038&src=item&page_from=from_item_card_icon&iid=1768618038"
                                title="Нажмите, чтобы перейти в профиль"style="background-image: url(\'https://www.avito.st/stub_avatars/%D0%AE/1_256x256.png\')">Профиль</a>
                                            </div>
                                    </div>
    
    
                        
        
    
    <span
        class="seller-info-timing"
        elementtiming="tns.seller-info">
        <span class="seller-info-timing_content">timing</span>
    </span>
</div>
                    </div>
                                            <button class="js-item-phone-abuse-button item-phone-abuse-button button button-origin">Пожаловаться</button>
                                    </div>
                <div class="js-item-phone-autoteka"></div>
            </div>
            
                            </div>
            </div>
                                                    </div>

                                            
                                            <div class="item-actions-line">
                                                        
    
    

<span class="js-messenger-button"
    data-props="&#x7B;&quot;isOpenInNewTab&quot;&#x3A;null,&quot;isAuthorized&quot;&#x3A;true,&quot;isFullWidth&quot;&#x3A;true,&quot;isMiniMessenger&quot;&#x3A;true,&quot;isIconButton&quot;&#x3A;null,&quot;isHidden&quot;&#x3A;null,&quot;parentSelector&quot;&#x3A;null,&quot;hasHidePhoneOnboarding&quot;&#x3A;false,&quot;sellerIdHash&quot;&#x3A;138191850,&quot;itemId&quot;&#x3A;1768618038,&quot;itemUrl&quot;&#x3A;&quot;&#x5C;&#x2F;moskva&#x5C;&#x2F;komnaty&#x5C;&#x2F;komnata_15m_v_3-k._25et._1768618038&quot;,&quot;itemCVViewed&quot;&#x3A;null,&quot;categoryId&quot;&#x3A;23,&quot;buttonText&quot;&#x3A;&quot;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x041D;&#x0430;&#x043F;&#x0438;&#x0441;&#x0430;&#x0442;&#x044C;&#x20;&#x0441;&#x043E;&#x043E;&#x0431;&#x0449;&#x0435;&#x043D;&#x0438;&#x0435;&#x5C;n&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&#x20;&quot;,&quot;replyTime&quot;&#x3A;&quot;&#x041E;&#x0442;&#x0432;&#x0435;&#x0447;&#x0430;&#x0435;&#x0442;&#x20;&#x043E;&#x043A;&#x043E;&#x043B;&#x043E;&#x20;&#x0447;&#x0430;&#x0441;&#x0430;&quot;,&quot;logParams&quot;&#x3A;&#x7B;&quot;userId&quot;&#x3A;90200356,&quot;wsrc&quot;&#x3A;&quot;item&quot;,&quot;s&quot;&#x3A;&quot;mi&quot;&#x7D;,&quot;experiments&quot;&#x3A;&#x7B;&quot;item&quot;&#x3A;&#x5B;&#x5D;&#x7D;,&quot;side&quot;&#x3A;&quot;card&quot;,&quot;contactlessView&quot;&#x3A;null&#x7D;"
    data-marker="messenger-button"
    data-side="card" >
    <a
        class="button button-origin contactBar_blueColor button-origin_full-width button-origin_large-extra"
                style="padding: 23px 17px;">
                                            Написать сообщение
                            
    </a>
</span>
                        </div>
                                                
            
            
                    </div>
    </div>
                        
                        <div class="item-view-seller-info js-item-view-seller-info ">
                            


<div class="seller-info  js-seller-info ">
            <div class="seller-info-prop   js-seller-info-prop_seller-name seller-info-prop_layout-two-col">
            <div class="seller-info-col">                <div class="seller-info-value">
                    <div data-marker="seller-info/name" class="seller-info-name js-seller-info-name">
                                                        <a href="https://www.avito.ru/user/2f209f846fa6ad260914e8b69fc20a15533cbca360c846b89ac2871f21ba2517/profile?id=1768618038&src=item&page_from=from_item_card&iid=1768618038" title="Нажмите, чтобы перейти в профиль">
        Юлия
    </a>
                                            </div>

                                    </div>

                                    <div data-marker="seller-info/label">Арендодатель</div>
                
                <div class="seller-info-value">
                                                                <div>
                            На Авито c июня 2018                        </div>
                    
                                                        </div>
            </div>
                                                                    <div class="seller-info-avatar js-seller-info-avatar ">
                                                    <a
                                class="seller-info-avatar-image  js-public-profile-link"
                                href="https://www.avito.ru/user/2f209f846fa6ad260914e8b69fc20a15533cbca360c846b89ac2871f21ba2517/profile?id=1768618038&src=item&page_from=from_item_card_icon&iid=1768618038"
                                title="Нажмите, чтобы перейти в профиль"style="background-image: url(\'https://www.avito.st/stub_avatars/%D0%AE/1_256x256.png\')">Профиль</a>
                                            </div>
                                    </div>
    
    
                        
        
    
    <span
        class="seller-info-timing"
        elementtiming="tns.seller-info">
        <span class="seller-info-timing_content">timing</span>
    </span>
</div>
                        </div>

                                                                            <div class="item-view-search-info-redesign">
                                <span data-marker="item-view/item-id">№ 1768618038</span>
                                                                    , 


<div class="title-info-metadata-item title-info-metadata-views">
    <i class="title-info-icon-views"></i>21582 (+454)</div>
                                                            </div>
                        
                        
                        <span
                            class="item-view-timing"
                            elementtiming="bx.contacts">
                            <span class="item-view-timing_content">timing</span>
                        </span>
                    </div>

                            </div>
                            <div class="item-view-ads">
                    


                    


                    <div class="avito-ads-tgb2-sticky-container">
                        

                    </div>
                </div>
                                </div>
    </div>

            <div class="item-view-low-ads">
            

    <div class="avito-ads-container avito-ads-container_ldr_low">
        <div id="template_ldr_low" class="avito-ads-template">
            <div class="item-view-ads-ldr_low avito-ads-content">
            </div>
        </div>
    </div>
        </div>
    </div>

<div class="slide-alert js-slide-alert">
    </div>

<div class="item-tooltip js-item-tooltip tooltip tooltip_bottom">
    <i class="item-tooltip-arrow js-item-tooltip-arrow tooltip-arrow"></i>
    <div class="item-tooltip-content js-item-tooltip-content tooltip__content"></div>
</div>

<div class="item-tooltip js-buy-cv-tooltip tooltip tooltip_bottom">
    <i class="item-tooltip-arrow js-item-tooltip-arrow tooltip-arrow"></i>
    <div class="item-tooltip-content js-item-tooltip-content tooltip__content"></div>
</div>

<div class="item-tooltip js-download-resume-tooltip tooltip tooltip_bottom">
    <i class="item-tooltip-arrow js-item-tooltip-arrow tooltip-arrow"></i>
    <div class="item-tooltip-content js-item-tooltip-content tooltip__content"></div>
</div>


    <div class="js-social-proof-toast-container" data-props="null"></div>


<script nonce="mhoCYK+FKeM88AwX4bYjRw==" type="text/template" id="js-cookie-support">
    <div class="cookie-support-icon"></div>
    <div class="cookie-support-title">Произошла ошибка</div>
    <div class="cookie-support-body">Для продолжения работы включите поддержку cookies<br>в&nbsp;настройках вашего браузера.</div>
    <button type="button" class="button button-origin js-reload-page">Я включил поддержку cookies</button>
</script>

            </div>

                    <div
                class="js-footer-app layout-internal col-12"
                data-source-data=\'&#x7B;&quot;luri&quot;&#x3A;&quot;moskva&quot;,&quot;countrySlug&quot;&#x3A;&quot;rossiya&quot;,&quot;supportPrefix&quot;&#x3A;&quot;https&#x3A;&#x5C;&#x2F;&#x5C;&#x2F;support.avito.ru&quot;,&quot;siteName&quot;&#x3A;&quot;&#x0410;&#x0432;&#x0438;&#x0442;&#x043E;&quot;,&quot;city&quot;&#x3A;null,&quot;mobileVersionUrl&quot;&#x3A;&quot;m.avito.ru&#x5C;&#x2F;moskva&#x5C;&#x2F;komnaty&#x5C;&#x2F;komnata_15m_v_3-k._25et._1768618038&#x3F;nomobile&#x3D;0&quot;,&quot;isShopBackground&quot;&#x3A;null,&quot;isShopPlank&quot;&#x3A;null,&quot;isCompanyPage&quot;&#x3A;false,&quot;isTechPage&quot;&#x3A;false,&quot;isBrowserMobile&quot;&#x3A;false&#x7D;\'>
            </div>

                        </div>

        
    <div id="counters-invisible" class="counters-invisible">
                                
    <noscript>
        <img src="/stat/u?1643220804" alt=""/>
    </noscript>

    <script nonce="mhoCYK+FKeM88AwX4bYjRw==">
        var ci_id = 1768618038, ci_location = 637640, ci_category = 23, ci_root_category = 4;
    </script>

    <script nonce="mhoCYK+FKeM88AwX4bYjRw==">
                    if (window.devicePixelRatio > 1) {
                avito.tracking.trackGTMEvent(\'tracking\', \'retina\', \'item\');
            }
            </script>

<script nonce="mhoCYK+FKeM88AwX4bYjRw==">
                                    if (avito.tracking && avito.tracking.initCriteo) {
        var isRealty = true;
        avito.tracking.initCriteo(isRealty ? [10019, 39534] : 39534, 90200356, "8c1f78f7962cca1a6ba48470a88cfc14");
    }
                                
    
        if (avito.abFeatures.isCriteoTestTransactionsDefaultGroup) {
            avito.tracking.trackCriteo(
                { event: "viewItem", avito: "1", item: "1768618038", user_segment: 5 }
            );
        } else if (avito.abFeatures.isCriteoTestTransactionsPushRecGroup) {
            avito.tracking.trackCriteo(
                { event: "viewItem", avito: "1", item: "1768618038", user_segment: 6 }
            );
        } else if (avito.abFeatures.isCriteoTestTransactionsPushMoreAutoGroup) {
            avito.tracking.trackCriteo(
                { event: "viewItem", avito: "1", item: "1768618038", user_segment: 7 }
            );
        } else if (avito.abFeatures.isCriteoTestTransactionsPushMLBlendGroup) {
            avito.tracking.trackCriteo(
                { event: "viewItem", avito: "1", item: "1768618038", user_segment: 8 }
            );
        } else if (avito.abFeatures.isCriteoTestTransactionsPushMoreAutoWithMLBlendGroup) {
            avito.tracking.trackCriteo(
                { event: "viewItem", avito: "1", item: "1768618038", user_segment: 9 }
            );
        } else {
            avito.tracking.trackCriteo(
                { event: "viewItem", avito: "1", item: "1768618038" }
            );
        }

                    avito.tracking.trackCriteoTransaction = function(idPrefix) {
                if (!idPrefix) {
                    return;
                }
                var utmFromCookie = document.cookie.match(/_utmz=[^;]*utmcsr=([^|]*)/);
                var utmFromQueryString = location.href.indexOf(\'utm_source=criteo\') !== -1 ? 1 : 0;
                if (utmFromCookie && utmFromCookie.length) {
                    utmFromCookie = utmFromCookie.pop();
                }

                const criteoEvents = [{
                    event: "manualDising"
                }];

                if (
                    avito.abFeatures.isCriteoTestTransactionsPushRecGroup
                    ||  avito.abFeatures.isCriteoTestTransactionsPushMoreAutoGroup
                    ||  avito.abFeatures.isCriteoTestTransactionsPushMLBlendGroup
                    ||  avito.abFeatures.isCriteoTestTransactionsPushMoreAutoWithMLBlendGroup
                ) {
                    criteoEvents.push({
                        event: "setAccount",
                        account: [39534, 28472]
                    });
                }

                criteoEvents.push({
                    event: "trackTransaction",
                    id: [idPrefix, Math.floor(Math.random()*99999999999)].join(\'_\'),
                    deduplication: utmFromQueryString || Number(utmFromCookie === \'criteo\'),                     item: [{
                        id: "1768618038",
                                                price: 17000.00,
                        quantity: 1
                    }]
                });

                avito.tracking.trackCriteo(...criteoEvents);
            };
            
                
        var _comscore = _comscore || [];
        _comscore.push({ c1: "2", c2: "9829393", c4: document.location.href, c5: "nedvizhimost" });
        (function() {
            var s = document.createElement("script"), el = document.getElementsByTagName("script")[0]; s.async = true;
            s.src = (document.location.protocol == "https:" ? "https://sb" : "http://b") + ".scorecardresearch.com/beacon.js";
            el.parentNode.insertBefore(s, el);
        })();
        
</script>

    <noscript>
        <img src="https://sb.scorecardresearch.com/p?c1=2&c2=9829393&cv=2.0&c4=avito.ru/moskva/komnaty/komnata_15m_v_3-k._25et._1768618038&c5=nedvizhimost&cj=1" />
    </noscript>

                
    <script nonce="mhoCYK+FKeM88AwX4bYjRw==">
        var img = new Image();
        img.src = \'//www.tns-counter.ru/V13a***R>\' + document.referrer.replace(/\* /g,\'%2a\') + \'*avito_ru/ru/CP1251/tmsec=avito_4-23/\' + Math.round(Math.random() * 1000000000);
    </script>
    <noscript><img src="//www.tns-counter.ru/V13a****avito_ru/ru/CP1251/tmsec=avito_4-23/" width="1" height="1" alt="" /></noscript>

<script nonce="mhoCYK+FKeM88AwX4bYjRw==" type="text/javascript">
    window.avito = window.avito || {};
</script>

                <!-- Yandex.Metrika counter -->
    <script nonce="mhoCYK+FKeM88AwX4bYjRw==">
        (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

        ym(34241905, "init", {
            id:34241905,
                            webvisor:true,
                        clickmap:true,
            trackLinks:true,
            accurateTrackBounce:true,
            ecommerce:"dataLayer",
            params: {
                abFeatures: {"ad_splitter":"one","desktopPublishFromSerp":"test","dynamic_an_sx_design_web":"test","currency_price_Transport":"test2","online_booking_car":"test","Salary_suggest_item_add":"folks_added"}
            }
        });
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/34241905" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->

            <script nonce="mhoCYK+FKeM88AwX4bYjRw==">var google_conversion_id = 987009030;
    var google_conversion_label = "f8JaCLLjvAQQhqDS1gM";
    var google_custom_params = window.google_tag_params;
    var google_remarketing_only = true;</script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="//www.googleadservices.com/pagead/conversion.js"></script>
<noscript>
    <div style="display:inline;">
        <img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/987009030/?value=0&amp;label=f8JaCLLjvAQQhqDS1gM&amp;guid=ON&amp;script=0"/>
    </div>
</noscript>
            </div>

                        <script async>
        function loadSecuredTouchScript() {
            var scriptElement = document.createElement(\'script\');
            scriptElement.setAttribute(\'id\', \'stPingId\');
            scriptElement.setAttribute(\'nonce\', \'mhoCYK+FKeM88AwX4bYjRw==\');
            scriptElement.setAttribute(\'src\', \'https://static.securedtouch.com/sdk/securedtouch-sdk-4.1.1w.js?appId=avito&sessionId=2orc7ssi.he63ew.ymo0k4bze2g0\');
            document.head.appendChild(scriptElement);
        }
        function SecuredTouchTokenReadyEvent(callback) {
            if (window[\'_securedTouchToken\']) {
                callback();
            }
            document.addEventListener(\'SecuredTouchTokenReadyEvent\', callback);
        }
        function onSecuredTouchReady(callback) {
            if (window[\'_securedTouchReady\']) {
                callback();
            } else {
                document.addEventListener(\'SecuredTouchReadyEvent\', callback);
            }
        }

        window.addEventListener(\'load\', function() {
            setTimeout(function(){
                function interactiveTimeHandler(){
                    clearTimeout(timerId);
                    loadSecuredTouchScript();
                }

                var timerId = setTimeout(function(){
                    window.removeEventListener(\'interactivetime\', interactiveTimeHandler);
                    loadSecuredTouchScript();
                }, 20 * 1000);

                window.addEventListener(\'interactivetime\', interactiveTimeHandler);
            }, 0)
        });
        onSecuredTouchReady(function () {
            _securedTouch.init({
                url: "https://avito.securedtouch.com",
                appId: "avito",
                appSecret: "pYmrImQN8QLjIJ6SYznJXLyM",
                userId: "ac7e05d6d9648d72d8d17cdf9951527f",
                sessionId: "2orc7ssi.he63ew.ymo0k4bze2g0",
                isDebugMode: false,
            });
        });
        SecuredTouchTokenReadyEvent(function () {
            if (window.avito && window.avito.cookie && window.avito.cookie.set) {
                window.avito.cookie.set(\'st\', window[\'_securedTouchToken\'], 60*60*24);
            }
        });
    </script>


            <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/2dcd566f4e006467d685.js" async></script>
    
    
            
    
            <div
            id="js-auth"
            data-use-auth-composition-service-login-endpoints=""
            data-is-new-restore-api="1"
            data-captcha-enabled="1"
            data-captcha-type="hCaptcha"></div>
    
            <div class="js-after-login-notification"></div>
    
    <input type="hidden" class="js-token" name="token[8994913488711]" value="1643220804.2400.8e694df294c8b2ce6aa869f64e46994d734f77a7e2e7f1cdc4828ddfa9f7ea8e">

            <div class="js-popup-app" data-uid="90200356"></div>
    
            <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/deps/object-assign/4.1.1/prod/web/main.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/deps/react/16.14.0/prod/web/main.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/deps/scheduler/0.19.1/prod/web/main.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/deps/react-dom/16.14.0/prod/web/main.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/deps/prop-types/15.8.1/prod/web/main.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/deps/react-popper/1.3.11/prod/web/main.js" ></script>

                    <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/9aea22ce.7585c689c77183317e6e.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/6347c711.73f02354611f19130e1f.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~c6cf528e4501db21b258d7132506b957.7c2d398bd918ff2de26e.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/4976136ab8f0d1107396.js" ></script>
        
        
                <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~a2d3cceca3b7a99aec402f4274daae86~b6c9dd376cf6242aa66aaf78a98e7cd5.71d836396f70e0b5a885.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~b6c9dd376cf6242aa66aaf78a98e7cd5.887e00b503b0ec45a7ae.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/7c9b883b969a24590ca1.js" ></script>

            
                <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~3aed0e1873f8a9e1501f0b0b51f4c086~91ac0639b450d3cf68aad1b462aa0300~96d322aa0de504f4cdc0ae9171~dbd13c97.1f2d70f622317744409d.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/6b60531aca3b293e53d3.js" ></script>

        
        
                    <link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/3f4a8c7ad6a0d5f75b4c.css">
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/a81ded65.cabdfa40d2c5564077aa.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/e41fb864.2791d4694550c93be4a3.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~01bb0b00e760324b6501763d873c732f~02e85fbca9e338a5f86022d293e7ef94~08e8c2d7129f75c1627360bcd3~c7f5d2dc.2fd89e1149e7a5565b0e.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~939a0d94bbc91af8d638b39ad322496d.f3c321b24ed335adf2a7.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/e5a234afa49f1ea4e92f.js" ></script>
        
                    <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/ed4049507904637729eb.js" ></script>
        
        <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/c618e425ab0da51a1f03.js" ></script>

        <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~0cfd78a1f3e9f8e1dcd0b2504003e7bd~195d8f84988bf66a715829b2d3c62853~1b2b00a60cd03ffec338ef0b0a~768c21ae.c42dbb9f082974b49bd8.js" defer></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~1b2b00a60cd03ffec338ef0b0ac78b8e~31040a7aac027533fb02471c77e63023.1bb03f2694b0a81dc25c.js" defer></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~31040a7aac027533fb02471c77e63023.6db82dfe42b7fd886d51.js" defer></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/6c57310251fba488eb9d.js" defer></script>

                    <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~f683120034ffac843ed686125e86e410.66478a514217c5da8446.js" defer></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/39d3c0e2f7b1c6f6c4cd.js" defer></script>
        
        <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/66f046f89483a364c42c.js" defer></script>

        <link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/c958216ded9b7778bb98.css">
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/8389c2e0.b8ae1aad7f0c4998567c.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/7cc04b9d.179795ee10d4c6b80c01.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/04b08762.952478a46431e91affb3.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/9b1ecd3b.21ec0409300822b552d2.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~6d1ff0835052ef24d1cee868ea965cf7~87ecf4b3ce8a5dbfd7363ef20d2f928e~99543f3a5e776e5ddac31dc112~5ab99612.4acb2b5e425cd7628b9a.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~87ecf4b3ce8a5dbfd7363ef20d2f928e.2589b4dc3849cb73816b.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/ef3ff31529df40c8dc73.js" ></script>

        <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/6b60a8c2.9a8a8bef51baa706cc5f.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/e4a9664a.24c7deedd57d62f0d631.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/b95768c2.2c618abe734b64a6f872.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~bebc26f26cd0f589d3fa91a577e91571.afafed200144cf96e1c2.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/c63904821df5102b631c.js" ></script>
    

    <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~1e6ffda9acd9ef698cc242384ac53839~2aa3a87bb8bf177a5ea78ae155203f78~64287ebed9c63b5ae76193114f~983ccc46.7377c9b7bfd7c12e54b4.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/244f7d78ced61eaebc8c.js" ></script>

        
    
    <script nonce="mhoCYK+FKeM88AwX4bYjRw==">
        var itemMainNode = document.querySelector(\'.item-view-main\');
        var itemContactsNode = document.querySelector(\'.item-view-contacts\');

                    if (itemMainNode && itemContactsNode) {
                var contactsHeight = itemContactsNode.clientHeight;
                itemMainNode.style.minHeight = contactsHeight + \'px\';
            }
            </script>

    
        
        <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://www.avito.st/s/cc/raw/62cdb23e8a54996f6e27bf1ec11b5e89.js" ></script>

                <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/default~e9b4dd44143228a00f29c2921d0493dd~faf7926578c7e3306d7cb1609080046c.d8cde553409837341d64.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/9ffbc8ad9bba569ad78f.js" ></script>

            
    <link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/f58b7e748633a6c52ef8.css">
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/e69ff198.727c9367ddb8c08de434.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/82a23634.471c586eb2089abc6436.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/87d0a4a0.3576b86742d6b2332f0b.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/2cfca065.2baa604326bd588fd992.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/db5cd265.dc36a18443df5972e0cc.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/7c30ebdf.88563f9f3195c49c183f.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/84d0c675.cc3dcdcd47c1f4c76683.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/6fe0b9a2.d0658d677a2e88d4bdcf.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/d968cbe6.6a94a3532a0e4ce35186.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/10c5e866.f2252313e92a257b96b4.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/773c85f5.86a54ee5bdd5ace60824.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~38519037017eb3cb5ae72302e7595f19~41589ac41aa4f88989b8aa49cf110b40~5f5005ad3a3dfe086995859dee~c98e5923.baa095903e0d1c9c8679.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~38519037017eb3cb5ae72302e7595f19~41589ac41aa4f88989b8aa49cf110b40~5f5005ad3a3dfe086995859dee~94f47958.fdcfc1b945156e78080a.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~38519037017eb3cb5ae72302e7595f19~41589ac41aa4f88989b8aa49cf110b40~5f5005ad3a3dfe086995859dee~dcf402c7.b9e6a03fb5fac409dfa8.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/default~38519037017eb3cb5ae72302e7595f19~41589ac41aa4f88989b8aa49cf110b40~5f5005ad3a3dfe086995859dee~ba477ea7.9ae0cd93688a45541d88.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/default~38519037017eb3cb5ae72302e7595f19~41589ac41aa4f88989b8aa49cf110b40~5f5005ad3a3dfe086995859dee~f8af4146.52eb393194dc5f5d50e2.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/72f48d9853229eaced32.js" ></script>

    <link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/5f7505baa425667160ee.css">
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~38519037017eb3cb5ae72302e7595f19~b0efd46295e6ed764f33dea34194ff15~cccf472d3f069fc8b5f53652696e79f5.7f5a63a82958ed11d9f3.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/d13bedb2a612405f5b39.js" ></script>

            <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/b57e8dbdabec35adc339.js" ></script>
        <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/7b1f75c274b0c9910579.js" ></script>

        
        
        
        
        
            
    
    
    
            <link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/a4036674f3a491aeadc6.css">
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/6128ba32.ba365123577b9a68dcda.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/9bcc8aa8.fad6c912cadbde3b392d.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/431f429f.6fa28f51fafe700b4c43.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~3d7a7ca9000594cad41b161a2a6dca19.c4f5d19cb127785ca81b.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/7e94a0eb79f245e0f3ef.js" ></script>
    
    <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/fc758a31c2c5d38c5f01.js" ></script>

                        <link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/b922c4f6d09d19b7370e.css">
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/b8b80a84.2e964ae75baa5e671e88.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~02e85fbca9e338a5f86022d293e7ef94~08e8c2d7129f75c1627360bcd3f9287c~0c5d337047be3bf395777dff81~67648541.637c678ad77b65beff8a.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~02e85fbca9e338a5f86022d293e7ef94~a2df7c0a7350dbb731d49c78c7bd1295~a75e5db455c493308cfb21fde3~064f6e35.5e4f6a323884e2075f77.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~b7408dc2edd5ed2bdcb784152a2ac6e7~ee2e562a6e90eb37207b004e8d45f1eb.90aaf136965791ced306.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~b7408dc2edd5ed2bdcb784152a2ac6e7.9c09db3d3223d33823f3.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/default~08e8c2d7129f75c1627360bcd3f9287c~a2df7c0a7350dbb731d49c78c7bd1295~a75e5db455c493308cfb21fde3~9b5b5e1b.8fad5d4daf11b97940d0.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/4c2bc7307a42cf46c213.js" ></script>
            
    <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/05199ef3c13e2e54f434.js" ></script>

    
            <link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/08dd8ee5f57a6ea6daca.css">
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~96718df57bc88b38ac5ae5ebf51467da.ab1109ad6459c42b2091.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/0f29a36e2bcb967d414e.js" ></script>
    
            
        
        
        
        
            
            
            
    
    
    
            <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/@avito/au-discount/1.0.0/prod/web/bundles/6ce2928fb77334bfa97c.js" ></script>
    
    
                        <link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/1611665be17ccbe87325.css">
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~0c5d337047be3bf395777dff811bf840~350ee0a4aed09600712140245cd11992~a0a3729a8c6400b87358f7fd95e5cb55.b397fd5188248c06a5a8.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~a0a3729a8c6400b87358f7fd95e5cb55.98db84bf85e5025f6098.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/default~a0a3729a8c6400b87358f7fd95e5cb55~a2df7c0a7350dbb731d49c78c7bd1295.e664219a6e7d03e47297.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/ff0caf8cf992625d068d.js" ></script>
            
            <link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/6b5a3bfb9053579cc123.css">
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/766005f3.ea1bc47a8f576147c86e.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~024ac29525af4f19d771909cd044d250~02e85fbca9e338a5f86022d293e7ef94~0cfd78a1f3e9f8e1dcd0b25040~d00e4038.5695a494ada67ce42935.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~024ac29525af4f19d771909cd044d250~02e85fbca9e338a5f86022d293e7ef94~0cfd78a1f3e9f8e1dcd0b25040~dd097cf6.1bba761a01b1b57a9d4e.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~024ac29525af4f19d771909cd044d250~02e85fbca9e338a5f86022d293e7ef94~0cfd78a1f3e9f8e1dcd0b25040~4478d2c3.20d504425ec684f8eec4.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~02e85fbca9e338a5f86022d293e7ef94~3bc3062e9edbad2b932d96427bacf10a~504215414541d1b0bb5b1196fc~e23fc7e4.7910a8507872b8cd9dff.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~504215414541d1b0bb5b1196fc5f0473.d9f58b284bc742105619.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/default~504215414541d1b0bb5b1196fc5f0473~a246584fcd4684e5837aee804096020f~a2df7c0a7350dbb731d49c78c7bd1295.9e49db316a6cc0748e46.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/de1379098b99ffc9d48a.js" ></script>
    
            <link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/314bb5eb4dac46107ded.css">
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~76fa9fb9dc1a310f3c3afdd4d891eb0d.879335c85630e1c279f4.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/4566ddd644d7e8cd3efd.js" ></script>
    
        
    
    
    
    
            <link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/7f5e13adbe6e530c9c31.css">
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/fb3467b1.2104224e1d3527b4b2c0.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/9c4bffd4.de609a48e1ea27cd5645.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~02e85fbca9e338a5f86022d293e7ef94~0cfd78a1f3e9f8e1dcd0b2504003e7bd~195d8f84988bf66a715829b2d3~999a688b.fda2c4e0fb4c8660848a.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~02e85fbca9e338a5f86022d293e7ef94~0cfd78a1f3e9f8e1dcd0b2504003e7bd~195d8f84988bf66a715829b2d3~138e29db.db2f02179658e6ea59f0.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~02e85fbca9e338a5f86022d293e7ef94~30d786c2ae4a742f91b0c6189fc1865c~54b31601e2cbc55e32f5a81dc1~a4aafb2a.09456edeb6771f531b02.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~02e85fbca9e338a5f86022d293e7ef94~54b31601e2cbc55e32f5a81dc1163a87~8672419d283bbac19a0fb976ab~29567586.5ff49d2a3a445a8e537b.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~02e85fbca9e338a5f86022d293e7ef94~a75e5db455c493308cfb21fde32e3036~e77b18ecc8cbf660b10629f6a0c4a6fd.c640d151c5a66d16f6da.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~e77b18ecc8cbf660b10629f6a0c4a6fd.38132454d8e8edececc6.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/d46da361965372234b03.js" ></script>
    
            <link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/0e8e066ed39a07203f23.css">
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/6ab72189.81e7567db2988f253ffd.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/chunks/vendors~31116d1f2bf0720c8349ccd6944a0dc8.43d98f63b85d979d7096.js" ></script>
<script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/3951c162d261b6582b80.js" ></script>
    
                                <div class="js-mini-messenger" data-marker="mini-messenger"></div>
            <script nonce="mhoCYK+FKeM88AwX4bYjRw==" src="https://static.avito.ru/s/cc/bundles/92de6b7bd7e6879cd821.js" defer></script>
            
    <script nonce="mhoCYK+FKeM88AwX4bYjRw==">    (function(apps) {
        for (var key in apps) {
            if (!apps.hasOwnProperty(key)) {
                continue;
            }

            var app = apps[key];

            if (
                avito &&
                avito.bundles &&
                avito.bundles[app.name] &&
                avito.bundles[app.name][app.version] &&
                typeof avito.bundles[app.name][app.version].render === \'function\'
            ) {
                render = avito.bundles[app.name][app.version].render;

                app.instances.forEach(function(instance) {
                    var mountNode = document.querySelector(\'.\' + instance.selector);

                    var props = {};

                    try {
                        props = JSON.parse(instance.props);
                    } catch(error) {
                        console.error(\'Failed to parse instance.props\', error);
                    }

                    render(mountNode, props);
                });
            }
        };
    })({"au-discount":{"name":"@avito\/au-discount","version":"1.0.0","instances":[]},"profile-sidebar-navigation":{"name":"@avito\/profile-sidebar-navigation","version":"3.27.6","instances":[]}});
</script><img src="https://redirect.frontend.weborama.fr/rd?url=https%3A%2F%2Fwww.avito.ru%2Fadvertisement%2Fweborama.gif%3Fwebouuid%3D{WEBO_CID}" alt="" width="1" height="1"></body>
</html>
        ';
        return $data;
*/
        $data = Json::decode(Proxy::readData($url));
        /*
        if ($data['error']['code']) {
            throw new ProxyException(self::MSG_ERROR_DATA, self::CODE_ERROR_DATA);
        }
*/
        return $data;
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

    public static function delete($id) : array
    {
        try {
            $delObject  = DelObjectRep::findOne($id);
            $lastId     = ++$delObject['lastId'];
            $maxId      = DataLevel3Rep::getMaxId();
            $dataLevel3 = DataLevel3Rep::getRowLastId($lastId);
            $data       = self::_getPage($dataLevel3['url']);
            if ($data['result']['data']['httpCode'] == 404) {
                throw new GeoException(self::MSG_ERROR_DATA, self::CODE_ERROR_DATA);
            }
            if ($data['error']['code']) {
                throw new ProxyException(self::MSG_ERROR_DATA, self::CODE_ERROR_DATA);
            }
            $doc        = self::_getDocument($data['result']['data']['content']);

            switch ($dataLevel3['site']) {
                case 'avito': DataLevel3Avito::getGeo($doc); break;
                case 'vsn':   DataLevel3Vsn::getGeo($doc); break;
                default:      throw new GeoException('', 100);
            }

            // карта существует, значит страница "живая"
            // на всякий случай обновляем статус на "загружен"
            $comment = DataLevel3Rep::setStatusLoaded($dataLevel3['id']);

            // обновляем "последний просмотренный ИД"
            DelObjectRep::setLastId($maxId, $dataLevel3['id'], $id);

            return [
                'error' => [
                    'code'        => self::NO_ERROR,
                    'description' => self::MSG_NO_ERROR,
                ],
                'result' => [
                    'lastId'    => $lastId,
                    'currentId' => $dataLevel3['id'],
                    'message'   => 'Статус - "' . $comment . '"',
                ],
            ];
        } catch (GeoException $e) {
            // не нашли карту на странице
            $comment = DataLevel3Rep::setStatusStaled($dataLevel3['id']);
            Parus::clientRentDeleteOn($dataLevel3['id']);
//          ClientRentObjectsRep::setStatus($object['id'], ClientRentObjectsRep::STATUS_NOT_SUITABLE);
            // обновляем "последний просмотренный ИД"
            DelObjectRep::setLastId($maxId, $dataLevel3['id'], $id);

            return [
                'error' => [
                    'code'        => self::CODE_ERROR_MAP_NOT_FOUND,
                    'description' => self::MSG_ERROR_MAP_NOT_FOUND,
                ],
                'result' => [
                    'lastId'    => $lastId,
                    'currentId' => $dataLevel3['id'],
                    'message'   => 'Статус - "' . $comment . '"',
                ],
            ];
        } catch (BaseException $e) {
            $date = new \DateTime();
            return [
                'error'  => [
                    'code'        => $e->getCode(),
                    'description' => $e->getMessage()
                ],
                'result' => [
                    'lastId'    => $lastId,
                    'currentId' => $dataLevel3['id'],
                    'dateTime'  => $date->format('Y-m-d H:i:s'),
                ]
            ];
        }

        return [];
/*
        try {
            $delObject = DelObjectRep::findOne();
            $lastId = ++$delObject['lastId'];
            $dataLevel3 = DataLevel3Rep::getRowLastId($lastId);
            if (!empty($dataLevel3['url'])) {
                $data = self::_getPage($dataLevel3['url']);
                if ($data['error']['code']) {
                    if ($data['result']['response']['httpCode'] == 404) {
                        // страницы уже не существует
                        $comment = DataLevel3Rep::setStatusDelete($dataLevel3['id']);
//                        ClientRentObjectsRep::setStatus($object['id'], ClientRentObjectsRep::STATUS_NOT_SUITABLE);
                        // обновляем "последний просмотренный ИД"
                        $maxId = DataLevel3Rep::getMaxId();
                        DelObjectRep::setLastId($maxId, $dataLevel3['id']);
                        return [
                            'error' => [
                                'code'        => self::ERROR_PAGE_NOT_FOUND,
                                'description' => self::MSG_ERROR_PAGE_NOT_FOUND,
                            ],
                            'result' => [
                                'id'      => $lastId,
                                'message' => 'Статус - "' . $comment . '"',
                            ],
                        ];
                    }
                    return [
                        'error'  => [
                            'code'        => self::CODE_ERROR_DATA,
                            'description' => self::MSG_ERROR_DATA
                        ],
                        'result' => [
                            'data' => $data,
                            'id'   => $dataLevel3['id'],
                            'url'  => $dataLevel3['url'],
                        ]
                    ];
                } else {
                    $doc = self::_getDocument($data['result']['data']['content']);

                    if (   $doc->first('div.b-search-map')   // это для Авито
                        || $doc->first('div.map')            // это для VSN
                    ) {
                        // карта существует, значит страница "живая"
                        // на всякий случай обновляем статус на "загружен"
                        $comment = DataLevel3Rep::setStatusLoaded($dataLevel3['id']);
                    } else {
                        // не нашли карту на странице
                        $comment = DataLevel3Rep::setStatusStaled($dataLevel3['id']);
//                        ClientRentObjectsRep::setStatus($object['id'], ClientRentObjectsRep::STATUS_NOT_SUITABLE);
                    }
                    $ret = [
                        'error' => [
                            'code'        => self::NO_ERROR,
                            'description' => self::MSG_NO_ERROR,
                        ],
                        'result' => [
                            'id'      => $lastId,
                            'message' => 'Статус - "' . $comment . '"',
                        ],
                    ];
                }
            } else {
                $ret = [
                    'error' => [
                        'code'        => self::NO_ERROR,
                        'description' => self::MSG_NO_ERROR,
                    ],
                    'result' => [
                        'id'      => $lastId,
                        'message' => 'Объект с таким ИД не найден. Статус не изменился.',
                    ],
                ];
            }
            // обновляем "последний просмотренный ИД"
            $maxId = DataLevel3Rep::getMaxId();
            DelObjectRep::setLastId($maxId, $dataLevel3['id']);
        } catch (BaseException $e) {
            $date = new \DateTime();
            $ret = [
                'error'  => [
                    'code'        => $e->getCode(),
                    'description' => $e->getMessage()
                ],
                'result' => [
                    'lastId'   => $lastId,
                    'dateTime' => $date->format('Y-m-d H:i:s'),
                ]
            ];
        }

        return $ret;
*/
    }

    /**
     * Инициализирует парамеры удаления объектов
     *
     * @return array
     */
    public static function initDelete() : array
    {
        $listObject    = DataLevel3Rep::listLoadedObject(); // список ИД
        $countObject   = count($listObject);                // кол-во в списке ИД
        $numGapsDelete = Params::numGapsDelete();           // число промежутков
        $countOneGap   = intval($countObject/$numGapsDelete);   // кол-во ИД на одном промежутке

        $ret[0] = 0;
        DelObjectRep::initRow(1, $ret[0]);  // обновляем запись с ид==1

        for ($i=1; $i<$numGapsDelete; ++$i) {
            $ret[$i] = intval($listObject[$i*$countOneGap]['id']);  // берем из списка ИД со сдвигом $countOneGap
            DelObjectRep::initRow($i+1, $ret[$i]);              // обновляем запись с ид==$i+1
        }

        return $ret;
    }

}
