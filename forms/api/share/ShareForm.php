<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\api\share;

use app\core\response\ApiCode;
use app\forms\common\share\CommonShareLevel;
use app\forms\mall\share\ShareCustomForm;
use app\models\AdminInfo;
use app\models\Mall;
use app\models\Order;
use app\models\Share;
use app\models\ShareCash;
use app\models\ShareOrder;
use app\models\UserIdentity;
use app\forms\common\share\CommonShareTeam;
use app\models\ShareSetting;
use app\models\Model;
use app\plugins\bonus\forms\mall\SettingForm;

class ShareForm extends Model
{
    public $user_id;

    public function search()
    {
        $identity = UserIdentity::findOne([
            'is_delete' => 0,
            'is_distributor' => 1,
            'user_id' => \Yii::$app->user->id,
        ]);
        if (!$identity) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '权限不足'
            ];
        }

        //获取我的团队
        $team = new CommonShareTeam();
        $team->mall = \Yii::$app->mall;
        $setting = ShareSetting::get(\Yii::$app->mall->id, ShareSetting::LEVEL, 0);
        $first_count = $setting > 0 ? count($team->info(\Yii::$app->user->id, 1)) : 0;
        $second_count = $setting > 1 ? count($team->info(\Yii::$app->user->id, 2)) : 0;
        $third_count = $setting > 2 ? count($team->info(\Yii::$app->user->id, 3)) : 0;
        $list['team_count'] = $first_count + $second_count + $third_count; //团队数量

        $order = $this->getOrderCount();
        $list['order_money'] = $order['order_money']; //订单总额
        $list['order_money_un'] = $order['order_money_un']; //未结算佣金
        $shareLevel = CommonShareLevel::getInstance()->getShareLevelByLevel(\Yii::$app->user->identity->share->level);
        $list['level_name'] = $shareLevel ? $shareLevel->name : '无';
        $list['level'] = \Yii::$app->user->identity->share->level;

        //获取分销佣金及提现
        $price = $this->getPrice();

        //权限判断
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        try {
            if (!in_array('bonus', $permission)) {
                $bonusSetting = [];
            } else {
                $plugin = \Yii::$app->plugin->getPlugin('bonus');
                /**@var SettingForm $form * */
                if (method_exists($plugin, "getBonusForm")) {
                    $form = $plugin->getBonusForm();
                    $bonusSetting = $form->search()['data'];
                } else {
                    $bonusSetting = [];
                }
            }
        } catch (\Exception $exception) {
            $bonusSetting = [];
        }

        try {
            if (!in_array('stock', $permission)) {
                $stockSetting = [];
            } else {
                $plugin = \Yii::$app->plugin->getPlugin('stock');
                /**@var SettingForm $form * */
                if (method_exists($plugin, "getStockForm")) {
                    $form = $plugin->getStockForm();
                    $stockSetting = $form->search()['data'];
                } else {
                    $stockSetting = [];
                }
            }
        } catch (\Exception $exception) {
            $stockSetting = [];
        }

        try {
            if (!in_array('region', $permission)) {
                $regionSetting = [];
            } else {
                $plugin = \Yii::$app->plugin->getPlugin('region');
                /**@var SettingForm $form * */
                if (method_exists($plugin, "getRegionForm")) {
                    $form = $plugin->getRegionForm();
                    $regionSetting = $form->search()['data'];
                } else {
                    $regionSetting = [];
                }
            }
        } catch (\Exception $exception) {
            $regionSetting = [];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => array_merge($list, $price),
                'bonus_setting' => $bonusSetting,
                'stock_setting' => $stockSetting,
                'region_setting' => $regionSetting
            ],
        ];
    }


    public function getOrderCount()
    {
        $user_id = \Yii::$app->user->id;
        $orderQuery = Order::find()->andWhere(['!=', 'cancel_status', 1])
            ->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_recycle' => 0, 'is_delete' => 0])
            ->select('id');
        $query = ShareOrder::find()->select("
            SUM(CASE WHEN first_parent_id = $user_id THEN first_price ELSE 0 END) first,
            SUM(CASE WHEN second_parent_id = $user_id THEN second_price ELSE 0 END) second,
            SUM(CASE WHEN third_parent_id = $user_id THEN third_price ELSE 0 END) third
        ")->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_refund' => 0,
        ])->andWhere(['order_id' => $orderQuery]);
        $all = $query->asArray()->one();
        $info['order_money'] = doubleval(sprintf('%.2f', $all['first'] + $all['second'] + $all['third']));

        $un_all = $query->andWhere(['is_transfer' => 0])->asArray()->one();
        $info['order_money_un'] = doubleval(sprintf('%.2f', $un_all['first'] + $un_all['second'] + $un_all['third']));
        return $info;
    }

    /**
     * 分销佣金
     * @return [type] [description]
     */
    public function brokerage()
    {
        $price = $this->getPrice();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $price,
            ],
        ];
    }

    /**
     * 自定义接口
     * @return [type] [description]
     */
    public function customize()
    {
        //获取分销自定义数据
        $custom_form = new ShareCustomForm();
        $list = $custom_form->getData();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ],
        ];
    }

    /**
     * 获取佣金相关信息
     * @param int $user_id
     * @return array
     */
    public function getPrice($user_id = 0)
    {
        $user_id = empty($user_id) ? \Yii::$app->user->id : $user_id;
        $share = Share::findOne(['mall_id' => \Yii::$app->mall->id, 'user_id' => $user_id]);
        /* @var ShareCash[] $list */
        $list = ShareCash::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => $user_id,
            'is_delete' => 0,
        ])->andWhere(['<', 'status', 3])->all();
        $unPay = 0;
        $cashMoney = 0;
        $totalCash = 0;
        foreach ($list as $cash) {
            $totalCash += floatval($cash->price);
            switch ($cash->status) {
                case 0:
                    break;
                case 1:
                    $unPay += floatval($cash->price);
                    break;
                case 2:
                    $cashMoney += floatval($cash->price);
                    break;
                default:
            }
        }

        return [
            'total_money' => $share->total_money, //分销佣金
            'money' => $share->money,  //可提现
            'un_pay' => price_format($unPay),  //待打款
            'cash_money' => price_format($cashMoney),  //已提现
            'total_cash' => price_format($totalCash),  //提现明细
        ];
    }
}
