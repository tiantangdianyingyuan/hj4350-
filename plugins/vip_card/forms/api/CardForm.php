<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/10/15
 * Time: 10:04
 */


namespace app\plugins\vip_card\forms\api;

use app\models\Model;
use app\plugins\vip_card\models\VipCard;
use app\models\Goods;
use app\models\GoodsCats;

class CardForm extends Model
{
    public function getCard()
    {
        $query = VipCard::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);

        $list = $query->orderBy(['created_at' => SORT_DESC])
            ->with(['detail' => function ($query) {
                $query->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC])->where(['status' => 0,'is_delete' => 0])
                ->andWhere(['!=', 'num', 0]);
            }])
            ->with('detail.vipCards')
            ->with('detail.vipCoupons')
            ->asArray()
            ->one();

        if ($list) {
            foreach ($list['detail'] as &$v) {
                foreach ($v['cards'] as $k => &$item) {
                    if (isset($v['vipCards'][$k]['name'])) {
                        $item['name'] = $v['vipCards'][$k]['name'];
                    } else {
                        unset($v['cards'][$k]);
                    }
                }
                unset($item);

                foreach ($v['coupons'] as $k => &$item) {
                    if (isset($v['vipCoupons'][$k]['name'])) {
                        $item['name'] = $v['vipCoupons'][$k]['name'];
                    } else {
                        unset($v['coupons'][$k]);
                    }
                }
                unset($item);
            }
            unset($v);

            $types = json_decode($list['type_info'],true);
            $list['type_info'] = $types;
            $list['type_info_detail']['goods'] = [];
            foreach ($types['goods'] as $item) {
                try {
                    $goods = Goods::find()
                        ->where(['id' => $item])
                        ->one();
                    if ($goods) {
                        $temp['name'] = $goods->name;
                        $temp['id'] = $goods->id;
                        $list['type_info_detail']['goods'][] = $temp;
                    }
                } catch (\Exception $e) {
                    \Yii::error('会员卡指定商品不存在');
                    \Yii::error($e);
                }
            }
            unset($temp);

            $list['type_info_detail']['cats'] = [];
            foreach ($types['cats'] as $item) {
                try {
                    $cats = GoodsCats::findOne([
                        'mall_id' => \Yii::$app->mall->id,
                        'is_delete' => 0,
                        'id' => $item,
                        'mch_id' => 0,
                        'status' => 1
                    ]);
                    if ($cats) {
                        $temp['value'] = $cats->id;
                        $temp['label'] = $cats->name;
                        $list['type_info_detail']['cats'][] = $temp;
                    }
                } catch (\Exception $e) {
                    \Yii::error('会员卡指定分类不存在');
                    \Yii::error($e);
                }
            }
            unset($temp);

            $list['type_info_detail']['all'] = $types['all'];
        }

        return [
            'code' => 0,
            'msg' => '数据请求成功',
            'data' => $list
        ];
    }
}