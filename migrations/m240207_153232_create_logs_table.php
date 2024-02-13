<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%data}}`.
 */
class m240207_153232_create_logs_table extends Migration
{
    /**
     *
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%logs}}', [
            'id' => $this->primaryKey(),
            'ip_address' => $this->string()->comment('IP-адрес'),
            'request_time' => $this->time()->comment('время запроса'),
            'request_date' => $this->date()->comment('дата запроса'),
            'url' => $this->string()->comment('Url-адрес'),
            'operating_system_id' => $this->integer()->comment('ID операционной системы'),
            'architecture_is_64' => $this->boolean()->comment('архитектура'),
            'browser_id' => $this->integer()->comment('ID браузера')
        ]);

        $this->addForeignKey(
            'fk-logs-browser_id',
            '{{%logs}}',
            'browser_id',
            '{{%browser}}',
            'id',
            'CASCADE',
        );

        $this->addForeignKey(
            'fk-logs-operating_system_id',
            '{{%logs}}',
            'operating_system_id',
            '{{%operating_system}}',
            'id',
            'CASCADE',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-logs-browser_id', '{{%logs}}');
        $this->dropForeignKey('fk-logs-operating_system_id', '{{%logs}}');
        $this->dropTable('{{%logs}}');
    }
}
