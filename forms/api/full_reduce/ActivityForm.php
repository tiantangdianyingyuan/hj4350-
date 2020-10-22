<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/7/16
 * Time: 11:54
 */

namespace app\forms\api\full_reduce;

use app\core\response\ApiCode;
use app\forms\common\full_reduce\CommonActivity;
use app\models\Model;

class ActivityForm extends Model
{
    public function getActivity()
    {
        $info = CommonActivity::getActivityMarket();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => (object)$info,
        ];
    }
}
