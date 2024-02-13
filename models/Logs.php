<?php

namespace app\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "logs".
 *
 * @property int $id
 * @property string|null $ip_address IP-адрес
 * @property string|null $request_time время запроса
 * @property string|null $url Url-адрес
 * @property int|null $operating_system_id ID операционной системы
 * @property string|null architecture_is_64 архитектура
 * @property string|null $browser браузер
 * @property int|null $browser_id ID браузера
 *
 * @property Browser $browsers
 * @property OperatingSystem $operatingSystem
 */
class Logs extends \yii\db\ActiveRecord
{
    public const CHUNK_COUNT = 1000;
    const ARCHITECTURE_DROPDOWN = [
        '1'=>'64',
        '2'=>'86',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['request_time'], 'safe'],
            [['operating_system_id', 'browser_id'], 'integer'],
            [['ip_address', 'url', 'architecture_is_64', 'browser'], 'string', 'max' => 255],
            [['browser_id'], 'exist', 'skipOnError' => true, 'targetClass' => Browser::class, 'targetAttribute' => ['browser_id' => 'id']],
            [['operating_system_id'], 'exist', 'skipOnError' => true, 'targetClass' => OperatingSystem::class, 'targetAttribute' => ['operating_system_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip_address' => 'IP-адрес',
            'request_time' => 'время запроса',
            'url' => 'Url-адрес',
            'operating_system_id' => 'ID операционной системы',
            'architecture_is_64' => 'архитектура',
            'browser' => 'браузер',
            'browser_id' => 'ID браузера',
        ];
    }

    /**
     * Gets query for [[Browsers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBrowsers()
    {
        return $this->hasOne(Browser::class, ['id' => 'browser_id']);
    }

    /**
     * Gets query for [[OperatingSystem]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOperatingSystem()
    {
        return $this->hasOne(OperatingSystem::class, ['id' => 'operating_system_id']);
    }


    public static function getBrowserInDate(LogsSearch $searchModel)
    {
        $arrAll = self::find()->select(['request_date', 'browser_id']);

        if (!empty($searchModel->architecture)) {
            $arrAll->andWhere(['architecture_is_64'=>$searchModel->architecture === 1 ?: 0]);
        }

        if (!empty($searchModel->operating_system)) {
            $arrAll->andWhere(['operating_system_id'=>$searchModel->operating_system]);
        }

        if (!empty($searchModel->date_from)) {
            $arrAll->andWhere(['>=','request_date',$searchModel->date_from]);

        }

        if (!empty($searchModel->date_to)) {
            $arrAll->andWhere(['<=','request_date',$searchModel->date_to]);
        }

        $arrAll = $arrAll->asArray()->all();

        $arrAllBrowser = [];

        foreach ($arrAll as $item) {
            if (!array_key_exists($item['request_date'], $arrAllBrowser)) {
                $arrAllBrowser[$item['request_date']] = [
                    'allCount' => 0,
                    'percent' => 0,
                    'browsers' => []
                ];
            }
            $arrAllBrowser[$item['request_date']]['allCount'] += 1;
            if (!array_key_exists($item['browser_id'], $arrAllBrowser[$item['request_date']]['browsers'])) {
                $arrAllBrowser[$item['request_date']]['browsers'][$item['browser_id']] = 0;
            }
            $arrAllBrowser[$item['request_date']]['browsers'][$item['browser_id']] += 1;
        }

        $response = [];
        foreach ($arrAllBrowser as $key => $browser) {
            if (!array_key_exists($key, $response)) {
                $response[$key] = 0;
            }
            arsort($browser['browsers']);
            $countTop3 = array_sum(array_slice($browser['browsers'], 0, 3));
            if ($browser['allCount'] === 0) {
                $response[$key] = 0;
            } else {
                $response[$key] = round(($countTop3 / $browser['allCount']) * 100, 2);
            }

        }

        return $response;

    }

    public static function batchInsert($rows)
    {
        $chunks = array_chunk($rows,Logs::CHUNK_COUNT);
        foreach ($chunks as $chunk){
            Yii::$app->db->createCommand()->batchInsert(Logs::tableName(),
                [
                    'ip_address',
                    'request_date',
                    'request_time',
                    'url',
                    'operating_system_id',
                    'architecture_is_64',
                    'browser_id',
                ],
                $rows)->execute();
        }
    }


}
