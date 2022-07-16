<?php

use yii\db\Migration;

/**
 * Class m220716_143015_create_data_level3_log
 */
class m220716_143015_create_data_level3_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='Лог обработанных данных. Уровень 3'";
        }
    
        $this->createTable('{{%data_level3_log}}', [
            'level2_id' => $this->integer(11)->defaultValue(null),
            'level3_id' => $this->integer(11)->defaultValue(null),
            'url' => $this->string(512)->defaultValue(null),
            'createAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%data_level3_log}}');
    }

}
