<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\api;


use app\core\response\ApiCode;
use app\forms\common\goods\GoodsBase;
use app\forms\common\mch\MchSettingForm;
use app\plugins\mch\models\Goods;
use app\plugins\mch\models\MchGoods;

class GoodsEditForm extends GoodsBase
{
    public $mch_id;
    public $form;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['mch_id'], 'integer'],
            [['form'], 'safe'],
        ]);
    }

    public function findGoods()
    {
        $goods = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
            'id' => $this->id,
            'is_delete' => 0,
        ])->with('mchGoods')->one();

        $this->goods = $goods;
        return $goods;
    }

    public function switchStatus()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var Goods $goods */
            $goods = $this->findGoods();
            if (!$goods || !$goods->mchGoods) {
                throw new \Exception('商品不存在');
            }

            $form = new MchSettingForm();
            $setting = $form->search();
            $status = $goods->status == 1 ? 0 : 1;
            if ($status == 1 && $setting['is_goods_audit'] == 1) {
                throw new \Exception('商户开启了商品上架审核，无法直接上架');
            }

            // 无需后台审核 即可上架
            $goods->status = $status;
            $res = $goods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($goods));
            }
            // 0.申请上架|1.申请中|2.同意上架|3.拒绝上架
            $goods->mchGoods->status = $status == 1 ? 2 : 0;
            $goods->mchGoods->remark = $status == 1 ? '无审核，直接上架' : '';
            $res = $goods->mchGoods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($goods->mchGoods));
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $status == 1 ? '上架成功' : '下架成功'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $form = new \app\forms\mall\goods\GoodsEditForm();
            $data = json_decode($this->form, true);
            $form->attributes = $data;

            $group = $data['attr'];
            $group = array_column($group, 'attr_list');

            $result = array_reduce($group, function ($result, $value) {
                return array_merge($result, array_values($value));
            }, array());

            $res = array_values(array_unique($result, SORT_REGULAR));

            $attrs = array_column($res, null, 'attr_group_id');
            foreach ($attrs as $k => $v) {
                unset($attrs[$k]['attr_id']);
                unset($attrs[$k]['attr_name']);
                foreach ($res as $value) {
                    if ($k == $value['attr_group_id']) {
                        $temp['attr_id'] = $value['attr_id'];
                        $temp['attr_name'] = $value['attr_name'];
                        $attrs[$k]['attr_list'][] = $temp;
                    }
                }
            }

            if (!$data['mch_id']) {
                throw new \Exception('商户ID不能为空');
            }

            $form->attrGroups = array_values($attrs);
            $form->use_attr = $data['use_attr'];
            $form->mch_id = $data['mch_id'];
            if ($data['use_attr']) {
                $form->price = $form->attr[0]['price'];
            }

            $res = $form->save();
            return $res;

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
