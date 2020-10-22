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
use app\plugins\diy\models\DiyCouponLog;
use app\plugins\diy\models\DiyTemplate;

class UserCouponForm extends Model
{
    public $template_id;
    public $coupon_id;

    public function rules()
    {
        return [
            [['template_id', 'coupon_id'], 'required'],
            [['template_id', 'coupon_id'], 'integer'],
        ];
    }


    public function receive()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $this->checkUser();
            $this->checkCoupon();
            $this->setUserCoupon($coupon);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '领取成功',
                'data' => [
                    'coupon' => $coupon
                ],
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    //模板查询
    private function getModuleModel($id)
    {
        $query = DiyTemplate::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'type' => DiyTemplate::TYPE_MODULE,
            'is_delete' => 0,
            'id' => $id,
        ]);
        $list = $query->asArray()->one();
        if ($list) {
            return \yii\helpers\BaseJson::decode($list['data']);
        }
    }

    /**
     * 检查手动添加优惠券id
     * @throws \Exception
     */
    private function checkCoupon()
    {
        $diy = new CommonTemplate();
        $diy->mall = \Yii::$app->mall;
        $list = $diy->getTemplate($this->template_id);

        if (empty($data = \yii\helpers\BaseJson::decode($list['data']))) {
            throw new \Exception('配置错误');
        }

        $coupons = [];
        $func = function ($data) use (&$func, &$coupons) {
            foreach ($data as $item) {
                if ($item['id'] === 'module' && isset($item['data']['list'])) {
                    foreach ($item['data']['list'] as $item2) {
                        $model = $this->getModuleModel($item2['id']);
                        $model && $func($model);
                    }
                }
                if (
                    $item['id'] === 'coupon'
                    && isset($item['data']['addType'])
                    && $item['data']['addType'] === 'manual'
                    && is_array($item['data']['coupons'])
                ) {
                    $coupons = array_merge($coupons, $item['data']['coupons']);
                }
            }
        };
        $func($data);
        $couponIds = array_column($coupons, 'id');
        if (array_search($this->coupon_id, $couponIds) === false) {
            throw new \Exception('DIY优惠券不存在');
        }
    }

    /**
     * 检测用户是否已领取
     * @throws \Exception
     */
    protected function checkUser()
    {
        $coupon_id = $this->coupon_id;
        $query = DiyCouponLog::find()->alias('d')->where([
            'd.mall_id' => \Yii::$app->mall->id,
            'd.user_id' => \Yii::$app->user->id,
            'd.template_id' => $this->template_id,
            'd.is_delete' => 0
        ])->innerJoinwith(['userCoupon u' => function ($query) use ($coupon_id) {
            $query->where(['u.coupon_id' => $coupon_id]);
        }]);

        if ($query->exists()) {
            throw new \Exception('无法重复领取');
        }
    }

    protected function setUserCoupon(&$coupon)
    {
        $commonCoupon = new CommonCoupon();
        $commonCoupon->mall = \Yii::$app->mall;
        $commonCoupon->user = \Yii::$app->user->identity;

        $coupon = Coupon::findOne($this->coupon_id);
        $class = new UserCoupon($coupon, $this->template_id, $commonCoupon->user);
        $status = $commonCoupon->receive($coupon, $class, 'DIY手动添加优惠券领取');

        if (!$status) {
            throw new \Exception('DIY优惠券领取失败');
        }
    }
}