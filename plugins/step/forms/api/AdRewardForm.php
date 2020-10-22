<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\forms\api;

use app\core\response\ApiCode;
use app\forms\common\coupon\CommonCoupon;
use app\models\Coupon;
use app\models\Model;
use app\plugins\step\models\StepAd;
use app\plugins\step\models\StepAdLog;

class AdRewardForm extends Model
{
    public $id;

    public function rules()
    {
        return [
            ['id', 'required'],
            ['id', 'integer'],
        ];
    }

    public function receive()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $ad = StepAd::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
            ]);
            if (empty($ad)) {
                throw new \Exception('配置错误');
            };
            if ($ad['type'] !== 'rewarded-video') {
                throw new \Exception('非激励式视频');
            }

            $success = [];
            $reward_data = \yii\helpers\BaseJson::decode($ad['reward_data']);
            switch ($reward_data['award_type']) {
                case 1:
                    $this->checkUser($this->id,$reward_data['award_limit_type'],$reward_data['award_limit']);
                    $this->setIntegral($this->id,$reward_data['award_num'], $success);
                    break;
                case 2:
                    $this->checkUser($this->id,$reward_data['award_limit_type'],$reward_data['award_limit']);
                    $this->setUserCoupon($this->id,$reward_data['award_coupons'], $success);
                    break;
                default:
                    continue;
                    break;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '领取成功',
                'data' => [
                    'list' => $success
                ],
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * 日志检测
     * @param int $ad_id
     * @param $award_limit_type
     * @param $award_limit
     * @throws \Exception
     */
    protected function checkUser(int $ad_id,$award_limit_type,$award_limit)
    {
        if(empty($award_limit_type)){
            return;
        }
        $query = StepAdLog::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => \Yii::$app->user->id,
            'ad_id' => $ad_id,
            'is_delete' => 0
        ]);
        $award_limit_type == 2 && $query->andWhere("to_days(created_at) = to_days(now())");

        if($award_limit <= $query->count()) {
            throw new \Exception('奖励领取已达上限');
        }
    }

    /**
     * 储存日志
     * @param int $ad_id
     */
    private function saveLog(int $ad_id){
        $model =new StepAdLog();
        $model->mall_id = \Yii::$app->mall->id;
        $model->user_id = \Yii::$app->user->id;
        $model->ad_id = $ad_id;
        $model->raffled_at = mysql_timestamp();
        $model->save();
    }

    protected function setIntegral($id,$num, &$success)
    {
        try {
            $t = \Yii::$app->db->beginTransaction();

            $desc = '步数宝流量主广告奖励' . (int)$num . '积分';
            \Yii::$app->currency->setUser(\Yii::$app->user->identity)->integral->add((int)$num, $desc);

            $this->saveLog($id);

            $t->commit();
            //todo plugin-yh-2 待统一
            array_push($success, [
                'share_type' => 2,
                'content' => '即时到账',
                'page_url' => '',
                'name' => sprintf('%s积分', $num),
                'invite_num' => $num,
            ]);
        } catch (\Exception $e) {
            $t->rollBack();
            //return $this->getErrorResponse($model);
        }
    }

    /**
     * @param $id
     * @param $newData
     * @param $success
     * @throws \yii\db\Exception
     */
    protected function setUserCoupon($id,$newData, &$success)
    {
        //program 1
        $commonCoupon = new CommonCoupon();
        $commonCoupon->mall = \Yii::$app->mall;
        $commonCoupon->user = \Yii::$app->user->identity;

        for ($i = 0; $i < count($newData); $i++) {
            for ($j = 1; $j <= intval($newData[$i]['send_num']); $j++) {
                $coupon_id = $newData[$i]['coupon_id'];
                $coupon = Coupon::findOne(['id' => $coupon_id]);
                $class = new AdUserCoupon($coupon, $commonCoupon->user);
                $status = $commonCoupon->receive($coupon, $class, '步数宝流量主广告奖励');

                $coupon = \yii\helpers\ArrayHelper::toArray($coupon);
                $coupon['share_type'] = 4;
                $coupon['page_url'] = '';
                $status && array_push($success, $coupon);
            }
        }

        count($newData) && $this->saveLog($id);
        //program 2
        //$model = new UserCoupon();
    }
}