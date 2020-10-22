<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/21
 * Time: 15:58
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\events;


use app\models\Goods;
use app\models\GoodsWarehouse;
use yii\base\Event;

/**
 * Class StockEvent
 * @package app\events
 * @property GoodsWarehouse $goodsWarehouse
 * @property Goods $goods
 */
class StockEvent extends Event
{
    public $goodsWarehouse;
    public $goods;
    public $oldStock;
    public $newStock;
}
