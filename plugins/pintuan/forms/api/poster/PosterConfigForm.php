<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\pintuan\forms\api\poster;


use app\core\response\ApiCode;
use app\forms\common\poster\PosterConfigTrait;
use app\models\Model;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanGoodsGroups;
use app\plugins\pintuan\models\PintuanOrders;
use app\plugins\pintuan\Plugin;

class PosterConfigForm extends Model
{
    use PosterConfigTrait;

    public $goods_id;
    public $pintuan_group_id;

    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['goods_id', 'pintuan_group_id'], 'integer'],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'config' => $this->getConfig(),
                    'info' => $this->getAll(),
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function getPlugin(): array
    {
        return [
            'sign' => (new Plugin())->getName(),
        ];
    }

    public function getExtra(): array
    {
        $model = new PosterCustomize();
        $data = $model->traitMultiMapContent();

        $extra_multiMap = $this->formatType($data);
        return [
            'extra_multiMap' => $extra_multiMap,
        ];
    }

    public function getGoods(): array
    {
        if ($this->pintuan_group_id) {
            $ptGoods = PintuanOrders::findOne([
                'id' => $this->pintuan_group_id
            ]);

            $groups = PintuanGoodsGroups::findOne([
                'id' => $ptGoods->pintuan_goods_groups_id
            ]);
            $people_num = $ptGoods->people_num;
            $prices = array_column($groups->attr, 'pintuan_price');

            $extra_mark = [
                '长按识别小程序码参团',
                '长按识别小程序码参团',
                '长按识别小程序码 即可参团~',
                '长按识别小程序码参团',
                '快来一起拼单吧~',
                '邀请您一起拼单',
            ];
        } else {
            $ptGoods = PintuanGoods::find()->where([
                'goods_id' => $this->goods_id,
            ])->with(['goods.goodsWarehouse', 'goods.attr', 'ptGoodsAttr'])->one();
            if (empty($ptGoods)) {
                throw new \Exception('拼团海报商品不存在');
            }

            $people_num = 0;
            foreach ($ptGoods->groups as $i) {
                if (!$people_num || $i->people_num < $people_num) {
                    $people_num = $i->people_num;
                    $prices = array_column($i->attr, 'pintuan_price');
                }
            }
            $extra_mark = '';
        }

        $picUrl = \yii\helpers\BaseJson::decode($ptGoods->goods->picUrl);
        $pic_list = array_column($picUrl, 'pic_url');
        if (empty($pic_list)) {
            throw new \Exception('图片不能为空');
        }
        while (count($pic_list) < 5) {
            $pic_list = array_merge($pic_list, $pic_list);
        }

        return [
            'goods_name' => $ptGoods->goods->name,
            'min_price' => min($prices),
            'max_price' => max($prices),
            'multi_map' => $pic_list,
            'customize_text' => $this->pintuan_group_id ? '邀请您一起拼单' : '',
            'people_num' => $people_num . '人团',
            'extra_mark' => $extra_mark,
        ];

    }
}