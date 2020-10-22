<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/13
 * Time: 11:07
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\forms\api;


use app\core\response\ApiCode;
use app\plugins\check_in\forms\common\Common;

class UserForm extends ApiModel
{
    public $is_remind;

    public function rules()
    {
        return [
            [['is_remind'], 'integer']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $common = Common::getCommon($this->mall);
            $checkInUser = $common->getCheckInUser($this->user);
            $common->saveCheckInUser($checkInUser, $this->attributes);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
