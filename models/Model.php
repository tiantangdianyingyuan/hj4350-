<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 12:00
 */


namespace app\models;

use app\core\response\ApiCode;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class Model extends \yii\base\Model
{
    protected $sign = '';

    /**
     * ActiveRecord 数据验证，返回第一条错误验证
     * @param array $model
     * @return array
     */
    public function getErrorResponse($model = [])
    {
        if (!$model) {
            $model = $this;
        }

        $msg = isset($model->errors) ? current($model->errors)[0] : '数据异常！';

        return [
            'code' => ApiCode::CODE_ERROR,
            'msg' => $msg
        ];
    }

    /**
     * ActiveRecord 数据验证，返回第一条错误验证
     * @param array $model
     * @return string
     */
    public function getErrorMsg($model = null)
    {
        if (!$model) {
            $model = $this;
        }
        $msg = isset($model->errors) ? current($model->errors)[0] : '数据异常！';
        return $msg;
    }

    public function setSign($val)
    {
        $this->sign = $val;
        return $this;
    }
}
