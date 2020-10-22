<?php
/**
 * @link:http://www.zjhejiang.com/
 * @copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 *
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2018/12/7
 * Time: 14:48
 */

namespace app\forms\mall\postage_rules;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\PostageRules;

/**
 * @property PostageRules $model
 */
class PostageRulesEditForm extends Model
{
    public $name;
    public $type;
    public $detail;

    public $model;

    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            ['type', 'integer'],
            ['detail', 'safe']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if (empty($this->detail)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                ' msg' => '请填写运费规则'
            ];
        }

        foreach ($this->detail as &$item) {
            if (isset($item['first'])) {
                if (is_numeric($item['first'])) {
                } else {
                    return [
                        'code' => 1,
                        'msg' => '首件/首重必须是数字且不小于0'
                    ];
                }
            } else {
                $item['first'] = 0;
            }
            if (isset($item['firstPrice'])) {
                if (is_numeric($item['firstPrice']) && $item['firstPrice'] >= 0) {
                } else {
                    return [
                        'code' => 1,
                        'msg' => '运费必须是数字且不小于0'
                    ];
                }
            } else {
                $item['firstPrice'] = 0;
            }
            if (isset($item['second'])) {
                if (is_numeric($item['second'])) {
                } else {
                    return [
                        'code' => 1,
                        'msg' => '续件/续重必须是数字且不小于0'
                    ];
                }
            } else {
                $item['second'] = 0;
            }
            if (isset($item['secondPrice'])) {
                if (is_numeric($item['secondPrice']) && $item['secondPrice'] >= 0) {
                } else {
                    return [
                        'code' => 1,
                        'msg' => '运费必须是数字且不小于0'
                    ];
                }
            } else {
                $item['secondPrice'] = 0;
            }
        }

        $this->detail = \Yii::$app->serializer->encode($this->detail);
        $this->model->attributes = $this->attributes;
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