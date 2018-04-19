<?php

use yuncms\admin\widgets\ActiveForm;
use yuncms\helpers\Html;
use yuncms\transaction\channels\wechat\Wechat;

/* @var \yii\web\View $this */
/* @var ActiveForm $form */


?>


<?= $form->field($model, 'appId')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'apiKey')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'mchId')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'signType')->dropDownList([
    'MD5' => Wechat::SIGNATURE_METHOD_MD5,
    'HMAC-SHA256' => Wechat::SIGNATURE_METHOD_SHA256
]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'privateKey')->textarea() ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'publicKey')->textarea() ?>
<div class="hr-line-dashed"></div>


<div class="form-group">
    <div class="col-sm-4 col-sm-offset-2">
        <?= Html::submitButton(Yii::t('yuncms', 'Save'), ['class' => 'btn btn-primary']) ?>
    </div>
</div>