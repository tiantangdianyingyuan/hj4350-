<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\step\models\StepAd;

class AdEditForm extends Model
{
    public $unit_id;
    public $site;
    public $status;
    public $id;
    public $type;
    public $pic_url;
    public $video_url;
    public $award_num;
    public $award_type;
    public $award_coupons;
    public $award_limit_type;
    public $award_limit;

    public function rules()
    {
        return [
            [['status', 'site', 'unit_id'], 'required'],
            [['unit_id', 'type', 'pic_url', 'video_url'], 'string', 'max' => 255],
            [['pic_url', 'video_url', 'unit_id'], 'default', 'value' => ''],
            [['site', 'status', 'id', 'award_num', 'award_type','award_limit_type','award_limit'], 'integer'],
            [['award_coupons'], 'trim']
        ];
    }

    //GET
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = StepAd::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
        ]);
        empty($model) && $model = new StepAd();
        $model->attributes = $this->attributes;
        $model->reward_data = \yii\helpers\BaseJson::encode([
            'award_type' => $this->award_type,
            'award_num' => $this->award_num,
            'award_coupons' => $this->award_coupons ?: [],
            'award_limit_type' =>$this->award_limit_type,
            'award_limit' => $this->award_limit,
        ]);
        $model->mall_id = \Yii::$app->mall->id;
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
