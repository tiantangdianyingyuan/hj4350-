<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\copyright;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class CopyrightEditForm extends Model
{
    public $data;
    public $mall_id;

    public function rules()
    {
        return [
            [['data'], 'safe'],
            [['mall_id'], 'integer']
        ];
    }

    public function save()
    {
        try {
            if (!$this->data) {
                throw new \Exception('请输入form参数数据');
            }
            $mallId = $this->mall_id ? $this->mall_id : \Yii::$app->mall->id;
            $res = CommonOption::set(Option::NAME_COPYRIGHT, $this->data, $mallId, Option::GROUP_APP);

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
}
