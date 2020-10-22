<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/13
 * Time: 18:22
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\jobs;


use app\models\Mall;
use app\plugins\bargain\forms\common\goods\CommonBargainGoods;
use app\plugins\bargain\models\BargainGoods;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * @property BargainGoods $bargainGoods
 */
class BargainGoodsTimeJob extends BaseObject implements JobInterface
{
    public $bargainGoods;

    public function execute($queue)
    {
        \Yii::$app->setMall(Mall::findOne($this->bargainGoods->mall_id));
        $this->bargainGoods = CommonBargainGoods::getCommonGoods()->getGoods($this->bargainGoods->goods_id);
        \Yii::error($this->bargainGoods);
        if ($this->bargainGoods->end_time > date('Y-m-d H:i:s')) {
            return false;
        }
        $this->bargainGoods->goods->status = 0;
        $this->bargainGoods->goods->save();
    }
}
