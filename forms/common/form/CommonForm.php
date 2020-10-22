<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/10/31
 * Time: 15:10
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\form;


use app\forms\common\CommonOption;
use app\models\Form;
use app\models\Mall;
use app\models\Model;
use app\models\Option;

/**
 * Class CommonForm
 * @package app\forms\common\form
 * @property Mall $mall
 */
class CommonForm extends Model
{
    private static $instance;
    public $mall;

    const FORM_DEFAULT = 1; // 默认
    const FORM_NOT_DEFAULT = 0; // 不默认
    const FORM_OPEN = 1; // 状态开启
    const FORM_CLOSE = 0; // 状态关闭

    public static function getInstance($mall = null)
    {
        if (!self::$instance) {
            self::$instance = new self();
            if (!$mall) {
                $mall = \Yii::$app->mall;
            }
            self::$instance->mall = $mall;
        }
        return self::$instance;
    }

    /**
     * @return null|array
     * 设置旧版的表单数据到新表中
     */
    public function setOldData()
    {
        $option = CommonOption::get(Option::NAME_ORDER_FORM, \Yii::$app->mall->id, Option::GROUP_APP);
        if (!$option) {
            return null;
        }
        $option = CommonOption::set(Option::NAME_ORDER_FORM, '', \Yii::$app->mall->id, Option::GROUP_APP);
        $model = new Form();
        $model->mall_id = $this->mall->id;
        $model->mch_id = 0;
        $model->is_delete = 0;
        $model->status = $option['status'];
        $model->is_default = CommonForm::FORM_DEFAULT;
        $model->name = $option['name'];
        $model->value = json_encode($option['value'], JSON_UNESCAPED_UNICODE);
        $model->save();
        return [$model];
    }

    /**
     * @param $id
     * @return Form|null
     * @throws \Exception
     */
    public function getDetail($id)
    {
        $form = Form::findOne([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'id' => $id
        ]);
        if (!$form) {
            throw new \Exception('内容不存在');
        }
        $form->value = json_decode($form->value, true);
        return $form;
    }
}
