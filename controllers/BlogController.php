<?php

namespace andrewnan09\blog\controllers;

use andrewnan09\blog\models\BlogTag;
use common\models\ImageManager;
use Yii;
use andrewnan09\blog\models\Blog;
use andrewnan09\blog\models\BlogSearch;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * BlogController implements the CRUD actions for Blog model.
 */
class BlogController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'delete-image' => ['POST'],//только пост метод допускается
                    'sort-image' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Blog models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BlogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionFff()
    {//создаем  записей от 0 до 29 те 30
        for ($i = 0; $i < 20; $i++) {
            $model = new Blog();
            $model->title = 'Заголовок №' . $i;
            $model->sort = 50;
            $model->status_id = 1;
            $model->url = 'url_' . $i;
            $model->save();
        }
        return '12345';
    }

    /**
     * Displays a single Blog model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Blog model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Blog();
        //VarDumper::dump($model);
        $model->sort = 50;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            //VarDumper::dump(Yii::$app->request->post());
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Blog model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
           return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Blog model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionDeleteImage()
    {
        if(($model = ImageManager::findOne(Yii::$app->request->post('key'))) and $model->delete()){
            return true;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

    }

    public function actionSortImage($id)
    {
        if(Yii::$app->request->isAjax){//проверяе аджакс это запрос или нет
            $post = Yii::$app->request->post('sort');//загоняем в пост все что есть в сорт новый и старый индекс
            if($post['oldIndex'] > $post['newIndex']){//проверяем старый индекс больше нового
                $param = ['and',['>=','sort',$post['newIndex']],['<','sort',$post['oldIndex']]];//если больше то картинка переместилась в верх и нас интересую толко этот диапазон
            	$counter = 1;
		}else{
                $param = ['and',['<=','sort',$post['newIndex']],['>','sort',$post['oldIndex']]];
                $counter = -1;
            }
            ImageManager::updateAllCounters(['sort' => $counter], [
                'and',['class'=>'blog','item_id'=>$id],$param
            ]);
            ImageManager::updateAll(['sort' => $post['newIndex']], [
                'id' => $post['stack'][$post['newIndex']]['key']
            ]);
            return true;
        }
        throw new MethodNotAllowedHttpException();//если это не аджакс то без аждакса на этот урл перейти нельзя было
    }



    /**
     * Finds the Blog model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Blog the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        //(($model = Blog::findOne($id)) !== null)//было так изменили, чтобы уменьшить количество запросов к бд
        if (($model = Blog::find()->with('tag')->andWhere(['id' => $id])->one()) !== null) {
            return $model;
        } else {

            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
