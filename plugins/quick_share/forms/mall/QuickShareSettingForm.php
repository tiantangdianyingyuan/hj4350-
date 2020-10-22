<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\quick_share\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\plugins\quick_share\forms\common\CommonPoster;
use app\plugins\quick_share\forms\common\CommonQuickShare;
use app\plugins\quick_share\models\QuickShareSetting;

class QuickShareSettingForm extends Model
{
    public $type;
    public $goods_poster;
    public function rules()
    {
        return [
            [['goods_poster'], 'required'],
            [['type'], 'integer'],
            [['goods_poster'], 'trim']
        ];
    }

    public function attributeLabels()
    {
        return [
            'type' => '发圈对象 仅素材 1全部商品',
            'goods_poster' => '商品海报',
        ];
    }

    public function getList()
    {
        $setting = CommonQuickShare::getSetting();
        $setting = \yii\helpers\ArrayHelper::toArray($setting);
        $setting['goods_poster'] = (new CommonOptionP())->poster($setting['goods_poster'], CommonPoster::getPosterDefault());
        if (isset($setting['goods_poster']['price'])) {
            unset($setting['goods_poster']['price']);
        }

        //海报默认值
        $setting['goods_poster']['name']['text'] = '随风流水的灵动，无声的控诉着时装的"装"，同时亦诠释着服装的"简单和纯良"，静...';
        ///////
        if ($setting['goods_poster']['bg_pic']['url'] === \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster_bg.png') {
            $setting['goods_poster']['bg_pic']['url'] = '';
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $setting,
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };
        $model = QuickShareSetting::findOne([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$model) {
            $model = new QuickShareSetting();
        }
        $model->attributes = $this->attributes;
        $model->mall_id = \Yii::$app->mall->id;
        $model->goods_poster = \Yii::$app->serializer->encode((new CommonOptionP())->saveEnd($this->goods_poster));
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }
}
