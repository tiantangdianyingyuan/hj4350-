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
use app\models\Goods;
use app\models\GoodsHotSearch;
use app\models\Model;

class SearchEdit extends Model
{
    public $goods_id;
    public $type;
    public $id;
    public $sort;
    public $title;

    public function rules()
    {
        return [
            [['goods_id', 'type'], 'required'],
            [['type'], 'in', 'range' => [GoodsHotSearch::TYPE_HOT_SEARCH, GoodsHotSearch::TYPE_GOODS]],
            [['goods_id', 'id', 'sort'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'goods_id' => '商品',
            'type' => '类型',
            'title' => '热搜词',
            'sort' => '排序',
        ];
    }

    public function detail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $goods_id = (int)$this->goods_id;
            if ($this->type === GoodsHotSearch::TYPE_HOT_SEARCH) {
                $model = CommonHotSearch::getHotSearchOne($goods_id, GoodsHotSearch::TYPE_HOT_SEARCH);
            }
            if ($this->type === GoodsHotSearch::TYPE_GOODS) {
                $model = CommonHotSearch::getGoodsOne($goods_id);
            }
            if (empty($model)) {
                throw new \Exception('记录不存在');
            }
            $real = (new CommonHotSearch())->format([$model], $this->type);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '获取成功',
                'data' => array_shift($real),
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function formatSort(bool $isAdd, $beforeSort, $afterSort): int
    {
        $this->type === GoodsHotSearch::TYPE_HOT_SEARCH or die('禁止');

        $commonWhere = [
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'type' => GoodsHotSearch::TYPE_HOT_SEARCH,
        ];
        if ($isAdd) {
            $exists = GoodsHotSearch::find()->where($commonWhere)->andWhere(['sort' => $afterSort])->exists();
            if (!$exists) {
                $data = (new CommonHotSearch())->getAll();
                $count = count($data);
                return $afterSort > $count ? $count < 10 ? ++$count : 10 : $afterSort;
            }
            /** @var GoodsHotSearch $big */
            $big = GoodsHotSearch::find()->where($commonWhere)
                ->andWhere(['>=', 'sort', $afterSort])
                ->orderBy(['sort' => SORT_ASC])
                ->all();
            for ($i = 0; $i < count($big); $i++) {
                if ($i == 0 || $big[$i]->sort == $big[$i - 1]->sort) {
                    $hasDelete = $big[$i]->sort == $afterSort || $big[$i]->sort > 9;
                    $hasDelete ? $big[$i]->is_delete = 1 : $big[$i]->sort++;
                    $big[$i]->save();
                    continue;
                }
                break;
            }
            return $afterSort;
        } else {
            if ($beforeSort == $afterSort) {
                return $afterSort;
            }
            $exists = GoodsHotSearch::find()->where($commonWhere)->andWhere(['sort' => $afterSort])->exists();
            if (!$exists) {
                $data = (new CommonHotSearch())->getAll();
                $count = count($data);
                return $afterSort > $count ? $count < 10 ? ++$count : 10 : $afterSort;
            }

            if ($beforeSort > $afterSort) {
                $big = GoodsHotSearch::find()
                    ->where($commonWhere)
                    ->andWhere([
                        'AND',
                        ['>=', 'sort', $afterSort],
                        ['<', 'sort', $beforeSort],
                    ])
                    ->orderBy(['sort' => SORT_ASC])
                    ->all();
                for ($i = 0; $i < count($big); $i++) {
                    if ($i == 0 || $big[$i - 1]->sort == $big[$i]->sort) {
                        $big[$i]->sort++;
                        $big[$i]->save();
                        continue;
                    }
                    break;
                }
            }
            if ($beforeSort < $afterSort) {
                $big = GoodsHotSearch::find()
                    ->where($commonWhere)
                    ->andWhere([
                        'AND',
                        ['<=', 'sort', $afterSort],
                        ['>', 'sort', $beforeSort],
                    ])
                    ->orderBy(['sort' => SORT_DESC])
                    ->all();

                for ($i = 0; $i < count($big); $i++) {
                    if ($i == 0 || $big[$i - 1]->sort == $big[$i]->sort) {
                        $big[$i]->sort--;
                        $big[$i]->save();
                        continue;
                    }
                    break;
                }
            }
            return $afterSort;
        }
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $goods_id = (int)$this->goods_id;
            if ($this->type === GoodsHotSearch::TYPE_GOODS) {
                $model = CommonHotSearch::getHotSearchOne($goods_id, $this->type);
            }
            if ($this->type === GoodsHotSearch::TYPE_HOT_SEARCH) {
                $model = CommonHotSearch::getHotSearchOne($goods_id, $this->type);
            }

            $is_add = false;
            if (empty($model)) {
                $sql = sprintf(
                    'select * from %s where mall_id = %s and is_delete = %s and type = "%s" and goods_id = %s for update',
                    GoodsHotSearch::tableName(),
                    \Yii::$app->mall->id,
                    0,
                    $this->type,
                    $this->goods_id
                );
                $hot = \Yii::$app->db->createCommand($sql)->queryOne();
                if ($hot) {
                    throw new \Exception('请刷新重试');
                }
                $is_add = true;
                $model = new GoodsHotSearch();
                $model->mall_id = \Yii::$app->mall->id;
                $model->is_delete = 0;
                $model->type = $this->type;
            }
            if (intval($model->goods_id) !== intval($this->goods_id)) {
                $sql = sprintf(
                    'select * from %s where mall_id = %s and id = %s and status = 1 and is_delete = 0 for update',
                    Goods::tableName(),
                    \Yii::$app->mall->id,
                    $this->goods_id
                );
                $goods = \Yii::$app->db->createCommand($sql)->queryOne();
                if (empty($goods)) {
                    throw new \Exception('请刷新重试');
                }
            }

            $sort = $this->type === GoodsHotSearch::TYPE_GOODS ?
                0 : $this->formatSort($is_add, $model->sort, (int)$this->sort);
            $model->attributes = $this->attributes;
            $model->sort = $sort;
            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            }
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
