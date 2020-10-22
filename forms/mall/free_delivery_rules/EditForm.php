<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 11:07
 */

namespace app\forms\mall\free_delivery_rules;

use app\core\response\ApiCode;
use app\models\FreeDeliveryRules;
use app\models\Model;

/**
 * @property FreeDeliveryRules $model
 */
class EditForm extends Model
{
    public $model;

    public $price;
    public $detail;
    public $name;
    public $type;
    public $status;

    public function rules()
    {
        return [
            [['type', 'status'], 'integer'],
            [['price', 'status'], 'default', 'value' => 0],
            ['price', 'number', 'min' => 0],
            ['detail', 'safe'],
            ['name', 'required'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->model->isNewRecord) {
            $this->model->is_delete = 0;
        }
        $conditionList = [];
        foreach ($this->detail as &$item) {
            if (isset($item['condition'])) {
                if (in_array($item['condition'], $conditionList)) {
                    return [
                        'code' => 1,
                        'msg' => '同一条规则下，包邮条件不能相同'
                    ];
                }
                $conditionList[] = $item['condition'];
                $item['condition'] = trim($item['condition']);
                if (!is_numeric($item['condition']) || $item['condition'] < 0 || $item['condition'] > 99999999) {
                    return [
                        'code' => 1,
                        'msg' => '包邮条件必须大于等于0,小于99999999'
                    ];
                }
                if (empty($this->type)) {
                    return [
                        'code' => 1,
                        'msg' => '请选择包邮类型'
                    ];
                }
                if (in_array($this->type, [2, 4])) {
                    $item['condition'] = (int)$item['condition'];
                }
            } else {
                return [
                    'code' => 1,
                    'msg' => '请设置包邮条件'
                ];
            }
        }
        unset($item);
        $this->model->detail = \Yii::$app->serializer->encode($this->detail);
        $this->model->price = $this->price;
        $this->model->name = $this->name;
        $this->model->type = $this->type;
        $this->model->status = $this->status;
        if ($this->model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($this->model);
        }
    }
}
