<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\order_form;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\common\form\CommonForm;
use app\models\Form;
use app\models\Model;
use app\models\Option;
use yii\helpers\ArrayHelper;

class OrderFormForm extends Model
{
    public $keyword;
    public $page;
    public $id;

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['keyword'], 'string'],
            [['page'], 'integer']
        ];
    }

    public function getDetail()
    {
        $default = $this->getDefault();
        try {
            $commonForm = CommonForm::getInstance();
            $model = $commonForm->getDetail($this->id);
            $model = [
                'id' => $model->id,
                'name' => $model->name,
                'value' => $model->value,
                'status' => $model->status,
            ];
            if ($model['value']) {
                foreach ($model['value'] as &$item) {
                    $item['is_required'] = (int)$item['is_required'];
                }
                unset($item);
            } else {
                $model = $default;
            }
        } catch (\Exception $exception) {
            $model = $default;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $model,
            ]
        ];
    }

    public function getDefault()
    {
        return [
            'name' => '',
            'status' => 0,
            'value' => [],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $list = Form::find()->where([
            'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'mch_id' => \Yii::$app->getMchId(),
        ])->keyword($this->keyword, ['like', 'name', $this->keyword])
            ->page($pagination, 20, $this->page)
            ->select('id,status,name,is_default,is_delete')->all();
        if ($this->page == 1 && (!$list || empty($list))) {
            $commonForm = CommonForm::getInstance();
            $list = $commonForm->setOldData();
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }

    public function getAllList()
    {
        $list = Form::find()->where([
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->getMchId(),
            'status' => 1
        ])->select('id,name')->all();
        if ($this->page == 1 && (!$list || empty($list))) {
            $commonForm = CommonForm::getInstance();
            $list = $commonForm->setOldData();
        }
        array_unshift($list, [
            'id' => 0,
            'name' => '默认表单'
        ]);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list,
            ]
        ];
    }
}
