<?php

use yii\web\View;
use yii\helpers\Url;
use yuncms\helpers\Html;
use yuncms\admin\widgets\Box;
use yuncms\admin\widgets\Toolbar;
use yuncms\admin\widgets\Alert;
use yuncms\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel yuncms\transaction\backend\models\TransactionChargeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('yuncms/transaction', 'Manage Transaction Charge');
$this->params['breadcrumbs'][] = $this->title;
$this->registerJs("jQuery(\"#batch_deletion\").on(\"click\", function () {
    yii.confirm('" . Yii::t('yuncms', 'Are you sure you want to delete this item?') . "',function(){
        var ids = jQuery('#gridview').yiiGridView(\"getSelectedRows\");
        jQuery.post(\"/transaction-charge/batch-delete\",{ids:ids});
    });
});", View::POS_LOAD);
?>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12 transaction-charge-index">
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
                            'label' => Yii::t('yuncms/transaction', 'Manage Transaction Charge'),
                            'url' => ['index'],
                        ],
                        [
                            'label' => Yii::t('yuncms/transaction', 'Create Transaction Charge'),
                            'url' => ['create'],
                        ],

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
                    'id',
                    'paid:boolean',
                    'refunded:boolean',
                    'reversed:boolean',
                    'channel',
                    'order_no',
                    'amount',
                    'currency',
                    'subject',
                    // 'body',
                    //'client_ip',
                    'transaction_no',
                    'amount_refunded',
                    // 'metadata:ntext',
                    // 'description',
                    [
                        'attribute' => 'time_paid',
                        'format' => 'datetime',
                        'filter' => \yii\jui\DatePicker::widget([
                            'model' => $searchModel,
                            'options' => [
                                'class' => 'form-control'
                            ],
                            'attribute' => 'time_paid',
                            'name' => 'time_paid',
                            'dateFormat' => 'yyyy-MM-dd'
                        ]),
                    ],
                    [
                        'attribute' => 'time_expire',
                        'format' => 'datetime',
                        'filter' => \yii\jui\DatePicker::widget([
                            'model' => $searchModel,
                            'options' => [
                                'class' => 'form-control'
                            ],
                            'attribute' => 'time_expire',
                            'name' => 'time_expire',
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
                        'header' => Yii::t('yuncms', 'Operation'),
                        'template' => '{refund} {view} {update} {delete}',
                        'buttons' => ['refund' => function ($url, $model, $key) {
                            return Html::a('<span class="fa fa-reply"></span>',
                                Url::toRoute(['refund/create', 'charge_id' => $model->id]), [
                                    'title' => Yii::t('yuncms/transaction', 'Refund'),
                                    'aria-label' => Yii::t('yuncms/transaction', 'Refund'),
                                    'data-pjax' => '0',
                                    'class' => 'btn btn-sm btn-default',
                                ]);
                        }]
                    ]

                ],
            ]); ?>
            <?php Box::end(); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
