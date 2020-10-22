<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\fxhb\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;
use app\plugins\fxhb\forms\common\CommonRecommend;

class RecommendForm extends Model
{
    public $data;

    public function rules()
    {
        return [
            [['data'], 'safe']
        ];
    }

    public function save()
    {
        try {
            $data = \Yii::$app->serializer->decode($this->data);

            $setting = CommonOption::set(
                Option::NAME_FXHB_RECOMMEND_SETTING,
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

    public function getSetting()
    {
        $form = new CommonRecommend();
        $setting = $form->getSetting();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $setting
            ]
        ];
    }
}