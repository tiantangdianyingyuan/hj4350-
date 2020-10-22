<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\forms\api;


use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsDetail;
use app\models\Goods;
use app\models\Mall;
use app\models\MallSetting;
use app\models\Model;
use app\models\UserInfo;
use app\plugins\scan_code_pay\forms\common\ActivityForm;
use app\plugins\scan_code_pay\forms\common\CommonActivityForm;
use app\plugins\scan_code_pay\forms\common\CommonScanCodePaySetting;
use app\plugins\scan_code_pay\forms\common\GoodsEditForm;
use app\plugins\scan_code_pay\models\ScanCodePayActivities;
use yii\helpers\ArrayHelper;
use app\plugins\scan_code_pay\Plugin;

class IndexForm extends Model
{
    public function search()
    {
        try {
            $activity = (new CommonActivityForm())->search();
            $setting = (new CommonScanCodePaySetting())->getSetting();
            $integral = $this->getIntegral();
            $goods = $this->getGoods();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'activity' => $activity,
                    'integral' => $integral,
                    'goods' => $goods,
                    'setting' => $setting
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    /**
     * 获取积分信息
     * @return array
     * @throws \Exception
     */
    private function getIntegral()
    {
        $mall = new Mall();
        $integral = [];
        $mallSetting = $mall->getMallSetting(['member_integral', 'member_integral_rule']);
        $integral = array_merge($integral, $mallSetting);
        $userInfo = UserInfo::findOne(['user_id' => \Yii::$app->user->id]);
        if (!$userInfo) {
            throw new \Exception('用户不存在');
        }
        $integral['user_integral_num'] = $userInfo->integral;

        return $integral;
    }

    private function getGoods()
    {

        $goods = $this->goods();
        if (!$goods) {
            $form = new GoodsEditForm();
            $res = $form->save();

            $goods = $this->goods();
        }


        $newGoods = ArrayHelper::toArray($goods);
        $newGoods['attr_groups'] = \Yii::$app->serializer->decode($goods->attr_groups);
        $newGoods['attr'] = ArrayHelper::toArray($goods->attr);

        return $newGoods;
    }

    public function goods()
    {
        /** @var Goods $goods */
        $goods = Goods::find()->with(['attr'])
            ->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'sign' => (new Plugin())->getName(),
            ])->one();

        return $goods;
    }
}