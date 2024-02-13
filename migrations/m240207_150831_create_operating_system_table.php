<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%operating_system}}`.
 */
class m240207_150831_create_operating_system_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%operating_system}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%operating_system}}');
    }
}
