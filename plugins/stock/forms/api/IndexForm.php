<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/9
 * Time: 10:47
 */

namespace app\plugins\stock\forms\api;

use app\core\response\ApiCode;
use app\forms\api\share\ShareForm;
use app\models\Model;
use app\models\OrderDetail;
use app\models\Share;
use app\models\ShareOrder;
use app\models\UserIdentity;
use app\plugins\stock\models\StockSetting;
use Yii;

class IndexForm extends Model
{
    const ALL_MEMBERS = 1; //下线总人数
    const ALL_SHARE_ORDERS = 4; //分销订单总数
    const TOTAL_BONUS = 5; //分销订单总金额
    const ALL_BONUS = 2; //累计佣金总额
    const CASHED_BONUS = 3; //已提现佣金总额

    protected $becomeType;
    protected $condition;

    public function search()
    {
        try {
            $identity = UserIdentity::findOne([
                'is_delete' => 0,
                'is_distributor' => 1,
                'user_id' => \Yii::$app->user->id,
            ]);
            if (!$identity) {
                throw new \Exception('你不是分销商');
            }

            $this->becomeType = StockSetting::get(\Yii::$app->mall->id, 'become_type', 0);
            $this->condition = StockSetting::get(\Yii::$app->mall->id, 'condition', 0);
            if (empty($this->becomeType)) {
                throw new \Exception('股东分红未配置');
            }

            $info = $this->becomeType();
            $info['condition'] = $this->condition;
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $info
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    private function becomeType()
    {
        $form = new ShareForm();
        $price = $form->getPrice();

        $share = Share::findOne(['mall_id' => \Yii::$app->mall->id, 'user_id' => \Yii::$app->user->id]);

        switch ($this->becomeType) {
            case self::ALL_BONUS:
                $info['total_money'] = $price['total_money'];
                $info['pass'] = $info['total_money'] >= price_format($this->condition) ? true : false;
                return $info;

            case self::CASHED_BONUS:
                $info['cash_money'] = $price['cash_money'];
                $info['pass'] = $info['cash_money'] >= price_format($this->condition) ? true : false;
                return $info;

            case self::ALL_MEMBERS:
                $count = $share->all_children;
                $info['all_children'] = $count ? $count : 0;
                $info['pass'] = $info['all_children'] >= $this->condition ? true : false;
                return $info;

            case self::ALL_SHARE_ORDERS:
                $count = $share->all_order;
                $info['all_order'] = $count ? $count : 0;
                $info['pass'] = $info['all_order'] >= $this->condition ? true : false;
                return $info;

            case self::TOTAL_BONUS:
                $count = ShareOrder::find()->alias('so')->leftJoin(['od' => OrderDetail::tableName()], 'od.id = so.order_detail_id')
                        ->andWhere(['so.is_delete' => 0, 'so.is_refund' => 0])
                        ->andWhere(['or', ['first_parent_id' => Yii::$app->user->id], ['second_parent_id' => Yii::$app->user->id], ['third_parent_id' => Yii::$app->user->id]])
                        ->sum('od.total_price') ?? 0;
                $info['all_money'] = $count ? $count : 0;
                $info['pass'] = $info['all_money'] >= $this->condition ? true : false;
                return $info;

            default:
                throw new \Exception('未知的条件');
        }

    }
}
