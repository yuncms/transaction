<?php
return [
    'id'=> 'transaction',
    'migrationPath' => '@vendor/yuncms/transaction/migrations',
    'translations' => [
        'yuncms/transaction' => [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@vendor/yuncms/transaction/messages',
        ],
    ],
    'backend' => [
        'class'=>'yuncms\transaction\backend\Module'
    ],
    'frontend' => [
        'class'=>'yuncms\transaction\frontend\Module'
    ],
];