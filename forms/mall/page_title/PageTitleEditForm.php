<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\page_title;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class PageTitleEditForm extends Model
{
    public $data;

    public function save()
    {
        try {
            $this->checkData();
            $res = CommonOption::set(Option::NAME_PAGE_TITLE, $this->data, \Yii::$app->mall->id, Option::GROUP_APP);

            if (!$res) {
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

    // 检测数据
    public function checkData()
    {
        if (!$this->data && is_array($this->data)) {
            throw new \Exception('请检查信息是否填写完整');
        }

        foreach ($this->data as &$item) {
            if (!$item['new_name']) {
                $item['new_name'] = $item['name'];
            }
            if (!$item['new_name']) {
                throw new \Exception($item['name'] . '标题不能为空');
            }
        }
        unset($item);
    }
}
