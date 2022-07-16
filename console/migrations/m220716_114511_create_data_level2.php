<?php

use yii\db\Migration;

/**
 * Class m220716_114511_create_data_level2
 */
class m220716_114511_create_data_level2 extends Migration
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
    
        $this->createTable('{{%data_level2}}', [
            'id' => $this->primaryKey(),
            'url' => $this->string(512)->notNull(),
            'status' => "enum('загружен','устарел','не наш объект','обработан','ошибка') DEFAULT NULL",
            'type' => "enum('квартира','комната','участок','дом') DEFAULT NULL",
            'action' => "enum('продажа','аренда') DEFAULT NULL",
            'site' => $this->string(64)->defaultValue(null),
            'block' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'createAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%data_level2}}');
    }

}
