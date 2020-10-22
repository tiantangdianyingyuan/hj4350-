<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\pick\forms\api;

use app\core\response\ApiCode;
use app\models\GoodsAttr;
use app\models\Model;
use app\plugins\pick\models\PickCart;

abstract class CartEditBase extends Model
{
    public $list;

    public function rules()
    {
        return [
            [['list'], 'trim'],
        ];
    }

    protected function secondArrayUniqueBykey($arr, $key, &$tmp)
    {
        $tmp_arr = [];
        foreach ($arr as $k => $item) {
            if (in_array($item[$key], $tmp_arr)) {
                $re = array_search($item[$key], $tmp_arr);
                unset($tmp_arr[$re]);
            }
            $tmp_arr[$k] = $item[$key];
        }

        foreach ($tmp_arr as $k => $item) {
            if (array_key_exists($k, $arr)) {
                $tmp[] = $arr[$k];
            }
        }
    }


    //批量更新
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            while (PickCart::cacheStatusGet()) {

            }
            PickCart::cacheStatusSet(true);

            $info = json_decode($this->list, true);
            if (!$info) {
                throw new \Exception('数据为空');
            }

            $user_id = \Yii::$app->user->identity->id;

            //去重
            $this->secondArrayUniqueBykey($info, 'attr', $list);

            $array = [];
            $t = \Yii::$app->db->beginTransaction();
            foreach ($list as $item) {
                $goodsAttr = $this->getGoodsAttr($item);
                if (!$goodsAttr) {
                    continue;
                }
                $form = PickCart::findOne([
                    'goods_id' => $item['goods_id'],
                    'user_id' => $user_id,
                    'attr_id' => $item['attr'],
                    'is_delete' => 0
                ]);

                if (isset($form) && $item['num'] > 0) {
                    $form->num = $item['num'];
                    $form->save();
                    continue;
                } elseif (isset($form) && $item['num'] == 0) {
                    $form->is_delete = 1;
                    $form->save();
                    continue;
                }

                if ($item['num'] > 0) {
                    $array[] = [
                        \Yii::$app->mall->id,
                        $user_id,
                        $item['attr'],
                        $item['goods_id'],
                        $item['num'],
                        0,
                        date("Y-m-d H:i:s"),
                        '0000-00-00 00:00:00',
                        '0000-00-00 00:00:00',
                    ];
                }
            }

            if (isset($array)) {
                \Yii::$app->db->createCommand()
                    ->batchInsert(
                        PickCart::tableName(),
                        [
                            'mall_id',
                            'user_id',
                            'attr_id',
                            'goods_id',
                            'num',
                            'is_delete',
                            'created_at',
                            'updated_at',
                            'deleted_at'
                        ],
                        $array
                    )
                    ->execute();
            }
            $t->commit();
            PickCart::cacheStatusSet(false);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success'
            ];
        } catch (\Exception $e) {
            PickCart::cacheStatusSet(false);
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }


    }

    protected function getGoodsAttr($item)
    {
        return GoodsAttr::find()->alias('c')->where([
            'c.goods_id' => $item['goods_id'],
            'c.id' => $item['attr'],
            'c.is_delete' => 0,
        ])
            ->innerJoinWith('goods')
            ->one();
    }
}
