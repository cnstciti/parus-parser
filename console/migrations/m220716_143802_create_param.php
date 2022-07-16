<?php

use yii\db\Migration;

/**
 * Class m220716_143802_create_param
 */
class m220716_143802_create_param extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='Параметры парсеров'";
        }
    
        $this->createTable('{{%param}}', [
            'id' => $this->primaryKey(),
            'description' => $this->string(128)->notNull(),
            'numPage' => $this->integer(11)->unsigned()->notNull(),
            'type' => "enum('квартира','комната','участок','дом') DEFAULT NULL",
            'action' => "enum('продажа','аренда') DEFAULT NULL",
            'city' => "enum('Климовск','Воскресенское','Москва','Подольск','Щербинка','Видное','Знамя Октября','Москва и МО','Домодедово','Коммунарка','Константиново','Львовский','Троицк','Шишкин лес','Ватутинки','Cектор1','Cектор2','Cектор3','Cектор4','Cектор5','Cектор6','Cектор7','Cектор8','Балашиха','Железнодорожный','Реутов','Люберцы','Красково','Котельники','Быково','Ильинский','Жуковский','Кратово','Старая Купавна','Октябрьский','Удельная','Раменское','Cектор9','Cектор10','Cектор11','Cектор12','Cектор13','Cектор14','Cектор15','Cектор16','Cектор17','Cектор18','Cектор19','Cектор20','Cектор21','Cектор22','Cектор23') NOT NULL",
            'site' => $this->string(16)->notNull()->comment('Сайт'),
            'url' => $this->string(1200)->defaultValue(null),
            'run' => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('Признак запуска парсера'),
            
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%param}}');
    }

}
