<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/11/8
 * Email: <657268722@qq.com>
 */

namespace app\plugins\gift\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\gift\models\GiftLog;

class GiftDeleteForm extends Model
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
        ];
    }

    public function del()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            $model = GiftLog::findOne($this->id);
            if ($model->is_delete == 1) {
                throw new \Exception('该礼物已被删除');
            }
            $model->is_delete = 1;
            if (!$model->save()) {
                throw new \Exception($model->errors[0]);
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '礼物删除成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }
}