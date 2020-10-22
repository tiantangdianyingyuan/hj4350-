<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\diy\forms\common\CommonTemplate;
use app\plugins\diy\models\DiyTemplate;


class ModuleEditForm extends Model
{
    public $id;
    public $name;
    public $data;

    /** 'module' */

    public function rules()
    {
        return [
            [['name', 'data'], 'required'],
            [['id'], 'integer'],
            [['name', 'data'], 'string'],
            [['data'], dataValidator::className()],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '模块名称',
            'data' => '模块内容',
            'type' => '类型',
        ];
    }

    public function post()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $common = CommonTemplate::getCommon();
            $template = $common->getTemplate($this->id);
            if (!$template) {
                $template = new DiyTemplate();
                $template->type = DiyTemplate::TYPE_MODULE;
                $template->is_delete = 0;
                $template->mall_id = \Yii::$app->mall->id;
            }
            $template->name = $this->name;
            $template->data = $this->data;
            if (!$template->save()) {
                throw new \Exception('保存失败');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
                'data' => [
                    'id' => $template->id
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $e->getMessage(),
            ];
        }
    }
}