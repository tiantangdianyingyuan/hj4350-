<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 */

namespace app\plugins\community\forms\api;

use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\plugins\community\models\CommunityAddress;
use app\plugins\community\models\CommunityBonusLog;
use app\plugins\community\models\CommunityOrder;
use yii\db\Exception;

/**
 * @property Mall $mall
 */
class ProfitForm extends Model
{
    public $id;
    public $type;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['type'], 'string'],
        ];
    }


    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            //团长页面
            $address = CommunityAddress::findOne(['user_id' => \Yii::$app->user->id, 'is_default' => 1, 'is_delete' => 0]);
            if (empty($address)) {
                throw new Exception('请先成为社区团长');
            }

            $list = CommunityOrder::find()->alias('co')->leftJoin(['o' => Order::tableName()], 'o.id = co.order_id')
                ->andWhere(['co.is_delete' => 0, 'o.is_delete' => 0, 'o.is_pay' => 1, 'o.is_recycle' => 0])->andWhere(['!=', 'o.cancel_status', 1])
                ->andWhere(['co.middleman_id' => \Yii::$app->user->id])
                ->orderBy('o.created_at desc')->groupBy('co.activity_id')
                ->leftJoin(['bl' => CommunityBonusLog::tableName()], 'bl.order_id = co.order_id')
                ->select(['order_num' => 'count(co.order_id)', 'total_pay_price' => 'sum(o.total_pay_price)',
                    'stay_price' => 'sum(case when bl.id > 0 then co.profit_price else 0 end)',
                    'profit_price' => 'sum(co.profit_price)', 'co.activity_id'])
                ->with('activity')
                ->page($pagination)
                ->asArray()
                ->all();


            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'list' => $list,
                'pagination' => $pagination,
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'data' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
//            if (!$this->id) {
//                throw new Exception('活动ID不能为空');
//            }
            //团长页面
            $address = CommunityAddress::findOne(['user_id' => \Yii::$app->user->id, 'is_default' => 1, 'is_delete' => 0]);
            if (empty($address)) {
                throw new Exception('请先成为社区团长');
            }

            $model = CommunityOrder::find()->alias('co')->leftJoin(['o' => Order::tableName()], 'o.id = co.order_id')
                ->andWhere(['co.is_delete' => 0, 'o.is_delete' => 0, 'o.is_pay' => 1, 'o.is_recycle' => 0])->andWhere(['!=', 'o.cancel_status', 1])
                ->andWhere(['co.middleman_id' => \Yii::$app->user->id])//, 'co.id' => $this->id])
                ->orderBy('o.created_at desc');

            //总数据
            $model_1 = clone $model;
            $data['all_price'] = $model_1->leftJoin(['bl' => CommunityBonusLog::tableName()], 'bl.order_id = co.order_id')
                ->select('sum(co.profit_price) as all_price')
                ->asArray()
                ->one()['all_price'];

            switch ($this->type) {
                case 'month':
                    $model->andWhere(['>=', 'o.created_at', date('Y-m-01 00:00:00', strtotime(date("Y-m-d")))]);
                    $model->andWhere(['<=', 'o.created_at', date('Y-m-d 23:59:59', strtotime(date('Y-m-01', strtotime(date("Y-m-d"))) . " +1 month -1 day"))]);
                    break;
                case 'day':
                    $model->andWhere(['>=', 'o.created_at', date('Y-m-d 00:00:00')]);
                    $model->andWhere(['<=', 'o.created_at', date('Y-m-d 23:59:59')]);
                    break;
            }
            $model_2 = clone $model;
            $data['stay_price'] = $model_2->leftJoin(['bl' => CommunityBonusLog::tableName()], 'bl.order_id = co.order_id')
                ->select('sum(case when bl.id > 0 then 0 else co.profit_price end) as stay_price')
                ->asArray()
                ->one()['stay_price'];
            //列表
            $list = $model->groupBy('co.order_id')
                ->select('o.order_no,o.created_at,co.profit_price,co.order_id')
                ->page($pagination)
                ->with('bonusLog')
                ->asArray()
                ->all();

            foreach ($list as &$item) {
                $item['status'] = empty($item['bonusLog']) ? '待结算' : '已结算';
                unset($item['bonusLog']);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list,
                    'data' => $data
                ],
                'pagination' => $pagination
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'data' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
