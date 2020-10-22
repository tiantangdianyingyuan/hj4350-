<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/10/31
 * Time: 16:27
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\order_form;


use app\core\response\ApiCode;
use app\forms\common\form\CommonForm;
use app\models\Form;
use app\models\Model;

class OrderFormUpdate extends Model
{
    public $id;
    public $status;
    public $is_default;
    public $is_delete;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'status', 'is_default', 'is_delete'], 'integer'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $commonForm = CommonForm::getInstance();
            $model = $commonForm->getDetail($this->id);
            $model->value = json_encode($model->value, JSON_UNESCAPED_UNICODE);
            $model->attributes = $this->attributes;
            if (!$model->save()) {
                return $this->getErrorResponse($model);
            }
            if ($model->is_default == 1) {
                Form::updateAll(['is_default' => 0], [
                    'AND',
                    ['mch_id' => \Yii::$app->getMchId()],
                    ['mall_id' => \Yii::$app->mall->id],
                    ['!=', 'id', $model->id]
                ]);
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '修改成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }
}
