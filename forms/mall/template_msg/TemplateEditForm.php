<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\template_msg;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class TemplateEditForm extends Model
{
    public $data;

    public function save()
    {
        try {
            $this->checkedData();

            $list = CommonOption::get(
                Option::NAME_WX_TEMPLATE,
                \Yii::$app->mall->id,
                Option::GROUP_APP
            );
            $newList = [];
            if ($list) {
                foreach ($list as $item) {
                    $newList[] = $item;
                }
            }
            $newList[] = $this->data;

            $option = CommonOption::set(
                Option::NAME_WX_TEMPLATE,
                $newList,
                \Yii::$app->mall->id,
                Option::GROUP_APP
            );

            if (!$option) {
                throw new \Exception('保存失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function destroyTemplate()
    {
        try {
            $option = CommonOption::set(
                Option::NAME_WX_TEMPLATE,
                $this->data,
                \Yii::$app->mall->id,
                Option::GROUP_APP
            );

            if (!$option) {
                throw new \Exception('删除失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function checkedData()
    {
        if (!$this->data || !is_array($this->data)) {
            throw new \Exception('请检查信息是否填写完整x01');
        }

        foreach ($this->data as $k => $item) {
            if (!$item && $k != 'link_url') {
                throw new \Exception('请检查信息是否填写完整x02');
            }

            if (!isset($this->data['fields']) || !is_array($this->data['fields']) || !count($this->data['fields'])) {
                throw new \Exception('请完善模板字段信息x01');
            }

            foreach ($this->data['fields'] as $item2) {
                if (!$item2['field_name']) {
                    throw new \Exception('请完善模板字段信息x02');
                }
            }
        }
    }
}
