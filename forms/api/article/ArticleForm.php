<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/3
 * Time: 11:33
 */

namespace app\forms\api\article;


use app\core\response\ApiCode;
use app\forms\common\article\CommonArticle;
use app\models\Model;

class ArticleForm extends Model
{
    public $article_id;

    public function rules()
    {
        return [
            [['article_id'], 'required'],
            [['article_id'], 'integer'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $com = new CommonArticle($this->attributes, true);
			$res = $com->getDetail();
			return [
			    'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success',
                'data' => [
                    'article' => $res
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
