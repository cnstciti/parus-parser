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
    const ERROR_PAGE_NOT_FOUND = 3;
    const MSG_ERROR_PAGE_NOT_FOUND = 'Страницы уже не существует.';
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
    public static function parser() : array
    {
        $ret = [];
        try {
            $dataLevel2 = DataLevel2Rep::findByStatus(DataLevel2Rep::STATUS_LOADED);
            $data       = self::_getPage($dataLevel2['url']);
            if ($data['error']['code']) {
                throw new ProxyException(self::MSG_ERROR_DATA, self::CODE_ERROR_DATA);
            }
            $document   = self::_getDocument($data['result']['data']['content']);
            switch ($dataLevel2['site']) {
                case 'avito': $ret = DataLevel3Avito::parser($document, $dataLevel2); break;
                case 'vsn':   $ret = DataLevel3Vsn::parser($document, $dataLevel2); break;
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
           
<html> <head> <script nonce="KqlWaNxalqJ/Cog23Ye0bg==">
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
    <script nonce="KqlWaNxalqJ/Cog23Ye0bg==">
 window.dataLayer = [{"userAuth":1,"pageType":"404page","userId":90200356,"cityName":"moskva","regionName":"MSK"}];
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
   <meta name="format-detection" content="telephone=no">  <meta name="google-site-verification" content="7iEzRRMJ2_0p66pVS7wTYYvhZZSFBdzL5FVml4IKUS0" />
                <title>Ошибка 404. Страница не найдена &#8212; Объявления на сайте Авито</title>
  <!--NOTE: если вносите изменения в этот файл, поддержите их в @avito/bx-ads -->
         <meta name="yandex-verification" content="499bdc75d3636c55" /><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/edcf951a50e7eae10aeb.css"><script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/5247f1e287f44ff8c738.js" ></script><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/ad26b420a4e32ec18e4e.css"><script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/d066ac63c881378168cf.js" ></script><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/61b4c466260a7a65c89d.css"><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/d65909e0ffa699c486b6.css"><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/c223ad67f49182fa3f4b.css"><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/12d40f81b7e415ffe00e.css"><script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/7a3af27e628eec76c045.js" ></script><link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/53cef5de35d22fb4ae99.css"><script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/9a55be79271824b4f6d7.js" ></script><link rel="apple-touch-icon-precomposed" sizes="180x180" href="https://www.avito.st/s/common/touch-icons/common/apple-touch-icon-180x180-precomposed.png?57be3fb" /><link rel="apple-touch-icon-precomposed" sizes="152x152" href="https://www.avito.st/s/common/touch-icons/common/apple-touch-icon-152x152-precomposed.png?cac4f2a" /><link rel="apple-touch-icon-precomposed" sizes="144x144" href="https://www.avito.st/s/common/touch-icons/common/apple-touch-icon-144x144-precomposed.png?9615e61" /><link rel="apple-touch-icon-precomposed" sizes="120x120" href="https://www.avito.st/s/common/touch-icons/common/apple-touch-icon-120x120-precomposed.png?2a32f09" /><link rel="apple-touch-icon-precomposed" sizes="114x114" href="https://www.avito.st/s/common/touch-icons/common/apple-touch-icon-114x114-precomposed.png?174e153" /><link rel="apple-touch-icon-precomposed" sizes="76x76" href="https://www.avito.st/s/common/touch-icons/common/apple-touch-icon-76x76-precomposed.png?28e6cfb" /><link rel="apple-touch-icon-precomposed" sizes="72x72" href="https://www.avito.st/s/common/touch-icons/common/apple-touch-icon-72x72-precomposed.png?aeb90b3" /><link rel="apple-touch-icon-precomposed" sizes="57x57" href="https://www.avito.st/s/common/touch-icons/common/apple-touch-icon-57x57-precomposed.png?fd7ac94" /><meta name="msapplication-TileColor" content="#000000"><meta name="msapplication-TileImage" content="/s/common/touch-icons/common/mstile-144x144.png"><meta name="msapplication-config" content="browserconfig.xml" /><link href="https://www.avito.st/favicon.ico?9de48a5" rel="shortcut icon" type="image/x-icon" /><link href="https://www.avito.st/ya-tableau-manifest-ru.json?5ac8b8a" rel="yandex-tableau-widget" /><link href="https://www.avito.st/open_search_ru.xml?4b0fd3d" rel="search" type="application/opensearchdescription+xml" title="Авито" />  <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~df485e34331ffe15da321cbe18cad04a.10d574a14311c3758fb8.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/default~d446c9efe1935fbf69cc24bbe2635c3b~df485e34331ffe15da321cbe18cad04a.528c9a19f76b3ae68a73.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/1b3530b6d1d4bdcfbafc.js" ></script>
 <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~e0704b583294c2ecf03d33677df35305.10868918abba5fd9d9c3.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/8f343abd988e6bd279cb.js" ></script>
   <script nonce="KqlWaNxalqJ/Cog23Ye0bg==">
 window.capturedErrors = [];
 window.addEventListener(\'error\', function(error) {
 window.capturedErrors.push(error);
 });
 </script> <script nonce="KqlWaNxalqJ/Cog23Ye0bg==">
 window.avito = window.avito || {};
 window.avito.platform = \'desktop\';
 window.avito.siteName = \'Авито\';
 window.avito.staticPrefix = \'https://www.avito.st\';
 window.avito.supportPrefix = \'https://support.avito.ru\';
 window.avito.pageId = \'404\';
 window.avito.categoryId = null;
 window.avito.microCategoryId = null;
 window.avito.locationId = null;
 window.avito.routeName = \'item\';
 window.avito.fromPage = \'\';
 window.avito.sentry = {
 dsn: \'https://1ede3b886c8b4efd9230509682fe2f12@sntr.avito.ru/41\',
 release: "rc-202112161101-110146"
 };
 window.avito.clickstream = {
 buildId: "rc-202112161101-110146",
 buildUid: null,
 srcId: 96
 };
 window.avito.isAuthenticated = \'1\' === \'1\';
 window.avito.metrics = {};
 window.avito.metrics.categoryId = null;
 window.avito.metrics.browser = \'chrome.96\';
 
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
 </script>
  <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="/s/a/j/dfp/px.js?ch=1"></script> <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="/s/a/j/dfp/px.js?ch=2"></script>
     
 <script nonce="KqlWaNxalqJ/Cog23Ye0bg==">
 window.avito.ads = {
 userGroup: 40
 };
 </script>
  <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~3d7e85d2c8b51bd652a72c38a1bfd251.7b5f95379e5856536e41.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/a39882a8f95933c72139.js" ></script>
  <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/3ea2ac735fdb34843b10.js" ></script>
    <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/cdc49ce1e76591b99eb5.js" ></script>
  <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/b48f04e1854627ae7544.js" ></script>
 <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/cc947d7c.5144e5d126b00adbf988.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/ac04150f5e6525ba530f.js" ></script>
    </head> <body class="windows windows chrome chrome-chrome "  >
    <noscript> <iframe src=//www.googletagmanager.com/ns.html?id=GTM-KP9Q9H height="0" width="0" style="display:none;visibility:hidden"></iframe> </noscript>
         
<div class="js-header-container header-container header-responsive">
         <div class=\'js-header\' data-state=\'{"responsive":true,"alternativeCategoryMenu":null,"addButtonText":"Разместить объявление","servicesClassName":"header-services","logoData":[{"title":"об автомобилях"},{"title":"об недвижимости"},{"title":"об работе"},{"title":"об услугах"}],"inTNS8114test":true,"currentPage":"404","country":{"host":"www.avito.ru","country_slug":"rossiya","site_name":"Авито","currency_sign":"₽"},"headerInfo":{"unreadFavoritesCount":0,"rating":{"summary":"Нет отзывов","score":0,"scoreFloat":0,"activeReviewsCount":0,"useStarsReviewCount":null,"hideStars":true},"showExtendedProfileLink":false,"showBasicProfileLink":false},"luri":"moskva","menu":{"business":{"title":"Для бизнеса","link":"business","absoluteLink":null},"shops":{"title":"Магазины","link":"shops","absoluteLink":null},"support":{"title":"Помощь","link":null,"absoluteLink":"support.avito.ru"}},"messenger":{"unreadChatCount":0,"socketFallbackUrl":"https:\/\/socket.avito.ru\/fallback"},"isNCEnabled":true,"isShowAvitoPro":true,"user":{"isAuthenticated":true,"id":90200356,"name":"Пользователь","hasShopSubscription":false,"hasTariffOnBudget":false,"isLegalPerson":false,"avatar":"https:\/\/www.avito.st\/stub_avatars\/%D0%9F\/5_256x256.png"},"user_location_id":637640,"userAccount":{"balance":{"bonus":"","real":"0","total":"0"},"isSeparationBalance":false},"hierarchy":{"isEmployee":false,"companyName":null},"now":1639682025,"_dashboard":{},"nonce":"KqlWaNxalqJ\/Cog23Ye0bg=="}\'><div
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
  target="_blank" rel="noopener noreferrer" >Помощь</a></li></ul><div class="header-services-menu-2tz5y"><div class="header-services-menu-item-3H7kQ" data-marker="header/favorites"><a class="header-services-menu-link-fsJlE"
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
 alt="Аватар"></div><span>Пользователь</span></a><div class="header-services-menu-dropdown-popup-Cuy78 header-services-menu-dropdown-popup_left-3Jdq8"
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
 data-item-id="13"
 data-marker="header/tooltip-list-item"><a class="header-link-TLsAU header-profile-nav-link-3ZWkp"
 href="/profile/settings">Настройки</a></li></div><div class="header-profile-nav-group-3M1xl"><li class="header-profile-nav-item-1FXQp header-profile-nav-item_exit-gIxDk"
 data-item-id="14"
 data-marker="header/tooltip-list-item"><a class="header-link-TLsAU header-profile-nav-link-3ZWkp"
 href="/profile/exit">Выйти</a></li></div></ul></div></div></div></div><div class="header-button-wrapper-2UC-r"><a class="button-button-Dtqx2 button-button-origin-12oVr button-button-origin-blue-358Vt"
 href="/additem">Разместить объявление</a></div></div></div></div>   <div class=\'js-header-navigation\' data-state=\'{"responsive":true,"alternativeCategoryMenu":null,"categoryMenu":[{"title":"Авто","categoryId":1},{"title":"Недвижимость","categoryId":4},{"title":"Работа","categoryId":110},{"title":"Услуги","categoryId":113}],"orderAllCategories":[{"id":0,"values":[1,2,8]},{"id":1,"values":[4,6]},{"id":2,"values":[110,114,7]},{"id":3,"values":[5,35]}],"categoryTree":{"1":{"id":25984,"mcId":2,"name":"Транспорт","subs":[{"id":25985,"mcId":14,"name":"Автомобили","subs":[],"url":"\/moskva\/avtomobili?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":9,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_9","customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":25986,"mcId":15,"name":"Мотоциклы и мототехника","subs":[],"url":"\/moskva\/mototsikly_i_mototehnika?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":14,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_14","customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26025,"mcId":16,"name":"Грузовики и спецтехника","subs":[],"url":"\/moskva\/gruzoviki_i_spetstehnika?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":81,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_81","customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26040,"mcId":12,"name":"Водный транспорт","subs":[],"url":"\/moskva\/vodnyy_transport?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":11,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_11","customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":25999,"mcId":17,"name":"Запчасти и аксессуары","subs":[],"url":"\/moskva\/zapchasti_i_aksessuary?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":10,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_10","customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/transport?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":1,"params":[],"count":6,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_1","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"4":{"id":26113,"mcId":5,"name":"Недвижимость","subs":[{"id":26125,"mcId":30,"name":"Квартиры","subs":[],"url":"\/moskva\/kvartiry?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":24,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26115,"mcId":31,"name":"Комнаты","subs":[],"url":"\/moskva\/komnaty?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":23,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26116,"mcId":32,"name":"Дома, дачи, коттеджи","subs":[],"url":"\/moskva\/doma_dachi_kottedzhi?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":25,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26119,"mcId":34,"name":"Гаражи и машиноместа","subs":[],"url":"\/moskva\/garazhi_i_mashinomesta?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":85,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26118,"mcId":33,"name":"Земельные участки","subs":[],"url":"\/moskva\/zemelnye_uchastki?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":26,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26114,"mcId":35,"name":"Коммерческая недвижимость","subs":[],"url":"\/moskva\/kommercheskaya_nedvizhimost\/sdam-ASgBAgICAUSwCNRW?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":42,"params":{"536":5546},"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26120,"mcId":36,"name":"Недвижимость за рубежом","subs":[],"url":"\/moskva\/nedvizhimost_za_rubezhom?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":86,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":30344,"mcId":30,"name":"Ипотечный калькулятор","subs":[],"url":"\/ipoteka\/calculator","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":24,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":"\/ipoteka\/calculator","developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/nedvizhimost?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":4,"params":[],"count":9,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_4","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"110":{"id":26400,"mcId":10,"name":"Работа","subs":[{"id":26427,"mcId":61,"name":"Вакансии","subs":[],"url":"\/moskva\/vakansii?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":111,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26401,"mcId":62,"name":"Резюме","subs":[],"url":"\/moskva\/rezume?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":112,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/rabota?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":110,"params":[],"count":3,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_110","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"114":{"id":26486,"mcId":63,"name":"Услуги","subs":[],"url":"\/moskva\/predlozheniya_uslug?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":114,"params":[],"count":24,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_114","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"5":{"id":26127,"mcId":6,"name":"Личные вещи","subs":[{"id":26128,"mcId":37,"name":"Одежда, обувь, аксессуары","subs":[],"url":"\/moskva\/odezhda_obuv_aksessuary?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":27,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26153,"mcId":38,"name":"Детская одежда и обувь","subs":[],"url":"\/moskva\/detskaya_odezhda_i_obuv?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":29,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26173,"mcId":39,"name":"Товары для детей и игрушки","subs":[],"url":"\/moskva\/tovary_dlya_detey_i_igrushki?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":30,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26187,"mcId":41,"name":"Красота и здоровье","subs":[],"url":"\/moskva\/krasota_i_zdorove?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":88,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26183,"mcId":40,"name":"Часы и украшения","subs":[],"url":"\/moskva\/chasy_i_ukrasheniya?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":28,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/lichnye_veschi?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":5,"params":[],"count":6,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_5","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"2":{"id":26047,"mcId":3,"name":"Для дома и дачи","subs":[{"id":26088,"mcId":23,"name":"Ремонт и строительство","subs":[],"url":"\/moskva\/remont_i_stroitelstvo?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":19,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26073,"mcId":21,"name":"Мебель и интерьер","subs":[],"url":"\/moskva\/mebel_i_interer?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":20,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26048,"mcId":20,"name":"Бытовая техника","subs":[],"url":"\/moskva\/bytovaya_tehnika?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":21,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26087,"mcId":18,"name":"Продукты питания","subs":[],"url":"\/moskva\/produkty_pitaniya?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":82,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26097,"mcId":19,"name":"Растения","subs":[],"url":"\/moskva\/rasteniya?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":106,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26084,"mcId":22,"name":"Посуда и товары для кухни","subs":[],"url":"\/moskva\/posuda_i_tovary_dlya_kuhni?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":87,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/dlya_doma_i_dachi?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":2,"params":[],"count":7,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_2","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"6":{"id":26195,"mcId":7,"name":"Электроника","subs":[{"id":26249,"mcId":49,"name":"Телефоны","subs":[],"url":"\/moskva\/telefony?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":84,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26196,"mcId":43,"name":"Аудио и видео","subs":[],"url":"\/moskva\/audio_i_video?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":32,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26292,"mcId":42,"name":"Товары для компьютера","subs":[],"url":"\/moskva\/tovary_dlya_kompyutera?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":101,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26216,"mcId":44,"name":"Игры, приставки и программы","subs":[],"url":"\/moskva\/igry_pristavki_i_programmy?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":97,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26222,"mcId":46,"name":"Ноутбуки","subs":[],"url":"\/moskva\/noutbuki?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":98,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26221,"mcId":45,"name":"Настольные компьютеры","subs":[],"url":"\/moskva\/nastolnye_kompyutery?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":31,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26209,"mcId":50,"name":"Фототехника","subs":[],"url":"\/moskva\/fototehnika?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":105,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26236,"mcId":48,"name":"Планшеты и электронные книги","subs":[],"url":"\/moskva\/planshety_i_elektronnye_knigi?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":96,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26223,"mcId":47,"name":"Оргтехника и расходники","subs":[],"url":"\/moskva\/orgtehnika_i_rashodniki?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":99,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/bytovaya_elektronika?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":6,"params":[],"count":10,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_6","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"7":{"id":26315,"mcId":8,"name":"Хобби и отдых","subs":[{"id":26316,"mcId":51,"name":"Билеты и путешествия","subs":[],"url":"\/moskva\/bilety_i_puteshestviya?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":33,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26339,"mcId":53,"name":"Велосипеды","subs":[],"url":"\/moskva\/velosipedy?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":34,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26345,"mcId":54,"name":"Книги и журналы","subs":[],"url":"\/moskva\/knigi_i_zhurnaly?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":83,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26349,"mcId":55,"name":"Коллекционирование","subs":[],"url":"\/moskva\/kollektsionirovanie?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":36,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26373,"mcId":52,"name":"Музыкальные инструменты","subs":[],"url":"\/moskva\/muzykalnye_instrumenty?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":38,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26324,"mcId":56,"name":"Охота и рыбалка","subs":[],"url":"\/moskva\/ohota_i_rybalka?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":102,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26325,"mcId":57,"name":"Спорт и отдых","subs":[],"url":"\/moskva\/sport_i_otdyh?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":39,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/hobbi_i_otdyh?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":7,"params":[],"count":8,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_7","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"35":{"id":26098,"mcId":4,"name":"Животные","subs":[{"id":26099,"mcId":24,"name":"Собаки","subs":[],"url":"\/moskva\/sobaki?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":89,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26100,"mcId":25,"name":"Кошки","subs":[],"url":"\/moskva\/koshki?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":90,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26101,"mcId":26,"name":"Птицы","subs":[],"url":"\/moskva\/ptitsy?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":91,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26102,"mcId":27,"name":"Аквариум","subs":[],"url":"\/moskva\/akvarium?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":92,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26103,"mcId":28,"name":"Другие животные","subs":[],"url":"\/moskva\/drugie_zhivotnye?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":93,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26112,"mcId":29,"name":"Товары для животных","subs":[],"url":"\/moskva\/tovary_dlya_zhivotnyh?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":94,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/zhivotnye?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":35,"params":[],"count":7,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_35","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false},"8":{"id":26382,"mcId":9,"name":"Готовый бизнес и оборудование","subs":[{"id":26383,"mcId":59,"name":"Готовый бизнес","subs":[],"url":"\/moskva\/gotoviy_biznes?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":116,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false},{"id":26393,"mcId":60,"name":"Оборудование для бизнеса","subs":[],"url":"\/moskva\/oborudovanie_dlya_biznesa?cd=1","current":false,"currentParent":false,"opened":false,"level":2,"categoryId":40,"params":[],"count":1,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":null,"customUrl":null,"developerId":null,"shield":null,"nofollow":false}],"url":"\/moskva\/dlya_biznesa?cd=1","current":false,"currentParent":false,"opened":false,"level":1,"categoryId":8,"params":[],"count":3,"shortListMaxLength":null,"shortListCollapsedLength":null,"longListMaxLength":null,"longListCollapsedLength":null,"iconUrl":"cat_8","customUrl":null,"developerId":null,"shield":null,"nofollow":false,"isCars":false}},"commonCategories":{"0":{"slug":null,"id":0},"1":{"slug":"transport","id":1},"2":{"slug":"dlya_doma_i_dachi","id":2},"3":{"slug":null,"id":3},"4":{"slug":"nedvizhimost","id":4},"110":{"slug":"rabota","id":110},"111":{"slug":"vakansii","id":111},"112":{"slug":"rezume","id":112},"113":{"slug":"uslugi","id":113},"9":{"slug":"avtomobili","id":9}},"constant":{"Obj_Category_VERTICAL_AUTO":0,"Obj_Category_VERTICAL_REALTY":1,"Obj_Category_VERTICAL_JOB":2,"Obj_Category_VERTICAL_SERVICES":3,"Obj_Category_ROOT_TRANSPORT":1,"Obj_Category_ROOT_REAL_ESTATE":4,"Obj_Category_ROOT_JOB":110,"Obj_Category_JOB_VACANCIES":111,"Obj_Category_JOB_RESUME":112,"Obj_Category_ROOT_SERVICES":113,"Obj_Category_TRANSPORT_CARS":9},"allCategoriesLink":"\/moskva","country":{"host":"www.avito.ru","country_slug":"rossiya","site_name":"Авито","currency_sign":"₽"},"luri":"moskva","verticalId":null,"now":1639682025,"_dashboard":{},"nonce":"KqlWaNxalqJ\/Cog23Ye0bg=="}\'><div
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
 >Товары для животных</a></li></ul></div></div></div></div></div></div></div></div></div></div>  </div>
    
 <div class="g_123 layout-content-wrap"> <div class="l-content clearfix">
  <div class="b-404"> <h1>Ой! Такой страницы на нашем сайте нет :(</h1> <p>Наверное, вы ошиблись при наборе адреса или перешли по неверной ссылке.</p> <p>Не расстраивайтесь, выход есть!<br>Перейдите <a href="/">на главную страницу</a> или <a href="//www.avito.ru/moskva">на страницу объявлений</a>.</p> <i class="i i-404"></i> </div>
 </div>
  <div
 class="js-footer-app layout-internal col-12"
 data-source-data=\'&#x7B;&quot;luri&quot;&#x3A;&quot;moskva&quot;,&quot;countrySlug&quot;&#x3A;&quot;rossiya&quot;,&quot;supportPrefix&quot;&#x3A;&quot;https&#x3A;&#x5C;&#x2F;&#x5C;&#x2F;support.avito.ru&quot;,&quot;siteName&quot;&#x3A;&quot;&#x0410;&#x0432;&#x0438;&#x0442;&#x043E;&quot;,&quot;city&quot;&#x3A;null,&quot;mobileVersionUrl&quot;&#x3A;&quot;m.avito.ru&#x3F;nomobile&#x3D;0&quot;,&quot;isShopBackground&quot;&#x3A;null,&quot;isShopPlank&quot;&#x3A;null,&quot;isCompanyPage&quot;&#x3A;false,&quot;isTechPage&quot;&#x3A;false,&quot;isBrowserMobile&quot;&#x3A;false&#x7D;\'> </div>
   </div>
  
 <div id="counters-invisible" class="counters-invisible">
     <noscript> <img src="/stat/u?1639682025" alt=""/> </noscript>
  
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==">
         if (avito.tracking && avito.tracking.initCriteo) {
 var isRealty = false;
 avito.tracking.initCriteo(isRealty ? [10019, 39534] : 39534, 90200356, "8c1f78f7962cca1a6ba48470a88cfc14");
 }
         
</script>
    
 <script nonce="KqlWaNxalqJ/Cog23Ye0bg==">
 var img = new Image();
 img.src = \'//www.tns-counter.ru/V13a***R>\' + document.referrer.replace(/\* /g,\'%2a\') + \'*avito_ru/ru/CP1251/tmsec=avito_other/\' + Math.round(Math.random() * 1000000000);
 </script> <noscript><img src="//www.tns-counter.ru/V13a****avito_ru/ru/CP1251/tmsec=avito_other/" width="1" height="1" alt="" /></noscript>

<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" type="text/javascript">
 window.avito = window.avito || {};
</script>
  <!-- Yandex.Metrika counter --> <script nonce="KqlWaNxalqJ/Cog23Ye0bg==">
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
 abFeatures: {"ad_splitter":"one","react_recommendations":"react_recommendations","dynamic_an_sx_design_web":"control","currency_price_Transport":"test2","online_booking_car":"test","Salary_suggest_item_add":"folks_added","tariff_onboarding":"control"}
 }
 });
 </script> <noscript><div><img src="https://mc.yandex.ru/watch/34241905" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter -->
 <script nonce="KqlWaNxalqJ/Cog23Ye0bg==">var google_conversion_id=987009030,google_conversion_label="f8JaCLLjvAQQhqDS1gM",google_custom_params=window.google_tag_params,google_remarketing_only=!0;</script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="//www.googleadservices.com/pagead/conversion.js"></script> <noscript> <div style="display:inline;"> <img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/987009030/?value=0&amp;label=f8JaCLLjvAQQhqDS1gM&amp;guid=ON&amp;script=0"/> </div> </noscript>
  </div>
     <script async>
 function loadSecuredTouchScript() {
 var scriptElement = document.createElement(\'script\');
 scriptElement.setAttribute(\'id\', \'stPingId\');
 scriptElement.setAttribute(\'nonce\', \'KqlWaNxalqJ/Cog23Ye0bg==\');
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
   <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/3575862bf6f694165831.js" async></script>
      <div class="js-after-login-notification"></div>
 
 <input type="hidden" class="js-token" name="token[2548676105484]" value="1639682025.2400.ccd02775a4889f36389fed92d4fea14d5b9167e4e4c04ad6b1dbbddc17b695aa">
  <div class="js-popup-app" data-uid="90200356"></div>
   <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/deps/object-assign/4.1.1/prod/web/main.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/deps/react/16.14.0/prod/web/main.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/deps/scheduler/0.19.1/prod/web/main.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/deps/react-dom/16.14.0/prod/web/main.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/deps/prop-types/15.7.2/prod/web/main.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/deps/react-popper/1.3.11/prod/web/main.js" ></script>
  <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/ae1cec57.78d2082c7bca0775dd6d.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/d300404d.ef713627dc8ea454898c.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~c6cf528e4501db21b258d7132506b957.3313a7242bed1f0ba964.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/f69e3d8c3ec0ea9ff579.js" ></script>
    <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~a2d3cceca3b7a99aec402f4274daae86~b6c9dd376cf6242aa66aaf78a98e7cd5.832c59476c7e3cc5e78c.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~b6c9dd376cf6242aa66aaf78a98e7cd5.cbd76bc7eab1459e6bb2.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/b141a05eefc50dc99b3a.js" ></script>
     <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~3aed0e1873f8a9e1501f0b0b51f4c086~91ac0639b450d3cf68aad1b462aa0300~96d322aa0de504f4cdc0ae9171~dbd13c97.8288e7209229f78000fb.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~3aed0e1873f8a9e1501f0b0b51f4c086.0f6d03860fc0853584a6.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/fe64a4935f3f7c882f49.js" ></script>
    <link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/2159445a46e0707b14c2.css">
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/8a58e2e2.933ab78269e136eb43ee.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/310f6387.307dcce1f0ef2d7a1232.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/e464c10f.d663643fd1dfa98bc2f5.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/39292b7e.840fb589c9de2c312e27.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/6d22e520.b814d5ceb8007137b348.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/4b9903cd.52360aa19a1c1224a76f.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/57d36993.81a0502c20453b6b2b70.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/e4767866.2381b1f46f9010ce3193.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/9a57f842.d62b2d5a2efc2998a07a.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/08bfffcc.0f8122914387b71335cb.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/0522320a.03349735aa1ecb5ec6c5.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/5efdecd5.88a173f8da30814ca8f5.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/865d3415.29d3282c21b6e20fd700.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~939a0d94bbc91af8d638b39ad322496d.5dd65456031de2c1d299.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/03dc72c2263b1f9c9392.js" ></script>
   <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/7a9510c9e68e344f3f48.js" ></script>
 <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~1b2b00a60cd03ffec338ef0b0ac78b8e~31040a7aac027533fb02471c77e63023~4c8f84c8026041b236b200a341~05449912.9816299116ef6949f7f5.js" defer></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~31040a7aac027533fb02471c77e63023~4c8f84c8026041b236b200a3412a0021~b71730f89291be60bb6768ff07~38286b7d.b47f540e9e11d22d8c1c.js" defer></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~1b2b00a60cd03ffec338ef0b0ac78b8e~31040a7aac027533fb02471c77e63023~f683120034ffac843ed686125e86e410.91d96e6d9844e31cd2e9.js" defer></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~1b2b00a60cd03ffec338ef0b0ac78b8e~31040a7aac027533fb02471c77e63023.e593246c1b42919e18fb.js" defer></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~31040a7aac027533fb02471c77e63023.7052d8ecf79a66d4aefb.js" defer></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/211d3fb0d6f93687cfd8.js" defer></script>
  <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/c3e2978f6f98f663a688.js" defer></script>
  <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/a2dd8b0c33a59a02fdb6.js" defer></script>
 <link rel="stylesheet" href="https://static.avito.ru/s/cc/styles/1fa8bfce79159d0b41a6.css">
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/5fc19855.b54bf979e1934299c886.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/d20c09cd.380988cbbfbefedc472e.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/fb4116f7.72d65eea239793586071.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/87ba6cce.3c9f2e1467bcd3297990.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/67a1370b.6d720b4fd54819080898.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~6d1ff0835052ef24d1cee868ea965cf7~87ecf4b3ce8a5dbfd7363ef20d2f928e~99543f3a5e776e5ddac31dc112~5ab99612.15755c3a63f19f11845c.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~87ecf4b3ce8a5dbfd7363ef20d2f928e.f6c366d94cf375aa9f0e.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/4da22ed6e299d21784ac.js" ></script>
 <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/dca958e3.8122e41e4ee6931a1b18.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/65994ee9.3d792f946615aab42a86.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~bebc26f26cd0f589d3fa91a577e91571.6770cfd503e0d8700c8c.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/d0d7db99ec5dc8c7b980.js" ></script>
  <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/chunks/vendors~1e6ffda9acd9ef698cc242384ac53839~2aa3a87bb8bf177a5ea78ae155203f78~64287ebed9c63b5ae76193114f~983ccc46.7a55261d81e73fc0ff7d.js" ></script>
<script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/fd9582bd03d301cf0d14.js" ></script>
     <div class="js-mini-messenger" data-marker="mini-messenger"></div>
 <script nonce="KqlWaNxalqJ/Cog23Ye0bg==" src="https://static.avito.ru/s/cc/bundles/9234915849807e6a5817.js" defer></script>
   <script nonce="KqlWaNxalqJ/Cog23Ye0bg==">    (function(apps) {
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
    })({"profile-sidebar-navigation":{"name":"@avito\/profile-sidebar-navigation","version":"3.27.3","instances":[]}});
</script><img src="https://redirect.frontend.weborama.fr/rd?url=https%3A%2F%2Fwww.avito.ru%2Fadvertisement%2Fweborama.gif%3Fwebouuid%3D{WEBO_CID}" alt="" width="1" height="1"></body> </html>

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
