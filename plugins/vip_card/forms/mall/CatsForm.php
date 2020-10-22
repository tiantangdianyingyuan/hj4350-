<?php

namespace app\plugins\vip_card\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonCats;
use app\models\GoodsCats;
use app\models\Model;

class CatsForm extends Model
{
    public $id;
    public $sort;
    public $cat_id;
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'sort', 'cat_id'], 'integer'],
            [['sort'], 'default', 'value' => 0],
            [['keyword'], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'sort' => '排序',
            'cat_id' => '分类ID'
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $cats = CommonCats::getAllCats();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '获取成功',
            'data' => [
                'list' => $cats,
            ]
        ];
    }
}
