<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/3
 * Time: 9:59
 */

namespace app\forms\api\article;


use app\core\response\ApiCode;
use app\forms\common\article\CommonArticleList;
use app\models\Model;

class ArticleListForm extends Model
{
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['page', 'limit'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $config = [
            'page' => $this->page,
            'limit' => $this->limit,
            'isArray' => true,
        ];
        $com = new CommonArticleList($config);

        $list = $com->search();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => 'success',
            'data' => [
                'list' => $list
            ]
        ];
    }
}
