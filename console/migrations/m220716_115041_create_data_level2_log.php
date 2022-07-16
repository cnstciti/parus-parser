<?php

use yii\db\Migration;

/**
 * Class m220716_115041_create_data_level2_log
 */
class m220716_115041_create_data_level2_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='Лог обработанных данных. Уровень 2'";
        }
    
        $this->createTable('{{%data_level2_log}}', [
            'url' => $this->string(256)->notNull(),
            'status' => $this->string(64)->notNull(),
            'createAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%data_level2_log}}');
    }

}
