<?php

namespace andrewnan09\blog\models;

use common\components\behavior\StatusBehavior;
use common\models\ImageManager;
use common\models\User;
use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\UploadedFile;
use yii\helpers\Url;

/**
 * This is the model class for table "blog".
 *
 * @property int $id
 * @property string $title
 * @property string $text
 * @property string $url
 * @property int $status_id
 * @property int $sort
 */
class Blog extends ActiveRecord
{
    const STATUS_LIST = ['off','on'];//создаем константу
    const IMAGES_SIZE =[
        ['50', '50'],
        ['800', null],
    ];
    public $tags_array;//для выводов тегов
    public $file;//сам файл
    public $post;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'blog';
    }


    public function behaviors()//здесь поведение указывать времь при изменении и при создании
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['date_create'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['date_update'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                 'value' => new Expression('NOW()'),
            ],


            'statusBehavior'=>[//настраиваем свое повидение
                'class' => StatusBehavior::className(),
                'statusList'=>self::STATUS_LIST,

            ]

        ];
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'url'], 'required'],
            [['text'], 'string'],
            [['url'], 'unique'],//проверяем на уникальность и не дает сохранить
            [['status_id', 'sort'], 'integer'],
            [['sort'], 'integer', 'min'=>1, 'max'=>99],
            [['title', 'url'], 'string', 'max' => 150],
            [['image'], 'string', 'max'=>100],
            [['file'], 'image'],
            [['tags_array', 'date_create', 'date_update'], 'safe']
            ];

    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Значение',
            'text' => 'Текст',
            'url' => 'ЧПУ',
            'status_id' => 'Статус',
            'sort' => 'Сортировка',
            'tags_array' => 'Теги',
            'tagAsString' => 'Теги',
            'author.username' => 'Имя автора',
            'author.email' => 'Почта автора',
            'tag' => 'Теги',
            'date_create'=>'Время создания',
            'date_update'=>'Время изменения',
            'image'=>'Картинка',
            'file'=>'Картинка'

        ];
    }
    /*public static function getStatusList(){//выподающий список постов в админке
        return ['off', 'on'];
    }
    public function getStatusName(){//название столбцов в таблице офф или он
        $list = self::getStatusList();
        return $list[$this->status_id];
    }*/
    public function getAuthor()
    {
       return $this->hasOne(User::className(), ['id'=>'user_id']);//автор это одна запись в модели Юзер в которой йди равно юзер_фйди в данной конкретной модели
    }
