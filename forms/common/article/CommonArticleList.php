<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/3
 * Time: 10:09
 */

namespace app\forms\common\article;


use app\models\Article;
use yii\base\BaseObject;

class CommonArticleList extends BaseObject
{
    public $mall;

    public $page;
    public $limit;
    public $isArray = false;
    public $article_cat_id;

    public function __construct(array $config = [])
    {
        $this->mall = \Yii::$app->mall;
        parent::__construct($config);
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function search()
    {
        $list = Article::find()->where([
                'mall_id' => $this->mall->id,
                'is_delete' => 0,
                'status' => 1,
            ])
            ->orderBy(['sort' => SORT_ASC])
            ->page($pagination, $this->limit, $this->page)
            ->asArray($this->isArray)
            ->all();

        return $list;
    }
}
