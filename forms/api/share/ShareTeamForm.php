<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/23
 * Time: 15:29
 */

namespace app\forms\api\share;

use app\core\response\ApiCode;
use app\forms\common\share\CommonShareTeam;
use app\models\Model;
use app\models\ShareOrder;
use app\models\ShareSetting;
use app\models\User;
use app\models\UserInfo;

class ShareTeamForm extends Model
{
    public $status;

    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['status', 'page', 'limit'], 'integer'],
            [['limit'], 'default', 'value' => 20],
            [['page', 'status'], 'default', 'value' => 1],
        ];
    }

    // 获取团队详情
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $userId = \Yii::$app->user->id;
            $setting = ShareSetting::get(\Yii::$app->mall->id, ShareSetting::LEVEL, 0);
            $form = new CommonShareTeam();
            $form->mall = \Yii::$app->mall;
            $idList = $form->info($userId, $this->status);

            // 推广人数
            $peopleCount = UserInfo::find()->alias('i')->where(['i.parent_id' => $idList])
                ->select(['count(1) count', 'i.parent_id'])
                ->groupBy('i.parent_id')
                ->asArray()->all();
            $peopleCount = array_column($peopleCount, 'count', 'parent_id');
            // 佣金已结算的订单
            $shareOrder = ShareOrder::find()->alias('so')->select(['so.user_id',
                "sum(case when `so`.`first_parent_id` = {$userId} then `so`.`first_price` 
                when `so`.`second_parent_id` = {$userId} then `so`.`second_price` 
                when `so`.`third_parent_id` = {$userId} then `so`.`third_price` end) money",
            ])->andWhere([
                'or',
                ['so.first_parent_id' => $userId],
                ['so.second_parent_id' => $userId],
                ['so.third_parent_id' => $userId]
            ])->andWhere([
                'so.is_delete' => 0, 'so.is_transfer' => 1, 'so.mall_id' => \Yii::$app->mall->id,
                'so.user_id' => $idList])
                ->groupBy('so.order_id')->asArray()->all();
            $shareOrderList = [];
            foreach ($shareOrder as $item) {
                if (!isset($shareOrderList[$item['user_id']])) {
                    $shareOrderList[$item['user_id']] = [
                        'orderPrice' => 0,
                        'orderCount' => 0
                    ];
                }
                $shareOrderList[$item['user_id']]['orderCount']++;
                $shareOrderList[$item['user_id']]['orderPrice'] += $item['money'];
            }
            $res = User::find()->alias('u')->joinwith('userInfo ui')
                ->where(['u.id' => $idList, 'u.mall_id' => \Yii::$app->mall->id])
                ->select(['u.id', 'u.nickname'])
                ->orderBy("ui.junior_at DESC,u.id DESC")
                ->apiPage($this->limit, $this->page)->asArray()->all();

            foreach ($res as $index => &$item) {
                $item['avatar'] = $item['userInfo']['avatar'];
                $item['junior_at'] = $item['userInfo']['junior_at'];
                if (!isset($shareOrderList[$item['id']])) {
                    $shareOrderList[$item['id']] = [
                        'orderPrice' => 0,
                        'orderCount' => 0
                    ];
                }
                $item['orderPrice'] = price_format($shareOrderList[$item['id']]['orderPrice']);
                $item['orderCount'] = intval($shareOrderList[$item['id']]['orderCount']);
                $item['peopleCount'] = isset($peopleCount[$item['id']]) ? $peopleCount[$item['id']] : 0;
                unset($item['userInfo']);
            }
            unset($item);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $res,
                    'first_count' => $setting > 0 ? count($form->info(\Yii::$app->user->id, 1)) : -1,
                    'second_count' => $setting > 1 ? count($form->info(\Yii::$app->user->id, 2)) : -1,
                    'third_count' => $setting > 2 ? count($form->info(\Yii::$app->user->id, 3)) : -1,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => $e
            ];
        }
    }
}