//связь с фоторамой
    public function getImages(){
        return $this->hasMany(ImageManager::className(), ['item_id'=>'id'])
            ->andWhere(['class'=>self::tableName()])->orderBy('sort');//orderBy('sort') чтобы выводились в правильном порядке
    }

    //сортировка картинок
    public function getImagesLinks()
    {
        return ArrayHelper::getColumn($this->images,'imageUrl');//получаем все записи берем только урл картинки
    }
    public function getImagesLinksData()
    {
        return ArrayHelper::toArray($this->images,[//берем из гет имеджес и передаем кепшен и ключ
                ImageManager::className() => [
                    'caption'=>'name',
                    'key'=>'id',
                ]]
        );
    }

    public function getBlogTag()
    {
        return $this->hasMany(BlogTag::className(), ['blog_id'=>'id']);//автор это одна запись в модели Юзер в которой йди равно юзер_фйди в данной конкретной модели
    }
    public function getTag(){
        return $this->hasMany(Tag::className(), ['id'=>'tag_id'])->via('blogTag');
    }

    public function getTagAsString(){//вывод тегов
        $arr = \yii\helpers\ArrayHelper::map($this->tag, 'id', 'name');
        return implode(', ', $arr);
    }

    public function getSmallImage(){//вывод миниатюрки картинки
        if($this->image){//если картинка есть то
            $path =str_replace('admin.', '', Url::home(true)) . 'uploads/images/blog/50x50/' . $this->image;//в редактор надо вернуть строчку урл,  str_replace убирает admin
        }else {
            $path = str_replace('admin.', '', Url::home(true)) . 'uploads/images/hi.jpg';//в редактор надо вернуть строчку урл,  str_replace убирает admin
        }
        return $path;
    }





    public function afterFind()//данная функция выполняется после заполнения БД
    {
        $this->tags_array = $this->tag;//заполнили пустую tags_array


    }

    public function beforeSave($insert)//эта функция вызывается перед сохранением модели
    {
        if($file = UploadedFile::getInstance($this,'file')){//проверяем существунт ли файл и потом в этот фал записываем сам файл(объект) и передаем его  в модель $this тоесть в Блог атрибут файл (getInstance - одно значение!!! getInstances - массив)
            $dir = Yii::getAlias('@images').'/blog/';//получаем строку с путем
            $oldimg = $this->findOne(['id' => Yii::$app->request->get('id')]); //Получаем  название файла из базы.
            if ($oldimg->image) { // Проверяем есть ли в базе имя ранее сохраненного файла. Если есть, выполняем поиск файла и последующее удаление из папок
                if (file_exists($dir . $oldimg->image)) {// Только вложенных действий дофига. Желательно раскинуть по функциям.

                    unlink($dir . $oldimg->image);
                }
                if (file_exists($dir . '/50x50/' . $oldimg->image)) {
                    unlink($dir . '/50x50/' . $oldimg->image);
                }
                if (file_exists($dir . '/800x/' . $oldimg->image)) {
                    unlink($dir . '/800x/' . $oldimg->image);
                }
            }

            $this->image = strtotime('now').'_'.Yii::$app->getSecurity()->generateRandomString(6).'.'.$file->extension;//то добовляем уникальное имя Yii::$app->getSecurity()->generateRandomString(6) также спроверяет и создает имя из 6 символов/file->extension ставим расширение

            $file->saveAs($dir.$this->image);//сохраняем файл в блог
            $imge = Yii::$app->image->load($dir.$this->image);//дадее с помощью расширеня которое мы подключили загружаем картинку которую сохранили
            //делаем преобразования
            $imge->background('#fff',0);//задаем беграунд
            $imge->resize('50','50', yii\image\drivers\Image::INVERSE);//изменяем размер 50% высоты и 50% ширины
            $imge->crop('50','50');//потом обрежем 50 на 50 картинка будет хорошо вписана
            $imge->save($dir.'/50x50/'.$this->image, 90);//сохроняем в папку 50/50 качество 90%
            $imge = Yii::$app->image->load($dir.$this->image);//берем эту же картинку
            $imge->background('#fff',0);
            $imge->resize('800', null , yii\image\drivers\Image::INVERSE); //деоаем ее 800ширину и высота автоматом
            $imge->save($dir.'/800x/'.$this->image, 90);//сохраняем

        }


        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)//эта функция вызывается после сохранения модели сохранения в БД
    {
        parent::afterSave($insert, $changedAttributes); //ниже пишем код после сохранения модели БЛОГ
        $arr = \yii\helpers\ArrayHelper::map($this->tag, 'id', 'id');//перед сохранением мы кладем в переменную Васю и Петю

        if (!empty($this->tags_array)) {
            foreach ($this->tags_array as $one) {
                if (!in_array($one, $arr)) {//проверяем есть ли $one в массиве $arr ели нет то  делаем
                    $model = new BlogTag();
                    $model->blog_id = $this->id;

                    $model->tag_id = $one;

                    $model->save();
                }
                if (isset($arr[$one])) {


                    unset($arr[$one]);//

                }
            }


        }
        BlogTag::deleteAll(['tag_id' => $arr, 'blog_id' => $this->id]); //метод передаем условия при котором будет удалятся условия где тег айди и сюдаа даем массив оставшихся значений
        // VarDumper::dump($arr);
        //работает только без ивентов
    }





    public function beforeDelete()
    {
        if (parent::beforeDelete()) {

            $dir = Yii::getAlias('@images').'/blog/';
            $name= ImageManager::find()->where('item_id = :id',[':id'=>$this->id])->one();
                //VarDumper::dump($name['name']);
            if(!empty($this->image)) {
                if (file_exists($dir . $this->image)) {
                    unlink($dir . $this->image);
                }
                //VarDumper::dump(self::IMAGES_SIZE);
                foreach (self::IMAGES_SIZE as $size) {
                    $size_dir = $size[0] . 'x';
                    if ($size[1] !== null)
                        $size_dir .= $size[1];
                    //VarDumper::dump($size_dir);
                    if (file_exists($dir . $size_dir . '/' . $this->image)) {
                        unlink($dir . $size_dir . '/' . $this->image);
                    }
                }

                if (!empty($name['name'])) {
                    unlink($dir . $name['name']);

                }
                //VarDumper::dump($this->id);
                ImageManager::deleteAll(['item_id' => $this->id]);
                BlogTag::deleteAll(['blog_id' => $this->id]);//удаляем связ блогТаг напрямую, события блог.таг не сработают если есть
                /*foreach ($this->blogTag as $one){//$this->blogTag  и $this->getBlogTag->all() -тоже самое
                    $one->delete();//тоже удаление только сдесь события если есть сработают
                }*/
            }else{

                if (!empty($name['name'])) {
                   // VarDumper::dump($this->id);
                    unlink($dir . $name['name']);
                                  }
                ImageManager::deleteAll(['item_id' => $this->id]);
                BlogTag::deleteAll(['blog_id' => $this->id]);
            }

                return true;
    } else {

                return false;
            }
        }
    }



