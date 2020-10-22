<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2019 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\api\cat;


use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsList;
use app\models\GoodsCats;
use app\models\Mall;

class GoodsListForm extends CommonGoodsList
{
    private const limit = 10;
    public $mch_id;
    public $sign;
    public $cat_id;
    public $cat_ids;
    public $offset;

    public function rules()
    {
        return [
            [['cat_id', 'mch_id', 'offset'], 'integer'],
            [['cat_id', 'mch_id', 'offset'], 'default', 'value' => 0],
            [['cat_id', 'offset', 'cat_ids'], 'required'],
            [['sign'], 'string'],
            [['cat_ids'], 'trim'],
        ];
    }

    private function goods($cat_id, $limit, $offset = 0)
    {
        $form = new CommonGoodsList();
        $form->cat_id = $cat_id;
        $form->status = 1;
        $form->mch_id = $this->mch_id ?: 0;
        $form->sign = ['mch', ''];
        $form->is_array = false;
        $form->isSignCondition = true;
        $form->is_sales = (new Mall())->getMallSettingOne('is_sales');
        $form->relations = ['goodsWarehouse', 'mallGoods'];
        if (!$form->validate()) {
            throw new \Exception($form->getErrorMsg());
        }
        $form->getQuery();
        $list = $form->query->limit($limit)->offset($offset)
            ->groupBy($form->group_by_name)
            ->asArray($form->is_array)
            ->all();

        $newList = array_map(function ($item) use ($form) {
            return $form->getGoodsData($item);
        }, $list);
        return [$newList, $offset + count($list)];
    }

    /**
     * 三级分类专用
     * @return array
     */
    public function getCatGoodsList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $offset = $this->offset;
            $cat_ids = \yii\helpers\BaseJson::decode($this->cat_ids);
            $cat_id = $this->cat_id;

            if (!is_array($cat_ids) || !in_array($cat_id, $cat_ids)) {
                throw new \Exception('参数错误');
            }

            for ($i = 0; $i < array_search($cat_id, $cat_ids); $i++) {
                next($cat_ids);
            }

            $new_list = [];
            $last_limit = self::limit;
            while ($last_limit > 0 && $id = current($cat_ids)) {
                [$list, $offset] = $this->goods($id, $last_limit, $offset);

                $cat_model = GoodsCats::findOne($id);
                $cat_array = \yii\helpers\ArrayHelper::toArray($cat_model);
                $cat_array['goods_list'] = $list;
                $cat_array['offset'] = $offset;
                array_push($new_list, $cat_array);

                $last_limit -= count($list);
                /**同查10条***/
                next($cat_ids);
                $offset = 0;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $new_list,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}