<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\poster;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\models\Option;

class PosterEditForm extends Model
{
    public $data;

    public function save()
    {
        try {
            $this->checkData();
            $newData = [];
            foreach ($this->data as $key => $datum) {
                $newData[$key] = (new CommonOptionP())->saveEnd($datum);
            }

            $res = CommonOption::set(Option::NAME_POSTER, $newData, \Yii::$app->mall->id, Option::GROUP_APP);

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
            throw new \Exception('请检查信息是否填写完整x01');
        }
    }
}
