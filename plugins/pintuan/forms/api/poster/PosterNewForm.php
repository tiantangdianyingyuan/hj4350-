<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\pintuan\forms\api\poster;

use app\core\response\ApiCode;
use app\forms\api\poster\BasePoster;
use app\forms\api\poster\common\StyleGrafika;
use app\models\Model;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanOrders;

class PosterNewForm extends Model implements BasePoster
{
    public $style;
    public $typesetting;
    public $type;
    public $goods_id;
    public $color;
    public $pintuan_group_id;

    public function rules()
    {
        return [
            [['style', 'typesetting', 'goods_id'], 'required'],
            [['style', 'typesetting', 'type', 'pintuan_group_id'], 'integer'],
            [['color'], 'string'],
        ];
    }

    public function poster()
    {
        try {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $this->get()
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine(),
            ];
        }
    }

    public function get()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $class = $this->getClass($this->style);

        if ($this->pintuan_group_id) {
            $model = PintuanOrders::findOne([
                'id' => $this->pintuan_group_id
            ]);
        }
        if ($this->goods_id) {
            $model = PintuanGoods::findOne([
                'goods_id' => $this->goods_id,
            ]);
        }
        if (empty($model->goods)) {
            throw new \Exception('拼团海报商品不存在');
        }

        $class->typesetting = $this->typesetting;
        $class->type = $this->type;
        $class->color = $this->color;
        $class->goods = $model->goods;

        $this->pintuan_group_id && $class->defaultText = [
            'mark_one_text' => '长按识别小程序码参团',
            'mark_two_text' => '长按识别小程序码参团',
            'mark_three_text' => '长按识别小程序码 即可参团~',
            'mark_four_text' => '长按识别小程序码参团',
            'head_three_text' => '快来一起拼单吧~',
            'end_two_remark' => '邀请您一起拼单',
            'explanation_one' => '邀请您一起拼单',
            'explanation_four' => '邀请您一起拼单',
        ];
        $class->other = [
            $this->pintuan_group_id,
            $this->goods_id,
        ];
        $class->extraModel = 'app\plugins\pintuan\forms\api\poster\PosterCustomize';
        return $class->build();
    }


    /**
     * @param int $key
     * @return StyleGrafika
     * @throws \Exception
     */
    private function getClass(int $key): StyleGrafika
    {
        $map = [
            1 => 'app\forms\api\poster\style\StyleOne',
            2 => 'app\forms\api\poster\style\StyleTwo',
            3 => 'app\forms\api\poster\style\StyleThree',
            4 => 'app\forms\api\poster\style\StyleFour',
        ];
        if (isset($map[$key]) && class_exists($map[$key])) {
            return new $map[$key];
        }
        throw new \Exception('调用错误');
    }
}