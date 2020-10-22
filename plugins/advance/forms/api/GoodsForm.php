<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\advance\forms\api;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\goods\CommonGoodsList;
use app\forms\common\goods\CommonGoodsMember;
use app\forms\common\goods\CommonGoodsVipCard;
use app\forms\common\template\TemplateList;
use app\models\GoodsMemberPrice;
use app\models\MallMembers;
use app\models\Model;
use app\models\User;
use app\plugins\advance\forms\common\SettingForm;
use app\plugins\advance\models\AdvanceGoods;
use app\plugins\advance\models\AdvanceGoodsAttr;
use app\plugins\advance\models\AdvanceOrder;
use app\plugins\advance\models\Goods;
use app\plugins\advance\Plugin;
use yii\helpers\ArrayHelper;

class GoodsForm extends Model
{
    public $id;
    public $page;
    public $goods_id;
    public $keyword;

    public function rules()
    {
        return [
            [['page', 'goods_id', 'id'], 'integer'],
            [['keyword'], 'string'],
            [['page'], 'default', "value" => 1]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $form = new CommonGoodsList();
        $form->model = 'app\plugins\advance\models\Goods';
        if ($this->goods_id) {
            $advance_goods = Goods::find()->with('cat')->where(['id' => $this->goods_id])->one();
            if (empty($advance_goods)) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '参数错误',
                ];
            }
            $form->cat_id = $advance_goods->cat->cat_id;
        }

        $form->page = $this->page;
        $form->keyword = $this->keyword;
        $form->sign = 'advance';
        $form->sort = 1;
        $form->relations = ['goodsWarehouse.cats', 'attr', 'advanceGoods'];
        $form->is_array = 1;
        $form->status = 1;
        $form->getQuery();
        $query = $form->query->select('g.*,ag.start_prepayment_at,ag.end_prepayment_at')
            ->leftJoin(['ag' => AdvanceGoods::tableName()], 'g.id = ag.goods_id')
            ->andWhere(['<=', 'ag.start_prepayment_at', date('Y-m-d H:i:s', time())])
            ->andWhere(['>=', 'ag.end_prepayment_at', date('Y-m-d H:i:s', time())]);
        if ($this->goods_id) {
            $query = $query->andWhere(['<>', 'ag.goods_id', $this->goods_id]);
        }
        $list = $query->page($form->pagination, $form->limit, $form->page)
            ->groupBy($form->group_by_name)
            ->asArray($form->is_array)
            ->all();

        $setting = (new SettingForm())->search();
        $newList = [];
        foreach ($list as $index => &$item) {
            $attrList = AdvanceGoodsAttr::find()->where([
                'goods_id' => $item['id'],
                'is_delete' => 0,
            ])->asArray()->all();

            $goodsStock = 0;
            $newAttrList = [];
            foreach ($attrList as $aLItem) {
                $newAttrList[$aLItem['goods_attr_id']] = [
                    'deposit' => floatval($aLItem['deposit']),
                    'swell_deposit' => floatval($aLItem['swell_deposit']),
                ];
            }
            $minDeposit = price_format($item['advanceGoods']['deposit'], 'float');
            $swellDeposit = price_format($item['advanceGoods']['swell_deposit'], 'float');
            foreach ($item['attr'] as &$aItem) {
                $aLItem = $newAttrList[$aItem['id']];
                $aItem['deposit'] = floatval($aLItem['deposit']);
                $aItem['swell_deposit'] = floatval($aLItem['swell_deposit']);
                //取最小定金
                if ($minDeposit == 0) {
                    $minDeposit = floatval($aLItem['deposit']);
                    $swellDeposit = floatval($aLItem['swell_deposit']);
                }
                if ($minDeposit > $aLItem['deposit']) {
                    $minDeposit = floatval($aLItem['deposit']);
                    $swellDeposit = floatval($aLItem['swell_deposit']);
                }
                $goodsStock += $aItem['stock'];
            }
            unset($aItem);

            //预售销量
            $count = AdvanceOrder::find()
                ->andWhere([
                    'mall_id' => \Yii::$app->mall->id, 'goods_id' => $item['id'], 'is_delete' => 0, 'is_cancel' => 0,
                    'is_refund' => 0, 'is_pay' => 1, 'is_recycle' => 0
                ])
                ->sum('goods_num');
            $newList[] = [
                'id' => $item['id'],
                'goods_warehouse_id' => $item['goods_warehouse_id'],
                'mall_id' => $item['mall_id'],
                'mch_id' => $item['mch_id'],
                'advanceGoods' => [
                    'deposit' => $minDeposit,
                    'swell_deposit' => $swellDeposit,
                    'end_prepayment_at' => $item['advanceGoods']['end_prepayment_at'],
                ],
                'deposit' => $minDeposit,
                'swell_deposit' => $swellDeposit,
                'goods_stock' => $goodsStock,
                'cover_pic' => $item['goodsWarehouse']['cover_pic'],
                'goodsWarehouse' => [
                    'cover_pic' => $item['goodsWarehouse']['cover_pic'],
                    'video_url' => $item['goodsWarehouse']['video_url'],
                    'original_price' => price_format($item['goodsWarehouse']['original_price'], 'float'),
                ],
                'name' => $item['goodsWarehouse']['name'],
                'original_price' => price_format($item['goodsWarehouse']['original_price'], 'float'),
                'page_url' => (new Plugin())->getGoodsUrl($item),
                'price_content' => $item['price'],
                'price' => price_format($item['price'], 'float'),
                'is_level' => $setting['is_member_price'] ? $item['is_level'] : 0,
                'level_price' => CommonGoodsMember::getCommon()->getGoodsMemberPrice((object)$item),
                'vip_card_appoint' => CommonGoodsVipCard::getInstance()->setGoods($item)->getAppoint(),
                'sales' => '已售' . ($item['virtual_sales'] + $count) . $item['goodsWarehouse']['unit'],
                'sign' => $item['sign'],
                'virtual_sales' => $item['virtual_sales'],
                'use_attr' => intval($item['use_attr']),
                'attr' => $item['attr'],
                'video_url' => $item['goodsWarehouse']['video_url'],
                'subtitle' => $item['goodsWarehouse']['subtitle']
            ];
        }


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $form->pagination,
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
            $goods = $form->getAll();
            //预售销量
            $count = AdvanceOrder::find()
                ->andWhere(['mall_id' => \Yii::$app->mall->id, 'goods_id' => $form->goods->id,
                    'is_delete' => 0, 'is_cancel' => 0, 'is_refund' => 0, 'is_pay' => 1, 'is_recycle' => 0])
                ->sum('goods_num');
            $goods['sales'] = $form->goods->virtual_sales + $count;


