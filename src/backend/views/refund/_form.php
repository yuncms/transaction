<?php

use yuncms\helpers\Html;
use yuncms\admin\widgets\ActiveForm;
use yuncms\transaction\models\TransactionRefund;

/* @var \yii\web\View $this */
/* @var yuncms\transaction\models\TransactionRefund $model */
/* @var ActiveForm $form */
?>
<?php $form = ActiveForm::begin(['layout' => 'horizontal', 'enableAjaxValidation' => true, 'enableClientValidation' => false,]); ?>

<?= $form->field($model, 'charge_id')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'amount')->textInput() ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'charge_order_no')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'transaction_no')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'funding_source')->dropDownList([
    TransactionRefund::FUNDING_SOURCE_UNSETTLED => Yii::t('yuncms/transaction','Unsettled Funds'),
    TransactionRefund::FUNDING_SOURCE_RECHARGE => Yii::t('yuncms/transaction','Recharge Funds')
]) ?>
<div class="hr-line-dashed"></div>

<div class="form-group">
    <div class="col-sm-4 col-sm-offset-2">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('yuncms', 'Create') : Yii::t('yuncms', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>

