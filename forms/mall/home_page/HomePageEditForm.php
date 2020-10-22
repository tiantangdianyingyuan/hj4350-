<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\home_page;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class HomePageEditForm extends Model
{
    public $data;

    public function save()
    {
        try {
            $this->checkData();
            $option = CommonOption::set(Option::NAME_HOME_PAGE, $this->data, \Yii::$app->mall->id, Option::GROUP_APP);

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

    public function checkData()
    {
        if (!$this->data) {
            throw new \Exception('首页布局不能为空,至少添加一个布局');
        }
    }
}
