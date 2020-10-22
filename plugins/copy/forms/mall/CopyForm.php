<?php
/**
 * link: 域名
 * copyright: Copyright (c) 2018 人人禾匠商城
 * author: wxf
 */

namespace app\plugins\copy\forms\mall;

use app\core\response\ApiCode;
use app\forms\mall\goods\GoodsEditForm;
use app\helpers\CurlHelper;
use app\models\GoodsCats;
use app\models\Model;

class CopyForm extends Model
{
    public $id;
    public $cat_id;
    public $url_store_id;
    public $keyword;
    public $url;

    public function rules()
    {
        return [
            [['id', "cat_id"], 'integer'],
            [['url_store_id', 'url'], 'required'],
            [['keyword', 'url'], 'string']
        ];
    }


    public function getCatAllList()
    {


        try {
            $query = GoodsCats::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
            ]);

            $list = $query->all();
            $newList = [];
            /** @var GoodsCats[] $list */
            foreach ($list as $item) {
                $newItem = [];
                $newItem['value'] = $item->id;
                $newItem['label'] = $item->name;
                $newList[] = $newItem;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $newList,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }


}
