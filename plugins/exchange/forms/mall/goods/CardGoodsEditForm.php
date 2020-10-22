<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\exchange\forms\mall\goods;

use app\core\response\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\models\GoodsWarehouse;
use app\plugins\exchange\models\ExchangeGoods;
use app\plugins\exchange\Plugin;

/**
 * @property ExchangeGoods $cardGoods;
 */
class CardGoodsEditForm extends BaseGoodsEdit
{
    // 商品库商品字段
    public $name;
    public $subtitle;
    public $original_price;
    public $cost_price;
    public $detail;
    public $video_url;
    public $unit;
    public $pic_url;
    public $type;

    public $library_id;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name', 'cost_price', 'detail'
                , 'unit', 'library_id'], 'required'],
            [['video_url', 'type', 'subtitle'], 'trim'],
            [['video_url', 'type', 'subtitle'], 'string'],
            ['type', 'default', 'value' => 'goods'],
            [['type'], 'in', 'range' => ['goods', 'ecard', 'exchange']], // 商品类型
            [['original_price', 'cost_price', 'library_id'], 'number', 'min' => 0],
            [['pic_url'], 'safe'],
            [['original_price', 'library_id'], 'default', 'value' => 0],
            [['cost_price', 'original_price'], 'number', 'max' => 9999999]
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name' => '商品名称',
            'subtitle' => '副标题',
            'original_price' => '商品原价',
            'cost_price' => '商品成本价',
            'detail' => '商品详情',
            'cover_pic' => '商品缩略图',
            'video_url' => '商品视频',
            'unit' => '商品单位',
            'library_id' => 'library_id',
        ]);
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (count($this->pic_url) <= 0) {
                throw new \Exception('请上传商品轮播图');
            }

            //上下架
            //(new Plugin())->breakGoodsStatus($this->id, $this->status);

            $this->type = GoodsWarehouse::TYPE_VIRTUAL;
            $this->use_attr = 0;
            //区域限制取消
            $this->is_area_limit = 0;
            $this->area_limit = [['list' => []]];

            $this->attr_setting_type = 0;

            $this->attrValidator();
            $this->attrGroupNameValidator();
            if (!$this->id) {
                $this->add();
            } else {
                $this->update();
            }
            $this->setAttr();
            $this->setCard();
            $this->setCoupon();
            $this->setListener();
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ]
            ];
        }
    }

    protected function setGoodsSign()
    {
        return 'exchange';
    }

    /**
     * @throws \Exception
     * 新增商品库商品
     */
    private function add()
    {
        $goodsWarehouse = $this->editGoodsWarehouse();
        $this->goods_warehouse_id = $goodsWarehouse->id;
        $this->setGoods();
        $this->editCardGoods();
    }

    /**
     * @throws \Exception
     * 修改商品库商品
     */
    private function update()
    {
        $this->setGoods();
        if (!$this->goods->goodsWarehouse) {
            throw new \Exception('商品库错误：查找不到id为' . $this->goods->goods_warehouse_id . '的商品');
        }
        $this->editGoodsWarehouse($this->goods->goodsWarehouse);

        $cardGoods = ExchangeGoods::findOne([
            'goods_id' => $this->goods->id,
            'mall_id' => \Yii::$app->mall->id
        ]);
        if (!$cardGoods) {
            throw new \Exception('mall_goods商品不存在或者已删除');
        }
        $this->editCardGoods($cardGoods);
    }

    /**
     * @param null $goodsWarehouse
     * @return GoodsWarehouse|null
     * @throws \Exception 编辑商品库
     */
    private function editGoodsWarehouse($goodsWarehouse = null)
    {
        if (!$goodsWarehouse) {
            $goodsWarehouse = new GoodsWarehouse();
            $goodsWarehouse->mall_id = \Yii::$app->mall->id;
            $goodsWarehouse->is_delete = 0;
            $goodsWarehouse->type = $this->type;
            $goodsWarehouse->ecard_id = 0;
        }
        $goodsWarehouse->name = $this->name;
        $goodsWarehouse->subtitle = $this->subtitle;
        $goodsWarehouse->original_price = $this->original_price;
        $goodsWarehouse->cost_price = $this->cost_price;
        $goodsWarehouse->detail = $this->detail;
        $goodsWarehouse->cover_pic = $this->pic_url[0]['pic_url'];
        $goodsWarehouse->pic_url = \Yii::$app->serializer->encode($this->pic_url);
        $goodsWarehouse->video_url = $this->video_url;
        $goodsWarehouse->unit = $this->unit;
        if (!$goodsWarehouse->save()) {
            throw new \Exception('商品保存失败：' . $this->getErrorMsg($goodsWarehouse));
        }
        $this->goodsWarehouse = $goodsWarehouse;
        return $goodsWarehouse;
    }

    /**
     * @param null $cardGoods
     * @return ExchangeGoods|null
     * @throws \Exception 编辑商城商品
     */
    private function editCardGoods($cardGoods = null)
    {
        if (!$cardGoods) {
            $cardGoods = new ExchangeGoods();
            $cardGoods->mall_id = \Yii::$app->mall->id;
            $cardGoods->goods_id = $this->goods->id;
        }
        $cardGoods->library_id = $this->library_id;
        if (!$cardGoods->save()) {
            throw new \Exception('商品保存失败：' . $this->getErrorMsg($cardGoods));
        }
        return $cardGoods;
    }
}
