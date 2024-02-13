<?php

namespace app\models;

use DateTime;
use yii\base\Model;
use yii\data\SqlDataProvider;

/**
 * CalculationsLogsSearche represents the model behind the search form of `app\models\CalculationsLogs`.
 */
class LogsSearch extends Logs
{
    public $date_from;
    public $date_to;
    public $operating_system;
    public $architecture;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['operating_system', 'architecture'], 'safe'],
            [['date_from', 'date_to'], 'date', 'format' => 'php:Y-m-d'],
            [['date_to'], 'checkDate'],
        ];
    }

    public function checkDate($attributes)
    {
        if(!empty($this->date_from) && !empty($this->date_to)){
            $date1 = new DateTime($this->date_from);
            $date2 = new DateTime($this->date_to);

            if($date1 > $date2){
                $this->addError($attributes,'Дата начала не может быть больше даты окончания!');
            }else{

                $interval = $date1->diff($date2);
                if($interval->y > 0){
                    $this->addError($attributes,'Интервал между датами не должен превышать год.');
                }
            }

        }
    }
    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * @param $params
     * @return SqlDataProvider
     */
    public function search($params)
    {
        $queryParams = [];

        $sql = 'SELECT request_date, COUNT(*) AS request_count, 
(SELECT ll.url FROM logs AS ll WHERE l.request_date=ll.request_date GROUP BY ll.url ORDER BY COUNT(ll.url) DESC LIMIT 1) AS best_url,
(SELECT lll.browser_id FROM logs AS lll WHERE l.request_date=lll.request_date GROUP BY lll.browser_id ORDER BY COUNT(lll.browser_id) DESC LIMIT 1) AS best_browser
FROM logs l
where 1=1';

        $sqlProvider = new SqlDataProvider([
            'sql' => $sql,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['best_browser' => SORT_ASC],
                'attributes' => [
                    'request_date',
                    'request_count',
                    'best_url',
                    'best_browser',
                ],
            ],

        ]);

        $this->load($params);
        if(!$this->validate()){
            $sqlProvider->sql .= ' GROUP BY request_date';

            return $sqlProvider;
        }

        if (!empty($this->architecture)) {
            $sqlProvider->sql .= ' AND l.architecture_is_64 = :architecture';
            $queryParams[':architecture'] = (int) $this->architecture === 1 ?: 0;
            $sqlProvider->params = $queryParams;
        }

        if (!empty($this->operating_system)) {
            $sqlProvider->sql .= ' AND l.operating_system_id = :operating_system';
            $queryParams[':operating_system'] = (int) $this->operating_system;
            $sqlProvider->params = $queryParams;
        }

        if (!empty($this->date_from)) {
            $sqlProvider->sql .= ' AND l.request_date >= :date_from';
            $queryParams[':date_from'] = $this->date_from;
            $sqlProvider->params = $queryParams;
        }

        if (!empty($this->date_to)) {
            $sqlProvider->sql .= ' AND l.request_date <= :date_to';
            $queryParams[':date_to'] = $this->date_to;
            $sqlProvider->params = $queryParams;
        }

        $sqlProvider->sql .= ' GROUP BY request_date';

        return $sqlProvider;
    }
}
