<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/3
 * Time: 10:05
 */

namespace app\forms\common\article;


use app\models\Article;
use yii\base\BaseObject;

class CommonArticle extends BaseObject
{
    public $mall;
    public $article_id;
    public $isArray = false;

    public function __construct(array $config = [], $isArray = false)
    {
        $this->mall = \Yii::$app->mall;
        $this->isArray = $isArray;
        parent::__construct($config);
    }

    /**
     * @return array|\yii\db\ActiveRecord|null
     * @throws \Exception
     */
    public function getDetail()
    {
        $detail = Article::find()->where([
            'id' => $this->article_id, 'mall_id' => $this->mall->id,
            'is_delete' => 0
        ])->asArray($this->isArray)->one();

        if (!$detail) {
            throw  new \Exception('文章不存在或已删除');
        }
        return $detail;
    }
}
