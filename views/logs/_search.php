<?php

use kartik\date\DatePicker;
use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\LogsSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="calculations-logs-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>


    <?= $form->field($model, 'architecture')->dropDownList(\app\models\Logs::ARCHITECTURE_DROPDOWN, ['prompt' => 'Не выбрано']) ?>

    <?= $form->field($model, 'operating_system')->dropDownList(\app\models\OperatingSystem::getDropdown(), ['prompt' => 'Не выбрано']) ?>

    <?php

    echo DatePicker::widget([
        'model' => $model,
        'attribute' => 'date_from',
        'value' => $model->date_from,
        'type' => DatePicker::TYPE_RANGE,
        'attribute2' => 'date_to',
        'value2' => $model->date_to,
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd',
        ]
    ]);
    if($error = $model->getFirstError('date_to')){
        echo $error;
    }
    ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
