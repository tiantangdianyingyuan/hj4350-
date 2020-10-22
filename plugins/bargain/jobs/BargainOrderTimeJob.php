<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/19
 * Time: 15:52
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\jobs;


use app\forms\common\ecard\CommonEcard;
use app\models\CoreExceptionLog;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\plugins\bargain\forms\common\BargainFailTemplate;
use app\plugins\bargain\forms\common\CommonBargainOrder;
use app\plugins\bargain\models\BargainOrder;
use app\plugins\bargain\models\Code;
use yii\base\BaseObject;
use yii\helpers\Json;
use yii\queue\JobInterface;

/**
 * @property BargainOrder $bargainOrder
 */
class BargainOrderTimeJob extends BaseObject implements JobInterface
{
    public $bargainOrder;

    public function execute($queue)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $mall = Mall::findOne(['id' => $this->bargainOrder->mall_id]);
            \Yii::$app->setMall($mall);
            $this->bargainOrder = CommonBargainOrder::getCommonBargainOrder()->getBargainOrder($this->bargainOrder->id);
            if ($this->bargainOrder->resetTime > 0) {
                \Yii::$app->queue->delay($this->bargainOrder->resetTime)->push(new BargainOrderTimeJob([
                    'bargainOrder' => $this->bargainOrder
                ]));
            }
            if ($this->bargainOrder->status == Code::BARGAIN_FAIL) {
                return false;
            }
            if ($this->bargainOrder->order) {
                if ($this->bargainOrder->status != Code::BARGAIN_SUCCESS) {
                    $this->bargainOrder->status = Code::BARGAIN_SUCCESS;
                } else {
                    return false;
                }
            } else {
                $this->bargainOrder->status = Code::BARGAIN_FAIL;
            }

            $baseModel = new Model();
            if (!$this->bargainOrder->save()) {
                throw new \Exception($baseModel->getErrorMsg($this->bargainOrder));
            }

            if ($this->bargainOrder->status == Code::BARGAIN_FAIL) {
                $bargainGoods = $this->bargainOrder->bargainGoods;
                $bargainGoodsData = Json::decode($this->bargainOrder->bargain_goods_data, true);
                if (!isset($bargainGoodsData['stock_type']) || $bargainGoodsData['stock_type'] == 1) {
                    $bargainGoods->stock += 1;
                    if ($bargainGoods->goodsWarehouse->type === 'ecard') {
                        CommonEcard::getCommon()->refundEcard([
                            'type' => 'occupy',
                            'sign' => 'bargain',
                            'num' => 1,
                            'goods_id' => $bargainGoods->goods_id,
                        ]);
                    }
                }
                $bargainGoods->fail += 1;
                $bargainGoods->underway -= min($bargainGoods->underway, 1);
                if (!$bargainGoods->save()) {
                    throw new \Exception($baseModel->getErrorMsg($bargainGoods));
                }
            }

            $user = User::findOne(['id' => $this->bargainOrder->user_id]);
            $pageUrl = 'plugins/bargain/order-list/order-list';
            $tplMsg = new BargainFailTemplate([
                'page' => $pageUrl,
                'user' => $user,
                'goodsName' => $this->bargainOrder->goodsWarehouse->name,
                'price' => $this->bargainOrder->price . '元',
                'minPrice' => $this->bargainOrder->min_price . '元',
                'remark' => '超出砍价时间'
            ]);
            $tplMsg->send();

            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            \Yii::$app->queue->delay(0)->push(new BargainOrderTimeJob([
                'bargainOrder' => $this->bargainOrder
            ]));
            $form = new CoreExceptionLog();
            $form->mall_id = $this->bargainOrder->mall_id;
            $form->level = 1;
            $form->title = '砍价订单超时取消';
            $form->content = $exception->getMessage();
            $form->save();
        }
    }
}
