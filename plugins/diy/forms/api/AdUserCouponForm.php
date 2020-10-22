<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\forms\api;


use app\core\response\ApiCode;
use app\forms\common\coupon\CommonCoupon;
use app\models\Coupon;
use app\models\Model;
use app\plugins\diy\forms\common\CommonTemplate;
use app\plugins\diy\models\DiyAdLog;

class AdUserCouponForm extends Model
{
    public $template_id;

    public function rules()
    {
        return [
            ['template_id', 'required'],
            ['template_id', 'integer'],
        ];
    }

    public function receive()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $diy = new CommonTemplate();
            $diy->mall = \Yii::$app->mall;
            $list = $diy->getTemplate($this->template_id);

            if (empty($data = \yii\helpers\BaseJson::decode($list['data']))) {
                throw new \Exception('配置错误');
            }
            $data = array_filter($data, function ($item) {
                return $item['id'] === 'ad' && $item['data']['type'] === 'rewarded-video';
            });

            $newData = array_column($data, 'data');
            if (empty($newData)) {
                throw new \Exception('非激励式视频');
            }

            $success = [];
            foreach ($newData as $item) {
                switch ($item['award_type']) {
                    case 1:
                        $this->checkUser($this->template_id,$item['award_limit_type'],$item['award_limit']);
                        $this->setIntegral($this->template_id,$item['award_num'], $success);
                        break;
                    case 2:
                        $this->checkUser($this->template_id,$item['award_limit_type'],$item['award_limit']);
                        $this->setUserCoupon($this->template_id,$item['award_coupons'], $success);
                        break;
                    default:
                        continue;
                        break;
                }
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
     * @param int $template_id
     * @param $award_limit_type
     * @param $award_limit
     * @throws \Exception
     */
    protected function checkUser(int $template_id,$award_limit_type,$award_limit)
    {
        if(empty($award_limit_type)){
            return;
        }
        $query = DiyAdLog::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => \Yii::$app->user->id,
            'template_id' => $template_id,
            'is_delete' => 0
        ]);
        $award_limit_type == 2 && $query->andWhere("to_days(created_at) = to_days(now())");

        if($award_limit <= $query->count()) {
            throw new \Exception('奖励领取已达上限');
        }
    }

    /**
     * 储存日志
     * @param int $template_id
     */
    private function saveLog(int $template_id){
        $model = new DiyAdLog();
        $model->mall_id = \Yii::$app->mall->id;
        $model->user_id = \Yii::$app->user->id;
        $model->template_id = $template_id;
        $model->raffled_at = mysql_timestamp();
        $model->save();
    }

    /**
     * @param $num
     * @param $success
     */
    protected function setIntegral($template_id,$num, &$success)
    {
        try {
            $t = \Yii::$app->db->beginTransaction();

            $desc = 'DIY流量主广告奖励' . (int)$num . '积分';
            \Yii::$app->currency->setUser(\Yii::$app->user->identity)->integral->add((int)$num, $desc);

            $this->saveLog($template_id);
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
     * @param $template_id
     * @param $newData
     * @param $success
     * @throws \yii\db\Exception
     */
    protected function setUserCoupon($template_id,$newData, &$success)
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
                $status = $commonCoupon->receive($coupon, $class, 'DIY流量主广告奖励');

                $coupon = \yii\helpers\ArrayHelper::toArray($coupon);
                $coupon['share_type'] = 4;
                $coupon['page_url'] = '';
                $status && array_push($success, $coupon);
            }
        }

        count($newData) && $this->saveLog($template_id);
        //program 2
        //$model = new UserCoupon();
    }
}