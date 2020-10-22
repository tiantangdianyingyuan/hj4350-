<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\pick\forms\api;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsDetail;
use app\models\Model;
use app\models\User;
use app\plugins\pick\forms\common\CommonForm;
use app\plugins\pick\forms\common\CommonSetting;
use app\plugins\pick\models\PickActivity;
use app\plugins\pick\models\PickGoods;

class GoodsForm extends Model
{
    public $id;
    public $page;
    public $keyword;

    public function rules()
    {
        return [
            [['page', 'id'], 'integer'],
            [['keyword'], 'string'],
            [['page'], 'default', "value" => 1]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $list = CommonForm::getList($this->keyword);

        $setting = (new CommonSetting())->search();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list['list'] ?? [],
                'activity' => $list['activity'] ?? (object)[],
                'bg_url' => $setting['bg_url'] ?? '',
                'form' => $setting['form'] ?? (object)[],
                'rule' => $setting['rule'] ?? '',
                'title' => $setting['title'] ?? '',
                'pagination' => $list['pagination'] ?? (object)[],
            ]
        ];
    }

    public function detail()
    {
        try {
            $form = new CommonGoodsDetail();
            $form->mall = \Yii::$app->mall;
            $form->user = User::findOne(\Yii::$app->user->id);
            $goods = $form->getGoods($this->id);
            if (!$goods) {
                throw new \Exception('商品不存在');
            }
            if ($goods->status != 1) {
                throw new \Exception('商品未上架');
            }
            $form->goods = $goods;
            $setting = (new CommonSetting())->search();
            $form->setShare($setting['is_share']);
            $goods = $form->getAll();
            $goods['sales'] = $form->goods->sales + $form->goods->virtual_sales;

//            $groupMinMemberPrice = 0;
//            $groupMaxMemberPrice = 0;
//
//            foreach ($goods['attr'] as &$aItem) {
//                if (!$groupMinMemberPrice) {
//                    $groupMinMemberPrice = $aItem['price_member'];
//                    $groupMaxMemberPrice = $aItem['price_member'];
//                }
//                $groupMinMemberPrice = min($aItem['price_member'], $groupMinMemberPrice);
//                $groupMaxMemberPrice = max($aItem['price_member'], $groupMaxMemberPrice);
//            }
//            unset($aItem);
//
//            $goods['group_min_member_price'] = $groupMinMemberPrice;
//            $goods['group_max_member_price'] = $groupMaxMemberPrice;

            $goods['level_show'] = 0;
            //当前商品活动状态
            $activity_status = 1;
            $activity = PickActivity::find()->alias('a')
                ->leftJoin(['g' => PickGoods::tableName()], 'g.pick_activity_id = a.id')
                ->andWhere(['a.is_delete' => 0, 'g.goods_id' => $this->id])
                ->one();
            if (strtotime($activity->start_at) > time() && strtotime($activity->end_at) < time()) {
                $activity_status = 0;
            }
            if ($activity->status == 0) {
                $activity_status = 0;
            }

            $commend = CommonForm::getList('', $this->id);
            if (empty($commend['activity'])) {
                throw new \Exception('活动已下架');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $goods,
                    'list' => $commend['list'] ?? [],
                    'activity' => $commend['activity'] ?? [],
                    'activity_status' => $activity_status
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
