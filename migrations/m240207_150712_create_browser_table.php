<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%browser}}`.
 */
class m240207_150712_create_browser_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%browser}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%browser}}');
    }
}
