<?php

use yii\db\Migration;

/**
 * Class m220716_143456_create_del_object
 */
class m220716_143456_create_del_object extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='Параметры удаления объектов'";
        }
    
        $this->createTable('{{%del_object}}', [
            'id' => $this->primaryKey(),
            'lastId' => $this->integer(11)->unsigned()->notNull(),
            'start' => $this->integer(11)->unsigned()->notNull(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%del_object}}');
    }

}
