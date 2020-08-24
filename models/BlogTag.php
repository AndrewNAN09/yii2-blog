<?php

namespace andrewnan09\blog\models;

use Yii;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "blog_tag".
 *
 * @property int $id
 * @property int $blog_id
 * @property int $tag_id
 */
class BlogTag extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'blog_tag';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['blog_id', 'tag_id'], 'required'],
            [['blog_id', 'tag_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'blog_id' => 'Blog ID',
            'tag_id' => 'Tag ID',
        ];
    }
    public function getTag()
    {
        return $this->hasOne(Tag::className(), ['id'=>'tag_id']);//автор это одна запись в модели Юзер в которой йди равно юзер_фйди в данной конкретной модели
    }

    /*public function deleteTag($id){
       $model = BlogTag::find()->where(['blog_id'=>$id])->one();
        $model->delete();
}*/

}
