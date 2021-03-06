<?php

use yii\web\View;
use yii\helpers\Url;
use yuncms\helpers\Html;
use yuncms\admin\widgets\Box;
use yuncms\admin\widgets\Toolbar;
use yuncms\admin\widgets\Alert;
use yuncms\grid\GridView;
use yii\widgets\Pjax;
use yuncms\transaction\models\TransactionRefund;

/* @var $this yii\web\View */
/* @var $searchModel yuncms\transaction\backend\models\TransactionRefundSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('yuncms/transaction', 'Manage Transaction Refund');
$this->params['breadcrumbs'][] = $this->title;
$this->registerJs("jQuery(\"#batch_deletion\").on(\"click\", function () {
    yii.confirm('" . Yii::t('yuncms', 'Are you sure you want to delete this item?') . "',function(){
        var ids = jQuery('#gridview').yiiGridView(\"getSelectedRows\");
        jQuery.post(\"/transaction-refund/batch-delete\",{ids:ids});
    });
});", View::POS_LOAD);
?>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12 transaction-refund-index">
            <?= Alert::widget() ?>
            <?php Pjax::begin(); ?>
            <?php Box::begin([
                //'noPadding' => true,
                'header' => Html::encode($this->title),
            ]); ?>
            <div class="row">
                <div class="col-sm-4 m-b-xs">
                    <?= Toolbar::widget(['items' => [
                        [
                            'label' => Yii::t('yuncms/transaction', 'Manage Transaction Refund'),
                            'url' => ['index'],
                        ],
                        [
                            'label' => Yii::t('yuncms/transaction', 'Create Transaction Refund'),
                            'url' => ['create'],
                        ],
                        [
                            'options' => ['id' => 'batch_deletion', 'class' => 'btn btn-sm btn-danger'],
                            'label' => Yii::t('yuncms/transaction', 'Batch Deletion'),
                            'url' => 'javascript:void(0);',
                        ]
                    ]]); ?>
                </div>
                <div class="col-sm-8 m-b-xs">
                    <?= $this->render('_search', ['model' => $searchModel]); ?>
                </div>
            </div>
            <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'options' => ['id' => 'gridview'],
                'layout' => "{items}\n{summary}\n{pager}",
                'filterModel' => $searchModel,
                'columns' => [
                    [
                        'class' => 'yii\grid\CheckboxColumn',
                        "name" => "id",
                    ],
                    //['class' => 'yii\grid\SerialColumn'],
                    'id',
                    'amount',
                    'succeed:boolean',
                    [
                        'label' => Yii::t('yuncms/transaction', 'Status'),
                        'attribute' => 'status',
                        'value' => function ($model) {
                            return $model->statusText;
                        },
                        'filter' => [
                            TransactionRefund::STATUS_PENDING => Yii::t('yuncms/transaction', 'Pending'),
                            TransactionRefund::STATUS_SUCCEEDED => Yii::t('yuncms/transaction', 'Succeeded'),
                            TransactionRefund::STATUS_FAILED => Yii::t('yuncms/transaction', 'Failed'),
                        ]
                    ],
                    'description',
                    'failure_code',
                    'failure_msg',
                    'charge_id',
                    'charge_order_no',
                    'transaction_no',
                    [
                        'label' => Yii::t('yuncms/transaction', 'Funding Source'),
                        'attribute' => 'funding_source',
                        'value' => function ($model) {
                            return $model->fundingSourceText;
                        },
                        'filter' => [
                            TransactionRefund::FUNDING_SOURCE_UNSETTLED => Yii::t('yuncms/transaction','Unsettled Funds'),
                            TransactionRefund::FUNDING_SOURCE_RECHARGE => Yii::t('yuncms/transaction','Recharge Funds')
                        ]
                    ],
                    [
                        'attribute' => 'time_succeed',
                        'format' => 'datetime',
                        'filter' => \yii\jui\DatePicker::widget([
                            'model' => $searchModel,
                            'options' => [
                                'class' => 'form-control'
                            ],
                            'attribute' => 'time_succeed',
                            'name' => 'time_succeed',
                            'dateFormat' => 'yyyy-MM-dd'
                        ]),
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => 'datetime',
                        'filter' => \yii\jui\DatePicker::widget([
                            'model' => $searchModel,
                            'options' => [
                                'class' => 'form-control'
                            ],
                            'attribute' => 'created_at',
                            'name' => 'created_at',
                            'dateFormat' => 'yyyy-MM-dd'
                        ]),
                    ],
                    [
                        'class' => 'yuncms\grid\ActionColumn',
                        'template' => '{view} {update} {delete}',
                        //'buttons' => [
                        //    'update' => function ($url, $model, $key) {
                        //        return $model->status === 'editable' ? Html::a('Update', $url) : '';
                        //    },
                        //],
                    ],
                ],
            ]); ?>
            <?php Box::end(); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
