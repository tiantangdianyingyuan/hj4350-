<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\goods;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\common\goods\CommonRecommendSettingForm;
use app\models\Model;
use app\models\Option;

class RecommendSettingForm extends Model
{
    public $data;

    public function getSetting()
    {
        $form = new CommonRecommendSettingForm();
        $setting = $form->getSetting();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $setting
            ]
        ];
    }

    public function save()
    {
        try {
            $data = \Yii::$app->serializer->decode($this->data);
            if ($data['goods']['goods_num'] > 10) {
                throw new \Exception('推荐商品显示数量最多10个');
            }
            $data['goods']['goods_num'] = (int)$data['goods']['goods_num'];

            $setting = CommonOption::set(
                Option::NAME_RECOMMEND_SETTING,
                $data,
                \Yii::$app->mall->id,
                Option::GROUP_APP
            );

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}