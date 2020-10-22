<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/11
 * Time: 16:49
 */

namespace app\plugins\wxapp\forms;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\wxapp\models\WxappJumpAppid;

class JumpAppidForm extends Model
{
    public $appid_list;

    public function rules()
    {
        return [
            ['appid_list', 'each', 'rule' => ['trim']],
            ['appid_list', 'each', 'rule' => ['string', 'max' => 64]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'appid_list' => 'appid',
        ];
    }

    public function getResponseData()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        WxappJumpAppid::deleteAll([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        foreach ($this->appid_list as $appid) {
            if ($appid) {
                $model = new WxappJumpAppid();
                $model->mall_id = \Yii::$app->mall->id;
                $model->appid = $appid;
                if (!$model->save()) {
                    return $this->getErrorResponse($model);
                }
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功。',
        ];
    }
}
