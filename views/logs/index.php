<?php

use app\models\Browser;
use dosamigos\chartjs\ChartJs;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;

/** @var array $percent */
/** @var array $topBrowser */
/** @var yii\web\View $this */
/** @var yii\data\SqlDataProvider $sqlProvider */
/** @var app\models\Logs $getBrowserInDate */
/** @var app\models\LogsSearch $searchModel */


$this->title = 'LOGS';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php if (!$searchModel->getErrors()): ?>
        <?= GridView::widget([
            'dataProvider' => $sqlProvider,
            'columns' => [
                [
                    'attribute' => 'request_date',
                    'label' => 'Дата',
                ],
                [
                    'attribute' => 'request_count',
                    'label' => 'Число запросов',
                ],
                [
                    'attribute' => 'best_url',
                    'label' => 'Самый популярный URL',
                ],
                [
                    'attribute' => 'best_browser',
                    'label' => 'Самый популярный браузер',
                    'value' => function ($data) {
                        return ArrayHelper::getValue(Browser::find()->select(['title'])->where(['id' => $data['best_browser']])->one(), 'title');
                    }
                ]
            ],
        ]); ?>

        <?php if (!empty($sqlProvider->getModels())):?>
        <?php
        echo ChartJs::widget([
            'type' => 'line',
            'options' => [
                'height' => 400,
                'width' => 400
            ],
            'data' => [
                'labels' => ArrayHelper::getColumn($sqlProvider->getModels(), 'request_date'),
                'datasets' => [
                    [
                        'label' => "Число запросов",
                        'backgroundColor' => "rgba(179,181,198,0.2)",
                        'borderColor' => "rgba(179,181,198,1)",
                        'pointBackgroundColor' => "rgba(179,181,198,1)",
                        'pointBorderColor' => "#fff",
                        'pointHoverBackgroundColor' => "#fff",
                        'pointHoverBorderColor' => "rgba(179,181,198,1)",
                        'data' => $topBrowser,
                    ],
                    [
                        'label' => "доля (% от числа запросов) для трех самых популярных браузеров",
                        'backgroundColor' => "rgba(255,99,132,0.2)",
                        'borderColor' => "rgba(255,99,132,1)",
                        'pointBackgroundColor' => "rgba(255,99,132,1)",
                        'pointBorderColor' => "#fff",
                        'pointHoverBackgroundColor' => "#fff",
                        'pointHoverBorderColor' => "rgba(255,99,132,1)",
                        'data' => $percent
                    ]
                ]
            ]
        ]);

        ?>
        <?php endif;?>

    <?php endif; ?>
</div>
