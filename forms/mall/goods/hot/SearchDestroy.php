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

class SearchDestroy extends Model
{
    public $goods_id;
    public $type;
    public function rules()
    {
        return [
            [['goods_id', 'type'], 'required'],
            [['type'], 'in', 'range' => [GoodsHotSearch::TYPE_HOT_SEARCH, GoodsHotSearch::TYPE_GOODS]],
            [['goods_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'goods_id' => '商品',
            'type' => '类型',
        ];
    }

    public function destory()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $goods_id = (int)$this->goods_id;
            if ($this->type === GoodsHotSearch::TYPE_GOODS) {
                $return = $this->goods($goods_id, $model);
            }
            if ($this->type === GoodsHotSearch::TYPE_HOT_SEARCH) {
                $return = $this->hotSearch($goods_id, $model);
            }
            if (!$return) {
                throw new \Exception($this->getErrorMsg($model));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        };
    }

    private function hotSearch(int $goods_id, &$model)
    {
        $model = CommonHotSearch::getHotSearchOne($goods_id, GoodsHotSearch::TYPE_HOT_SEARCH);
        if (empty($model)) {
            throw new \Exception('记录不存在');
        }
        $model->is_delete = 1;
        return $model->save();
    }

    private function goods(int $goods_id, &$auto)
    {
        $model = CommonHotSearch::getGoodsOne($goods_id);
        if (empty($model)) {
            throw new \Exception('记录不存在');
        }
        $auto = CommonHotSearch::getHotSearchOne($goods_id, GoodsHotSearch::TYPE_GOODS);
        if (empty($auto)) {
            $auto = new GoodsHotSearch();
            $auto->mall_id = \Yii::$app->mall->id;
            $auto->sort = 0;
            //无用
            $auto->type = GoodsHotSearch::TYPE_GOODS;
            $auto->goods_id = $model->id;
            $auto->title = $model->getName();
            //$auto->title = mb_substr($model->getName(),0,15);
        }
        $auto->is_delete = 1;
        return $auto->save();
    }
}
