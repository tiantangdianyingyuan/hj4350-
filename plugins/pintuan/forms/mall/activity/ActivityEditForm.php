<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\mall\activity;


use app\core\response\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\plugins\pintuan\jobs\v2\PintuanGoodsJob;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanGoodsGroups;
use app\plugins\pintuan\Plugin;

/**
 * Class GoodsEditForm
 * @package app\plugins\pintuan\forms\mall
 */
class ActivityEditForm extends BaseGoodsEdit
{
    public $is_alone_buy;
    public $end_time;
    public $start_time;
    public $is_sell_well;
    public $is_auto_add_robot;
    public $add_robot_time;
    public $group_list;

    private $isAddGroups = false;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['is_alone_buy', 'is_sell_well', 'start_time',
                'is_auto_add_robot', 'add_robot_time'], 'required'],
            [['is_auto_add_robot', 'add_robot_time'], 'integer'],
            [['group_list'], 'safe'],
            [['end_time'], 'string'],
            [['virtual_sales', 'add_robot_time'], 'number', 'max' => 999999]
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'is_alone_buy' => '是否允许单独购买',
            'end_time' => '活动结束时间',
            'start_time' => '活动开始时间',
            'is_sell_well' => '是否热销',
            'is_auto_add_robot' => '是否自动添加机器人',
            'add_robot_time' => '添加机器人间隔',
            'virtual_sales' => '已团商品数'
        ]);
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->checkData();
            $this->checkGoodsGroupData();
            $this->deleteGoods();
            $this->setGoods();
            $this->setAttr();
            $this->setGoodsService();
            $this->setCard();
            $this->setCoupon();
            $pintuanGoods = $this->pintuanGoods();
            $this->setListener();

            foreach ($this->group_list as $item) {
                $this->isAddGroups = true;
                $goods = Goods::findOne(['id' => $item['goods_id'], 'mall_id' => \Yii::$app->mall->id]);
                $this->id = $goods ? $goods->id : 0;

                $this->shareLevelList = isset($item['shareLevelList']) && $item['shareLevelList'] ? $item['shareLevelList'] : [];
                $this->member_price = isset($item['member_price']) && $item['member_price'] ? $item['member_price'] : [];
                $this->attr = $item['attr'];
                if ($this->use_attr == 0) {
                    $this->price = $item['attr'][0]['price'];
                    $this->goods_num = $item['attr'][0]['stock'];
                }

                $this->setGoods();
                $this->setAttr();
                $this->setGoodsService();
                $this->setCard();
                $this->setCoupon();
                $this->pintuanGoods($pintuanGoods->id);
                $this->setPintuanGroups($item);
                $this->setListener();
            }

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }

    private function deleteGoods() {
        // 先将拼团组全部删除
        if ($this->id) {
            /** @var PintuanGoods $pintuanGoods */
            $pintuanGoods = PintuanGoods::findOne(['goods_id' => $this->id]);
            $list = PintuanGoods::find()->where([
                'pintuan_goods_id' => $pintuanGoods->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ])->all();
            $goodsIds = [];
            /** @var PintuanGoods $item */
            foreach ($list as $item) {
                $goodsIds[] = $item->goods_id;
            }
            Goods::updateAll(['is_delete' => 1], ['id' => $goodsIds, 'is_delete' => 0]);
            PintuanGoods::updateAll(['is_delete' => 1], ['goods_id' => $goodsIds, 'pintuan_goods_id' => $pintuanGoods->id, 'is_delete' => 0]);
            PintuanGoodsGroups::updateAll(['is_delete' => 1], ['goods_id' => $goodsIds, 'is_delete' => 0]);
        }
    }

    protected function setGoodsSign()
    {
        return (new Plugin())->getName();
    }

    /**
     * @param int $pintuanGoodsId
     * @return PintuanGoods|array|null|\yii\db\ActiveRecord
     * @throws \Exception
     */
    private function pintuanGoods($pintuanGoodsId = 0)
    {
        $pintuanGoods = PintuanGoods::find()->where(['mall_id' => \Yii::$app->mall->id, 'goods_id' => $this->goods->id])->one();
        if (!$pintuanGoods) {
            $pintuanGoods = new PintuanGoods();
            $pintuanGoods->mall_id = \Yii::$app->mall->id;
            $pintuanGoods->goods_id = $this->goods->id;
            $pintuanGoods->is_delete = 0;
        }
        $pintuanGoods->start_time = $this->start_time;
        $pintuanGoods->end_time = $this->end_time ? $this->end_time : '0000-00-00 00:00:00';
        $pintuanGoods->is_alone_buy = $this->is_alone_buy;
        $pintuanGoods->is_auto_add_robot = $this->is_auto_add_robot;
        $pintuanGoods->add_robot_time = $this->add_robot_time;
        $pintuanGoods->is_sell_well = $this->is_sell_well;
        $pintuanGoods->pintuan_goods_id = $pintuanGoodsId;
        $pintuanGoods->is_delete = 0;
        $res = $pintuanGoods->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($pintuanGoods));
        }

        // 拼团商品自动下架
        $time = strtotime($this->end_time) - time();
        $queueId = \Yii::$app->queue->delay($time > 0 ? $time : 0)->push(new PintuanGoodsJob([
            'goodsId' => $this->goods->id
        ]));

        return $pintuanGoods;
    }

    private function checkGoodsGroupData()
    {
        $people = [];
        foreach ($this->group_list as $key => &$item) {
            $item['people_num'] = isset($item['people_num']) ? (int)$item['people_num'] : 2;
            $item['preferential_price'] = isset($item['preferential_price']) ? (float)$item['preferential_price'] : 0;
            $item['pintuan_time'] = isset($item['pintuan_time']) ? (int)$item['pintuan_time'] : 1;
            $item['group_num'] = isset($item['group_num']) ? (int)$item['group_num'] : 0;

            $people[] = $item['people_num'];

            if ($item['people_num'] <= 0) {
                throw new \Exception('请填写拼团人数');
            }

            if ($item['people_num'] < 2) {
                throw new \Exception('拼团人数最少2人');
            }
            if ($item['preferential_price'] < 0) {
                throw new \Exception('团长优惠不能小于0');
            }
            if ($item['pintuan_time'] < 1) {
                throw new \Exception('拼团时间不能小于1小时');
            }
            if ($item['group_num'] < 0) {
                throw new \Exception('团长数量不能小于0');
            }

            foreach ($item['attr'] as $aKey => &$aItem) {
                $aItem['price'] = isset($aItem['price']) ? (float)$aItem['price'] : 0;
                if ($aItem['price'] < 0) {
                    throw new \Exception('拼团价不能小于0');
                }

                if ($aItem['stock'] < 0) {
                    throw new \Exception('拼团库存不能小于0');
                }
            }
            unset($aItem);
        }
        unset($item);

        if (count($people) != count(array_unique($people))) {
            throw new \Exception('拼团组拼团人数不能相同');
        }
    }

    // 保存拼团组信息
    private function setPintuanGroups($item)
    {
        $groups = PintuanGoodsGroups::find()->where(['goods_id' => $this->goods->id])->one();
        if (!$groups) {
            $groups = new PintuanGoodsGroups();
            $groups->goods_id = $this->goods->id;
        }
        $groups->people_num = $item['people_num'];
        $groups->group_num = $item['group_num'];
        $groups->preferential_price = $item['preferential_price'];
        $groups->pintuan_time = $item['pintuan_time'];
        $groups->is_delete = 0;
        $res = $groups->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($groups));
        }
    }

    private function checkData() {
        $startTime = strtotime($this->start_time);
        $endTime = strtotime($this->end_time);
        if ($this->end_time && $startTime > $endTime) {
            throw new \Exception('开始时间不能小于结束时间');
        }

        if ($startTime < strtotime('1970-01-05 00:00:00')) {
            throw new \Exception('开始时间不能小于1970-01-05 00:00:00');
        }

        if ($endTime > strtotime('2038-01-01 00:00:00')) {
            throw new \Exception('结束时间不能大于2038-01-01 00:00:00');
        }
    }

    protected function getGoodsData($common) {
        if (!$this->isAddGroups) {
            return parent::getGoodsData($common);
        }

        /** @var Goods $goods */
        $goods = Goods::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id])->one();
        if (!$goods) {
            throw new \Exception('goods商品不存在或以删除');
        }
        $goods->is_delete = 0;

        return $goods;
    }
}
