<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel andrewnan09\blog\models\BlogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Blogs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="blog-index">


    <?php Pjax::begin(); ?>
    <?php //echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Blog', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            ['class' => 'yii\grid\ActionColumn',
                'template'=> '{view} {update} {delete} {check}',
                'buttons'=> [
                    'check'=>function($url, $model, $key){
                        return Html::a('<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-alarm-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M5.5.5A.5.5 0 0 1 6 0h4a.5.5 0 0 1 0 1H9v1.07a7.002 7.002 0 0 1 3.537 12.26l.817.816a.5.5 0 0 1-.708.708l-.924-.925A6.967 6.967 0 0 1 8 16a6.967 6.967 0 0 1-3.722-1.07l-.924.924a.5.5 0 0 1-.708-.708l.817-.816A7.002 7.002 0 0 1 7 2.07V1H5.999a.5.5 0 0 1-.5-.5zM.86 5.387A2.5 2.5 0 1 1 4.387 1.86 8.035 8.035 0 0 0 .86 5.387zM13.5 1c-.753 0-1.429.333-1.887.86a8.035 8.035 0 0 1 3.527 3.527A2.5 2.5 0 0 0 13.5 1zm-5 4a.5.5 0 0 0-1 0v3.882l-1.447 2.894a.5.5 0 1 0 .894.448l1.5-3A.5.5 0 0 0 8.5 9V5z"/>
</svg>', $url);//изменили кнопку на чакиси
                    }
                ],
                'visibleButtons'=> [
                    'check'=>function($model, $key, $index){
                        return $model->status_id === 1;
                    }
                ],
            ],

            'id',
            'title',
            //'text:ntext',
            ['attribute'=>'url', 'format'=>'raw'],
            ['attribute' => 'status_id', 'filter'=>\andrewnan09\blog\models\Blog::STATUS_LIST, 'value'=> 'statusName'], //показали выподающий список и показали он или оф в таблице обращаясь к модели (классу)  блог
            'sort',
            'smallImage:image',//вывели картинку
            'date_create:datetime',
            'date_update',
            ['attribute'=>'tag', 'value'=>'tagAsString'],



        ],

    ]); ?>
    <?php Pjax::end(); ?>
</div>
