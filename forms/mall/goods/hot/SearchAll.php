<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

declare(strict_types=1);

namespace app\forms\mall\goods\hot;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonHotSearch;
use app\models\GoodsHotSearch;
use app\models\Model;

class SearchAll extends Model
{
    public $sort;
    public $type;
    public $goods_id;

    public function rules()
    {
        return [
            [['goods_id', 'sort', 'type'], 'required'],
            [['type'], 'in', 'range' => [GoodsHotSearch::TYPE_HOT_SEARCH, GoodsHotSearch::TYPE_GOODS]],
            [['goods_id', 'sort'], 'integer'],
            [['type'], 'string', 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'goods_id' => '商品',
            'type' => '类型',
            'sort' => '排序',
        ];
    }

    public function select()
    {
        $common = new CommonHotSearch();
        $data = $common->getAll();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '获取成功',
            'data' => [
                'list' => $data
            ],
        ];
    }

    public function changeSort()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $goods_id = (int)$this->goods_id;
            $model = CommonHotSearch::getHotSearchOne($goods_id, $this->type);
            if (!$model) {
                throw new \Exception('数据不存在');
            }
            $temp = new SearchEdit();
            $temp->type = $this->type;
            $model->sort = $temp->formatSort(false, $model->sort, (int)$this->sort);
            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            }
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '修改成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
