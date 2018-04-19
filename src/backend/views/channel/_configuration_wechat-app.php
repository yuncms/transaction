<?php

use yuncms\admin\widgets\ActiveForm;
use yuncms\helpers\Html;

/* @var \yii\web\View $this */
/* @var ActiveForm $form */


?>


<?= $form->field($model, 'appId')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'apiKey')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'mchId')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'privateKey')->textarea() ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'publicKey')->textarea() ?>
<div class="hr-line-dashed"></div>

<?= $form->field($model, 'signType')->textarea() ?>
<div class="hr-line-dashed"></div>


<div class="form-group">
    <div class="col-sm-4 col-sm-offset-2">
        <?= Html::submitButton(Yii::t('yuncms', 'Save'), ['class' => 'btn btn-primary']) ?>
    </div>
</div>