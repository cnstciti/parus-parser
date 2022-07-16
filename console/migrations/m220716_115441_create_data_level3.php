<?php

use yii\db\Migration;

/**
 * Class m220716_115441_create_data_level3
 */
class m220716_115441_create_data_level3 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='Обработанные данные. Уровень 2'";
        }
    
        $this->createTable('{{%data_level3}}', [
            'id' => $this->primaryKey()->comment('ИД'),
            'latitude' => $this->string(32)->defaultValue(null)->comment('Географическая широта'),
            'longitude' => $this->string(32)->defaultValue(null)->comment('Географическая долгота'),
            'type_house' => "enum('кирпичный','панельный','блочный','монолитный','деревянный','кирпич','брус','бревно','газоблоки','металл','пеноблоки','сэндвич-панели','ж/б панели','экспериментальные материалы','керамзитобетон','кирпично-монолитный','') DEFAULT NULL COMMENT 'Тип дома'",
            'floor' => $this->tinyInteger(3)->unsigned()->defaultValue(null)->comment('Этаж'),
            'number_of_floors' => $this->tinyInteger(3)->unsigned()->defaultValue(null)->comment('Этажность'),
            'rooms' => "enum('1','2','3','4','5','6','студия','свободная планировка','дома','дачи','таунхаусы','коттеджи') DEFAULT NULL COMMENT 'Количество комнат'",
            'total_area' => $this->string(8)->defaultValue(null)->comment('Общая площадь'),
            'kitchen_area' => $this->string()->defaultValue(null)->comment('Площадь кухни'),
            'living_area' => $this->string(8)->defaultValue(null)->comment('Жилая площадь'),
            'address' => $this->string(256)->defaultValue(null)->comment('Адрес'),
            'metro_station1' => $this->string(64)->defaultValue(null)->comment('Станция метро 1'),
            'metro_station2' => $this->string(64)->defaultValue(null)->comment('Станция метро 2'),
            'metro_station3' => $this->string(64)->defaultValue(null)->comment('Станция метро 3'),
            'description' => $this->text()->comment('Описание с сайта'),
            'price' => $this->integer(10)->unsigned()->defaultValue(null)->comment('Стоимость'),
            'price_deposit' => $this->integer(10)->unsigned()->defaultValue(null)->comment('Стоимость залога'),
            'seller_name1' => $this->string(256)->defaultValue(null)->comment('Имя продавца 1'),
            'url' => $this->string(512)->defaultValue(null)->comment('Ссылка на страницу'),
            'status' => "enum('загружен','опубликован','удален','устарел','ручное_удаление','не наш объект') DEFAULT NULL COMMENT 'Статус'",
            'type_object' => "enum('квартира','комната','участок','дом') DEFAULT NULL COMMENT 'Тип объекта'",
            'action_object' => "enum('продажа','аренда') DEFAULT NULL COMMENT 'Дествие над объектом'",
            'site' => $this->string(64)->defaultValue(null)->comment('Наименование сайта'),
            'comment_parser' => $this->string(128)->defaultValue(null)->comment('Комментарий парсера'),
            'create_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('Дата создания'),
            'update_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('Дата обновления'),
            'typeLand' => "enum('ИЖС','СНТ','') DEFAULT NULL",
            'myYearOfConstruction' => $this->smallInteger(6)->defaultValue(null),
            'myRoom1Area' => $this->string(8)->defaultValue(null),
            'myRoom2Area' => $this->string(8)->defaultValue(null),
            'myRoom3Area' => $this->string(8)->defaultValue(null),
            'myRoom4Area' => $this->string(8)->defaultValue(null),
            'myRoom5Area' => $this->string(8)->defaultValue(null),
            'myRoom6Area' => $this->string(8)->defaultValue(null),
            'myTypeRoom' => "enum('изолированные','смежные','смежно-изолированные','') DEFAULT NULL",
            'landArea' => $this->string(8)->defaultValue(null),
            'numberOfBedrooms' => $this->integer(11)->unsigned()->defaultValue(null)->comment('Количество спален'),
            'myCeilingHeight' => $this->string(8)->defaultValue(null),
            'myRepair' => "enum('типовой','евро-ремонт','нет','черновая отделка','') DEFAULT NULL",
            'myViewWindow' => $this->string(256)->defaultValue(null),
            'myBalcony' => "enum('балкон','лоджия','нет','') DEFAULT NULL",
            'myGlass' => $this->string(256)->defaultValue(null),
            'myAddress' => $this->string(256)->defaultValue(null),
            'myFlatNumber' => $this->string(8)->defaultValue(null),
            'myDescHouse' => $this->string(128)->defaultValue(null),
            'myDescription' => $this->text(),
            'myPrice1' => $this->integer(11)->unsigned()->defaultValue(null),
            'newPrice' => $this->integer(11)->unsigned()->defaultValue(null),
            'averagePrice' => $this->integer(11)->unsigned()->defaultValue(null),
            'financialCalculation' => "enum('только наличные','возможна ипотека','') DEFAULT NULL",
            'myContractPrice' => $this->integer(11)->unsigned()->defaultValue(null),
            'myСommissionPrice' => $this->integer(11)->unsigned()->defaultValue(null),
            'myDepositDivided' => $this->tinyInteger(4)->unsigned()->defaultValue(null),
            'myOverPayment' => "enum('коммунальные платежи','свет + вода + газ по счетчикам','свет + вода по счетчикам','свет по счетчикам','коммунальные платежи включены в стоимость аренды','') DEFAULT NULL",
            'mySettlementYes' => $this->string(256)->defaultValue(null),
            'mySettlementNo' => $this->string(256)->defaultValue(null),
            'mySettlementPreferably' => $this->string(256)->defaultValue(null),
            'myCommentContact1' => $this->string(256)->defaultValue(null),
            'myPhone1' => $this->string(16)->defaultValue(null),
            'dateLastCall1' => $this->date()->defaultValue(null),
            'mySellerName2' => $this->string(256)->defaultValue(null),
            'myCommentContact2' => $this->string(256)->defaultValue(null),
            'myPhone2' => $this->string(16)->defaultValue(null),
            'dateLastCall2' => $this->date()->defaultValue(null),
            'mySellerName3' => $this->string(256)->defaultValue(null),
            'myCommentContact3' => $this->string(256)->defaultValue(null),
            'myPhone3' => $this->string(16)->defaultValue(null),
            'dateLastCall3' => $this->date()->defaultValue(null),
            'mySellerName4' => $this->string(256)->defaultValue(null),
            'myCommentContact4' => $this->string(256)->defaultValue(null),
            'myPhone4' => $this->string(16)->defaultValue(null),
            'dateLastCall4' => $this->date()->defaultValue(null),
            'mySellerName5' => $this->string(256)->defaultValue(null),
            'myCommentContact5' => $this->string(256)->defaultValue(null),
            'myPhone5' => $this->string(16)->defaultValue(null),
            'dateLastCall5' => $this->date()->defaultValue(null),
            'mySellerName6' => $this->string(256)->defaultValue(null),
            'myCommentContact6' => $this->string(256)->defaultValue(null),
            'myPhone6' => $this->string(16)->defaultValue(null),
            'dateLastCall6' => $this->date()->defaultValue(null),
            'myNumberToilet' => $this->tinyInteger(3)->unsigned()->defaultValue(null),
            'myTypeToilet' => "enum('совместный','раздельный','') DEFAULT NULL",
            'myTileToilet' => $this->string(256)->defaultValue(null),
            'myNumberOwner' => $this->tinyInteger(4)->unsigned()->defaultValue(null),
            'minorOwners' => $this->tinyInteger(1)->unsigned()->defaultValue(null)->comment('Несовершеннолетние собственники'),
            'allocationShares' => $this->tinyInteger(1)->unsigned()->defaultValue(null)->comment('Выделение долей'),
            'custodyPassed' => $this->tinyInteger(1)->unsigned()->defaultValue(null)->comment('Опека пройдена'),
            'encumbrance' => $this->tinyInteger(1)->unsigned()->defaultValue(null)->comment('Обременение'),
            'myBankEncumbrances' => $this->string(64)->defaultValue(null),
            'myAmountEncumbrances' => $this->integer(11)->unsigned()->defaultValue(null),
            'myPeriodOfPurchase' => "enum('< 5 лет','> 5 лет','') DEFAULT NULL",
            'myDocBase' => "enum('договор долевого участия (первичка)','договор инвестирования (первичка)','договор уступки прав требования (первичка)','договор купли-продажи (вторичка)','договор передачи','наследство (завещание)','наследство (по закону)','договор дарения','договор долевого участия (ипотека)','договор уступки прав требования (ипотека)','договор купли-продажи (ипотека)','решение суда (наследство)','решение суда (завещание)','решение суда (раздел имущества)','справка о выплате пая','соглашение о разделе имущества','договор мены') DEFAULT NULL",
            'myAlternative' => $this->string(256)->defaultValue(null),
            'methodOfSale' => "enum('прямая продажа','альтернатива','') DEFAULT NULL",
            'myPhoto' => $this->string(256)->defaultValue(null),
            'passengerElevator' => $this->tinyInteger(4)->unsigned()->defaultValue(null)->comment('Лифт пассажирский'),
            'freightElevator' => $this->tinyInteger(4)->unsigned()->defaultValue(null)->comment('Лифт грузовой'),
            'myFlatParam' => $this->string(256)->defaultValue(null),
            'myComment' => $this->text(),
            'electricity' => "enum('заведено на участок','по границе','') DEFAULT NULL",
            'electricityPower' => $this->smallInteger(6)->unsigned()->defaultValue(null),
            'gas' => "enum('заведен на участок','по границе','') DEFAULT NULL",
            'waterSupply' => "enum('централизованное','колодец','скважина','') DEFAULT NULL",
            'irrigationWater' => $this->string(256)->defaultValue(null),
            'sewageSystem' => "enum('централизованная','выгребная яма','септик','') DEFAULT NULL",
            'internet' => $this->string(256)->defaultValue(null),
            'fence' => "enum('профнастил','сетка','дерево','кирпич','') DEFAULT NULL",
            'outbuilding' => $this->string(256)->defaultValue(null),
            'formLand' => "enum('правильная','не правильная','') DEFAULT NULL",
            'landSurveying' => $this->string(256)->defaultValue(null),
            'cadastralNumber' => $this->string(256)->defaultValue(null),
            'refusalOfNeighbors' => $this->string(256)->defaultValue(null),
            'separatePersonalAccount' => $this->string(256)->defaultValue(null),
            'landCategory' => "enum('ЗНП - ИЖС','ЗНП - ЛПХ','ЗСН - ЛПХ','ЗСН - Садоводство','') DEFAULT NULL",
            'permanentRegistration' => $this->tinyInteger(1)->unsigned()->defaultValue(null)->comment('Постоянная регистрация'),
            'residential' => $this->tinyInteger(1)->unsigned()->defaultValue(null)->comment('Жилой'),
            'yearRoundAccommodation' => $this->tinyInteger(1)->unsigned()->defaultValue(null)->comment('Круглогодичное проживание'),
            'videoSurveillance' => $this->tinyInteger(1)->unsigned()->defaultValue(null)->comment('Видеонаблюдение'),
            'alarmSystem' => $this->tinyInteger(1)->unsigned()->defaultValue(null)->comment('Сигнализация'),
            'mansard' => $this->tinyInteger(1)->unsigned()->defaultValue(null)->comment('Мансарда'),
            'base' => $this->tinyInteger(1)->unsigned()->defaultValue(null)->comment('Цоколь'),
            'garageInHouse' => $this->tinyInteger(1)->unsigned()->defaultValue(null)->comment('Гараж в доме'),
            'saunaInHouse' => $this->tinyInteger(1)->unsigned()->defaultValue(null)->comment('Баня в доме'),
            'heating' => "enum('котел электрический','котел газовый','котел твердотопливный','печь','камин','электроконвекторы','') DEFAULT NULL COMMENT 'Отопление'",
            'hot' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%data_level3}}');
    }

}
