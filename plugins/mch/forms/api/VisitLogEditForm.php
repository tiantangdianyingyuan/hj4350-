<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\api;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchVisitLog;

class VisitLogEditForm extends Model
{
    public $mch_id;

    public function rules()
    {
        return [
            [['mch_id'], 'required'],
            [['mch_id'], 'integer']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {

            $mch = Mch::findOne($this->mch_id);
            if (!$mch) {
                throw new \Exception('商户不存在');
            }

            if ($mch->user_id == \Yii::$app->user->id) {
                throw new \Exception('商户是用户自己！不记录访问日志');
            }

            /** @var MchVisitLog $model */
            $model = MchVisitLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'mch_id' => $this->mch_id,
            ])->one();

            if ($model) {
                $model->num = $model->num + 1;
            } else {
                $model = new MchVisitLog();
                $model->mall_id = \Yii::$app->mall->id;
                $model->user_id = \Yii::$app->user->id;
                $model->mch_id = $this->mch_id;
                $model->num = 1;
            }

            $res = $model->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($model));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
