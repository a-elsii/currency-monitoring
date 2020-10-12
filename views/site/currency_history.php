<?php
/**
 * @var $currency_array array
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>

    <div class="category-form box box-primary">
        <?php $form = ActiveForm::begin([
            'action' => ['/site/currency-history'],
            'method' => 'get',
        ]); ?>
        <div class="box-body table-responsive">
            <?= Html::input('date', 'date_start'); ?>
            <?= Html::input('date', 'date_end'); ?>
            <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-success btn-flat']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

<?= $this->render('/layouts/show_table_currency', ['currency_array' => $currency_array]); ?>