<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use vova07\imperavi\Widget;


/* @var $this yii\web\View */
/* @var $model andrewnan09\blog\models\Blog */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="blog-form">


    <?php $form = ActiveForm::begin([

    ]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'text')->widget(Widget::className(), [
        'settings' => [
            'lang' => 'ru',
            'minHeight' => 200,
            //'formating'=>'p','h1','h2',
            'imageUpload' => \yii\helpers\Url::to(['/site/save-redactor-img','sub'=>'blog/1']),//будет вести к контроллеру сайт в метот save-redactor-img
            'plugins' => [
                'clips',
                'fullscreen',
            ]
        ]
    ])?>
    <div class="row">
        <?= $form->field($model, 'file',['options'=>['class'=>'col-xs-6']])->widget(\kartik\file\FileInput::classname(), [
            'options' => ['accept' => 'image/*'],
            'pluginOptions' => [
                'showCaption' => false,
                'showRemove' => false,
                'showUpload' => false,
                'browseClass' => 'btn btn-primary btn-block',
                'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
                'browseLabel' =>  'Выбрать фото'
            ],
        ]);?>

        <?= $form->field($model, 'url',['options'=>['class'=>'col-xs-6']])->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'status_id',['options'=>['class'=>'col-xs-6']])->dropDownList(\andrewnan09\blog\models\Blog::STATUS_LIST) ?>

        <?= $form->field($model, 'sort',['options'=>['class'=>'col-xs-6']])->textInput() ?>


        <?php echo $form->field($model, 'tags_array',['options'=>['class'=>'col-xs-6']])->widget(\kartik\select2\Select2::classname(), [
            'data' => \yii\helpers\ArrayHelper::map(\andrewnan09\blog\models\Tag::find()->all(), 'id', 'name'),

            'options' => [
                'placeholder' => 'Выбирите Teg ...',
                'multiple' => true
            ],

            'pluginOptions' => [
                'initialize' => true,
            ],
        ]);

        ?>
    </div>


<pre><?//php print_r($model->imagesLinksData);?></pre>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>


    <?= \kartik\file\FileInput::widget([
        'name'=>'ImageManager[attachment]',

        'options'=>[
            'multiple'=>true
        ],
        'pluginOptions' => [
            'deleteUrl' => Url::toRoute(['/blog/blog/delete-image']),//
            'initialPreview'=> $model->imagesLinks,//приходит массив с картинками с их урл
            'initialPreviewAsData'=>true,
            'overwriteInitial'=>false,
            'initialPreviewConfig'=>$model->imagesLinksData,
            'uploadUrl' => Url::to(['/site/save-img']),
            'uploadExtraData' => [
                'ImageManager[class]' => $model->formName(),
                'ImageManager[item_id]' => $model->id
            ],
            'maxFileCount' => 10
        ],
        'pluginEvents' => [
            'filesorted' => new \yii\web\JsExpression('function(event, params){//когда перетаскиваем картинку, вызывается функция которая запускает пост запрос на урл Url::toRoute(["/blog/sort-image","id"=>$model->id]) и будут передоваться параметры
                  $.post("'.Url::toRoute(["/blog/blog/sort-image","id"=>$model->id]).'",{sort: params});
            }')
        ],
    ]);?>



</div>

<!--<pre><?php //print_r(\yii\helpers\ArrayHelper::map($model->getTag()->all(), 'id', 'name')); ?></pre>-->

