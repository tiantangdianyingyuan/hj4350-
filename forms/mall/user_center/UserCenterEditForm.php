<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\user_center;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class UserCenterEditForm extends Model
{
    public $data;

    public function save()
    {
        try {
            $this->checkData();
            $res = CommonOption::set(Option::NAME_USER_CENTER, $this->data, \Yii::$app->mall->id, Option::GROUP_APP);

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

    public function checkData()
    {
        if (!isset($this->data['menus'])) {
            $this->data['menus'] = [];
        }
        if (isset($this->data['account_bar'])) {
            foreach ($this->data['account_bar'] as $index => $item) {
                if (is_array($item) && mb_strlen($item['text']) > 4) {
                    throw new \Exception('我的账户--文字说明不能大于4个字');
                }
            }
        }
    }

    public function reset()
    {
        $userCenterDefault = (new UserCenterForm())->getDefault();
        $this->data = $userCenterDefault;
        return $this->save();
    }
}
