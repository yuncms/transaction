<?php

use yuncms\admin\widgets\ActiveForm;

/* @var \yii\web\View $this */
/* @var ActiveForm $form */
?>

<?= $form->field($model, 'configuration[fee_rate]')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'configuration[alipay_app_id]')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'configuration[alipay_pid]')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'configuration[alipay_account]')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'configuration[encryptVersion]')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'configuration[alipay_app_public_key]')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'configuration[alipay_mer_app_private_key]')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>