<?php

use yii\db\Migration;

/**
 * Class m220716_110738_create_data_level1
 */
class m220716_110738_create_data_level1 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='Загруженные данные. Уровень 1'";
        }

        $this->createTable('{{%data_level1}}', [
            'url' => $this->string(256)->notNull(),
            'type' => "enum('квартира','комната','участок','дом') NOT NULL",
            'action' => "enum('продажа','аренда') NOT NULL",
            'createAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'site' => $this->string(64)->defaultValue(null),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%data_level1}}');
    }
}
