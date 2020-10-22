<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/13
 * Time: 9:29
 */

namespace app\forms\mall\territorial_limitation;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class EditForm extends Model
{
    public $is_enable;
    public $detail;

    public function rules()
    {
        return [
            ['is_enable', 'integer'],
            ['detail', 'safe']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $data = [
            'is_enable' => $this->is_enable,
            'detail' => $this->detail
        ];
        $res = CommonOption::set(
            Option::NAME_TERRITORIAL_LIMITATION,
            $data,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            \Yii::$app->user->identity->mch_id
        );
        if ($res) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '保存失败'
            ];
        }
    }
}
