<?php

use yii\db\Migration;

/**
 * Class m220716_114141_create_data_level1_log
 */
class m220716_114141_create_data_level1_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='Лог загруженных данных. Уровень 1'";
        }
    
        $this->createTable('{{%data_level1_log}}', [
            'url' => $this->string(1200)->notNull(),
            'result' => $this->string(512)->notNull(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%data_level1_log}}');
    }

}
