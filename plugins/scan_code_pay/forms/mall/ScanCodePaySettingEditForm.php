<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\scan_code_pay\forms\common\CommonScanCodePaySetting;
use app\plugins\scan_code_pay\forms\common\GoodsEditForm;
use app\plugins\scan_code_pay\models\ScanCodePaySetting;

class ScanCodePaySettingEditForm extends Model
{
    public $is_scan_code_pay;
    public $payment_type;
    public $is_share;
    public $is_sms;
    public $is_mail;
    public $share_type;
    public $share_commission_first;
    public $share_commission_second;
    public $share_commission_third;
    public $poster;
    public $goods;

    public function rules()
    {
        return [
            [['is_scan_code_pay', 'is_share', 'share_type', 'is_sms', 'is_mail'], 'required'],
            [['share_commission_first', 'share_commission_second', 'share_commission_third'], 'number'],
            [['poster', 'payment_type', 'goods'], 'safe'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $model = ScanCodePaySetting::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
            if (!$model) {
                $model = new ScanCodePaySetting();
                $model->mall_id = \Yii::$app->mall->id;
            }
            $model->is_scan_code_pay = $this->is_scan_code_pay;
            $model->payment_type = \Yii::$app->serializer->encode($this->payment_type ?: []);
            $model->is_share = $this->is_share;
            $model->is_sms = $this->is_sms;
            $model->is_mail = $this->is_mail;
            $model->share_type = $this->share_type;
            $model->share_commission_first = $this->share_commission_first;
            $model->share_commission_second = $this->share_commission_second;
            $model->share_commission_third = $this->share_commission_third;
            $model->poster = \Yii::$app->serializer->encode($this->poster);
            $res = $model->save();

            $form = new GoodsEditForm();
            $form->goods = $this->goods;
            $form->is_goods_edit = 1;
            $form->is_share = $this->is_share;
            $form->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($model));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "保存成功"
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }

    }
}