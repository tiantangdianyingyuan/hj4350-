<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/17
 * Time: 10:19
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\ecard\forms\mall;


use app\models\Goods;
use app\models\GoodsWarehouse;
use app\plugins\ecard\forms\Model;
use app\models\EcardOrder;
use yii\helpers\Json;

/**
 * Class GoodsForm
 * @package app\plugins\ecard\forms\mall
 * @property GoodsWarehouse $goodsWarehouse
 * @property Goods $goods
 */
class GoodsForm extends Model
{
    public $ecard_id;
    public $goodsWarehouse;
    public $goods;

    public function rules()
    {
        return [
            [['ecard_id'], 'integer'],
            [['goodsWarehouse', 'goods'], 'safe']
        ];
    }

    /**
     * @return array
     * @throws \Exception
     * 获取商品中电子卡密信息
     */
    public function getInfo()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if ($this->goodsWarehouse->type != 'ecard') {
                return [];
            }
            return [
                'ecard_id' => $this->goodsWarehouse->ecard_id,
                'name' => $this->goodsWarehouse->ecard->name,
                'stock' => $this->goodsWarehouse->ecard->stock,
            ];
        } catch (\Exception $exception) {
            \Yii::warning('商品获取卡密失败');
            \Yii::warning($exception);
            throw $exception;
        }
    }
}
