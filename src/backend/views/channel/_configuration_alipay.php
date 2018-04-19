<?php

use yuncms\admin\widgets\ActiveForm;

/* @var \yii\web\View $this */
/* @var ActiveForm $form */
?>

<?= $form->field($model, 'fee_rate')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'alipay_app_id')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'alipay_pid')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'alipay_account')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'encryptVersion')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'alipay_app_public_key')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'alipay_mer_app_private_key')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>