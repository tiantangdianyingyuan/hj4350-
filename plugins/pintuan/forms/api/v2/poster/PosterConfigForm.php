<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\pintuan\forms\api\v2\poster;


use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\poster\PosterConfigTrait;
use app\models\Model;
use app\plugins\pintuan\models\Goods;
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

            $group = PintuanGoodsGroups::findOne([
                'id' => $ptGoods->pintuan_goods_groups_id
            ]);
            $goods = $group->goods;
            $people_num = $ptGoods->people_num;

            $prices = array_column($goods->attr, 'price');
            $extra_mark = [
                '长按识别小程序码参团',
                '长按识别小程序码参团',
                '长按识别小程序码 即可参团~',
                '长按识别小程序码参团',
                '快来一起拼单吧~',
                '邀请您一起拼单',
            ];
        } else {
            $form = new CommonGoodsDetail();
            $form->user = \Yii::$app->user->identity;
            $form->mall = \Yii::$app->mall;
            $goods = $form->getGoods($this->goods_id);
            if (empty($goods)) {
                throw new \Exception('拼团海报商品异常');
            }

            $goodsList = (new Goods())->getGoodsGroups($goods);
            if (empty($goodsList)) {
                throw new \Exception('拼团组异常');
            }
            /** @var Goods $item */
            $people_num = 0;
            foreach ($goodsList as $item) {
                if (!$people_num || $item->oneGroups->people_num < $people_num) {
                    $people_num = $item->oneGroups->people_num;
                    $prices = array_column($item->attr, 'price');
                }
            }
            $extra_mark = '';
        }

        $picUrl = \yii\helpers\BaseJson::decode($goods->picUrl);
        $pic_list = array_column($picUrl, 'pic_url');
        if (empty($pic_list)) {
            throw new \Exception('图片不能为空');
        }
        while (count($pic_list) < 5) {
            $pic_list = array_merge($pic_list, $pic_list);
        }

        return [
            'goods_name' => $goods->name,
            'min_price' => min($prices),
            'max_price' => max($prices),
            'multi_map' => $pic_list,
            'customize_text' => $this->pintuan_group_id ? '邀请您一起拼单' : '',
            'people_num' => $people_num . '人团',
            'extra_mark' => $extra_mark,
        ];

    }
}