            $attrList = AdvanceGoodsAttr::find()->where([
                'goods_id' => $goods['id'],
                'is_delete' => 0,
            ])->asArray()->all();
            foreach ($goods['attr'] as &$aItem) {
                foreach ($attrList as $alItem) {
                    if ($aItem['id'] == $alItem['goods_attr_id']) {
                        $aItem['deposit'] = floatval($alItem['deposit']);
                        $aItem['swell_deposit'] = floatval($alItem['swell_deposit']);
                    }
                }
            }

            $advanceGoods = AdvanceGoods::findOne(['goods_id' => $goods['id']]);
            if (strtotime($advanceGoods->end_prepayment_at) < time()) {
                throw new \Exception('该预售商品已过预售时间');
            }
            $goods = ArrayHelper::toArray($goods);
            $advanceGoods->ladder_rules = json_decode($advanceGoods->ladder_rules, true);
            $goods['advanceGoods'] = $advanceGoods;

            $setting = (new SettingForm())->search();
            $goods['goods_marketing']['limit'] = $setting['is_territorial_limitation']
                ? $goods['goods_marketing']['limit'] : '';
            $groupMinMemberPrice = 0;
            $groupMaxMemberPrice = 0;

            foreach ($goods['attr'] as &$aItem) {
                $aItem['extra'] = [
                    [
                        'value' => floatval($aItem['deposit']),
                        'name' => '定金'
                    ],
                    [
                        'value' => floatval($aItem['swell_deposit']),
                        'name' => '膨胀金'
                    ]
                ];
                if (!$groupMinMemberPrice) {
                    $groupMinMemberPrice = $aItem['price_member'];
                    $groupMaxMemberPrice = $aItem['price_member'];
                }
                $groupMinMemberPrice = min($aItem['price_member'], $groupMinMemberPrice);
                $groupMaxMemberPrice = max($aItem['price_member'], $groupMaxMemberPrice);
            }
            unset($aItem);

            $goods['group_min_member_price'] = $groupMinMemberPrice;
            $goods['group_max_member_price'] = $groupMaxMemberPrice;

            // 判断插件分销是否开启
            if (!$setting['is_share']) {
                $goods['share'] = 0;
            }

            try {
                $goods['template_message'] = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, [
                    'pay_advance_balance',
                ]);
            } catch (\Exception $exception) {
                $goods['template_message'] = [];
            }
            $goods['level_show'] = $setting['is_member_price'] ? $goods['level_show'] : 0;

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $goods
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
