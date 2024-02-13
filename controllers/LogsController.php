<?php

namespace app\controllers;

use app\models\Logs;
use app\models\LogsSearch;
use yii\base\Controller;
use yii\helpers\ArrayHelper;

class LogsController extends Controller
{

    public function actionIndex()
    {


        $searchModel = new LogsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $getBrowserInDate = Logs::getBrowserInDate($searchModel);
        $percent = [];
        $topBrowser = ArrayHelper::getColumn($dataProvider->getModels(), 'request_count');

        foreach (ArrayHelper::getColumn($dataProvider->getModels(), 'request_date') as $item) {
            if (ArrayHelper::keyExists($item, $getBrowserInDate)) {
                $percent[] = $getBrowserInDate[$item];
            }
        }

        return $this->render('index', [
            'sqlProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'percent' => $percent,
            'topBrowser' => $topBrowser
        ]);
    }
}