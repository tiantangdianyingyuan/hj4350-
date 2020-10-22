<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/25
 * Time: 14:38
 */

namespace app\forms\mall\share;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\Share;

class ContentForm extends Model
{
    public $content;
    public $id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['content'], 'trim'],
            [['content'], 'string'],
            [['id'], 'integer'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $share = Share::findOne(['id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);

        if (!$share) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '分销商不存在'
            ];
        }
        $share->content = $this->content;
        if ($share->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($share);
        }
    }
}
