<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api;

use app\core\currency\IntegralModel;
use app\core\response\ApiCode;
use app\models\Model;

class IntegralLogForm extends Model
{
    public $page;
    public $type;

    public function rules()
    {
        return [
            [['page', 'type'], 'integer'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = new IntegralModel();
        $model->user = \Yii::$app->user;
        $model->mall = \Yii::$app->mall;
        $model->type = $this->type ?: 1;
        $res = $model->getLogListByUser();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $res['list'],
                'pagination' => $res['pagination'],
            ]
        ];
    }
}
