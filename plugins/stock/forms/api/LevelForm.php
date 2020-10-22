<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/3
 * Time: 16:23
 */

namespace app\plugins\stock\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\OrderDetail;
use app\models\Share;
use app\models\ShareCash;
use app\models\ShareOrder;
use app\models\User;
use app\plugins\stock\forms\common\MsgService;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\StockLevelUp;
use app\plugins\stock\models\StockUser;
use Yii;

class LevelForm extends Model
{
    public function rules()
    {
        return [
        ];
    }

    public function search()
    {
        /* @var StockUser $user */
        $user = StockUser::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->with(['user.userInfo', 'level'])
            ->one();

        $list = StockLevel::find()->where(['is_delete' => 0, 'mall_id' => Yii::$app->mall->id])
            ->andWhere(['>', 'condition', 0])
            ->andWhere(['>', 'bonus_rate', $user['level']['bonus_rate']])
            ->orderBy('is_default desc,condition,bonus_rate,created_at')
            ->page($pagination)
            ->asArray()
            ->all();
        $up_info = StockLevelUp::findOne(['mall_id' => Yii::$app->mall->id]);

        $share = Share::findOne(['user_id' => Yii::$app->user->id]);
        $condition = 0;
        $is_big_rate = 1;//是否是最高分红等级，1是，0否
        foreach ($list as &$item) {
            $item['is_this'] = 0;//当前对应的等级
            $item['is_up'] = 0;//可以升级的等级
            if ($user->level_id == $item['id']) {
                $item['is_this'] = 1;
            }
            if ($user['level']['bonus_rate'] < $item['bonus_rate']) {
                $is_big_rate = 0;
            }
            switch ($up_info->type) {
                //1下线总人数，2累计佣金总额，3已提现佣金总额，4分销订单总数，5分销订单总金额
                case 1:
                    $condition = $share->all_children;
                    break;
                case 2:
                    $condition = $share->total_money;
                    break;
                case 3:
                    $condition = ShareCash::find()
                            ->where(['mall_id' => Yii::$app->mall->id, 'user_id' => Yii::$app->user->id, 'is_delete' => 0, 'status' => 2])
                            ->sum('price') ?? 0;
                    break;
                case 4:
                    $condition = $share->all_order;
                    break;
                case 5:
                    $condition = ShareOrder::find()->alias('so')->leftJoin(['od' => OrderDetail::tableName()], 'od.id = so.order_detail_id')
                            ->andWhere(['so.is_delete' => 0, 'so.is_refund' => 0])
                            ->andWhere(['or', ['first_parent_id' => Yii::$app->user->id], ['second_parent_id' => Yii::$app->user->id], ['third_parent_id' => Yii::$app->user->id]])
                            ->sum('od.total_price') ?? 0;
                    break;
            }
            if ($item['condition'] > 0 && $condition >= $item['condition']) {
                $item['is_up'] = 1;
            }
        }
        $level_up_info = StockLevelUp::findOne(['mall_id' => Yii::$app->mall->id]);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'user' => (object)[
                    'nickname' => $user['user']['nickname'],
                    'avatar' => $user['user']['userInfo']['avatar'],
                    'level_name' => $user['level']['name'],
                    'level_rate' => $user['level']['bonus_rate'],
                    'condition' => $condition,
                    'is_big_rate' => $is_big_rate
                ],
                'level_up_remark' => $level_up_info->remark,
                'up_type' => $up_info->type,
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = Yii::$app->db->beginTransaction();
        try {
            $up_info = StockLevelUp::findOne(['mall_id' => Yii::$app->mall->id]);
            /* @var StockUser $user */
            $user = StockUser::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->with(['level'])
                ->one();
            $share = Share::findOne(['user_id' => Yii::$app->user->id]);

            $model = StockLevel::find()->where(['is_delete' => 0, 'mall_id' => Yii::$app->mall->id]);
            $condition = 0;//条件值
            switch ($up_info->type) {
                //1下线总人数，2累计佣金总额，3已提现佣金总额，4分销订单总数，5分销订单总金额
                case 1:
                    $condition = $share->all_children;
                    break;
                case 2:
                    $condition = $share->total_money;
                    break;
                case 3:
//                    $condition = $share->total_money - $share->money;
                    $condition = ShareCash::find()
                            ->where(['mall_id' => Yii::$app->mall->id, 'user_id' => Yii::$app->user->id, 'is_delete' => 0, 'status' => 2])
                            ->sum('price') ?? 0;
                    break;
                case 4:
                    $condition = $share->all_order;
                    break;
                case 5:
                    $condition = ShareOrder::find()->alias('so')->leftJoin(['od' => OrderDetail::tableName()], 'od.id = so.order_detail_id')
                            ->andWhere(['so.is_delete' => 0, 'so.is_refund' => 0])
                            ->andWhere(['or', ['first_parent_id' => Yii::$app->user->id], ['second_parent_id' => Yii::$app->user->id], ['third_parent_id' => Yii::$app->user->id]])
                            ->sum('od.total_price') ?? 0;
                    break;
            }
            if ($condition <= 0) {
                throw new \Exception('您暂未达到升级条件，再继续努力哦~');
            }
            //取达到条件的分红比例最大的等级进行升级
            /* @var StockLevel $level_info */
            $level_info = $model
                ->andWhere(['<=', 'condition', $condition])
                ->andWhere(['>', 'condition', 0])
                ->orderBy('bonus_rate desc')
                ->one();
            if ($user->level_id == $level_info->id) {
                throw new \Exception('您暂未达到升级条件，再继续努力哦~');
            }

            $user->level_id = $level_info->id;
            if (!$user->save()) {
                throw new \Exception($this->getErrorMsg($user));
            }
            $t->commit();

            $smsUser = User::findOne(['id' => $user->user_id]);
            $mobile = $user->stockInfo->phone ?? $smsUser->mobile;
            MsgService::sendSms($mobile, 2, $level_info->name, $level_info->bonus_rate);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '恭喜！股东升级成功',
                'data' => $level_info
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine(),
            ];
        }
    }

}
