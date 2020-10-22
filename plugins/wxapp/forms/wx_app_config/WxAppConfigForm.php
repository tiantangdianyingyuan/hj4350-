<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\wxapp\forms\wx_app_config;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\wxapp\models\WxappConfig;
use yii\helpers\ArrayHelper;

class WxAppConfigForm extends Model
{
    public $id;
    public $page;


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '微信配置ID',
        ];
    }

    public function getDetail()
    {
        /**@var WxappConfig $detail**/
        $detail = WxappConfig::find()
            ->where(['mall_id' => \Yii::$app->mall->id])
            ->with(['service'])
            ->one();

        if ($detail) {
            $newDetail = ArrayHelper::toArray($detail);
            $newDetail['is_choise'] = 0;
            $newDetail['service_appid'] = '';
            $newDetail['service_mchid'] = '';
            $newDetail['service_key'] = '';
            $newDetail['service_key_pem'] = '';
            $newDetail['service_cert_pem'] = '';
            if ($detail->service) {
                $newDetail['is_choise'] = $detail->service->is_choise;
                $newDetail['service_appid'] = $detail->service->appid;
                $newDetail['service_mchid'] = $detail->service->mchid;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $newDetail,
                ]
            ];
        }

        return [
            'code' => ApiCode::CODE_ERROR,
            'msg' => '信息未配置',
        ];
    }
}
