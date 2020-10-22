<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/23
 * Time: 11:17
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api\poster;


use app\forms\api\poster\BasePoster;
use app\forms\api\poster\common\StyleGrafika;
use app\plugins\community\forms\Model;
use app\plugins\community\models\CommunityGoods;

class PosterNewForm extends Model implements BasePoster
{
    public $style;
    public $typesetting;
    public $type;
    public $goods_id;
    public $color;

    public function rules()
    {
        return [
            [['style', 'typesetting', 'goods_id'], 'required'],
            [['style', 'typesetting', 'type'], 'integer'],
            [['color'], 'string'],
        ];
    }

    public function poster()
    {
        try {
            return $this->success($this->get());
        } catch (\Exception $e) {
            return $this->fail(['msg' => $e->getMessage()]);
        }
    }

    public function get()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        /* @var StyleGrafika $class*/
        $class = $this->getClass($this->style);

        $communityGoods = CommunityGoods::find()->with('goods')
            ->where(['goods_id' => $this->goods_id])
            ->one();

        if (empty($communityGoods)) {
            throw new \Exception('海报-商品不存在');
        }

        $class->typesetting = $this->typesetting;
        $class->type = $this->type;
        $class->color = $this->color;
        $class->goods = $communityGoods->goods;

        $class->extraModel = 'app\plugins\community\forms\api\poster\PosterCustomize';
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
            return new $map[$key]();
        }
        throw new \Exception('调用错误');
    }
}
