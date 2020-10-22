<?php
/**
 * link: 域名
 * copyright: Copyright (c) 2018 人人禾匠商城
 * author: wxf
 */

namespace app\plugins\copy\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\copy\models\CopyStore;;

class StoreForm extends Model
{
    public $id;
    public $page;
    public $keyword;


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword'], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '会员ID',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = CopyStore::find()->where([
            'is_delete' => 0,
        ]);

        $list = $query->page($pagination)->orderBy(['id' => SORT_DESC])->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

}
