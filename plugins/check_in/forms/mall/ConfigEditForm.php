<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/26
 * Time: 17:06
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\forms\mall;


use app\core\response\ApiCode;
use app\plugins\check_in\forms\common\Common;
use app\plugins\check_in\forms\Model;

class ConfigEditForm extends Model
{
    public $status;
    public $is_remind;
    public $time;
    public $normal_type;
    public $normal;
    public $continue;
    public $total;
    public $rule;
    public $continue_type;

    public function rules()
    {
        $type = ['integral', 'balance'];
        return [
            [['status', 'is_remind', 'continue_type'], 'integer'],
            [['continue', 'total'], function ($attr) use ($type) {
                $dayArr = [];
                if (is_array($this->$attr)) {
                    foreach ($this->$attr as $item) {
                        if (in_array($item['day'], $dayArr)) {
                            $this->addError($attr, "{$this->getAttributeLabel($attr)}天数不能相同");
                        }
                        if (!in_array($item['type'], $type)) {
                            $this->addError($attr, "{$this->getAttributeLabel($attr)}奖励类型不合法");
                        }
                        if ($item['number'] < 0) {
                            $this->addError($attr, "{$this->getAttributeLabel($attr)}奖励数量必须大于0");
                        }
                        if ($item['type'] == 'integral') {
                            $item['number'] = round($item['number'], 2);
                            if (!is_numeric($item['number']) || strpos($item['number'], ".") !== false) {
                                $this->addError($attr, "{$this->getAttributeLabel($attr)}奖励类型为积分时，数量必须为整数");
                            }
                        }
                        $dayArr[] = $item['day'];
                    }
                }
            }],
            [['rule', 'time'], 'string'],
            [['normal_type'], 'in', 'range' => $type],
            [['time'], 'default', 'value' => '00:00:00'],
            [['normal'], 'number', 'min' => 0]
        ];
    }

    public function attributeLabels()
    {
        return [
            'status' => '签到是否开启',
            'is_remind' => '签到是否提醒',
            'normal' => '普通签到赠送数量',
            'normal_type' => '普通签到奖励类型',
            'continue_type' => '连续签到周期',
            'continue' => '连续签到设置',
            'total' => '累计签到设置',
            'rule' => '签到规则',
            'time' => '提醒时间'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            if ($this->normal_type == 'integral') {
                $this->normal = round($this->normal, 2);
                if (!is_numeric($this->normal) || strpos($this->normal, ".") !== false) {
                    throw new \Exception('普通签到奖励类型为积分时，奖励数量必须是整数');
                }
            }
            $common = Common::getCommon($this->mall);
            $config = $common->getConfig();
            $newList = [];
            $newList[] = [
                'number' => $this->normal,
                'day' => 1,
                'type' => $this->normal_type,
                'status' => 1,
            ];
            if ($this->continue) {
                foreach ($this->continue as $item) {
                    $newList[] = [
                        'number' => $item['number'],
                        'day' => $item['day'],
                        'type' => $item['type'],
                        'status' => 2,
                    ];
                }
            }
            if ($this->total) {
                foreach ($this->total as $item) {
                    $newList[] = [
                        'number' => $item['number'],
                        'day' => $item['day'],
                        'type' => $item['type'],
                        'status' => 3,
                    ];
                }
            }
            $common->addAwardConfig($newList);
            if ($config->isNewRecord || $this->time != $config->time) {
                $common->addRemindJob($common->getRemind($this->time));
            }
            if ($config->isNewRecord || $this->continue_type != $config->continue_type) {
                $continueTypeClass = $common->getContinueTypeClass($this->continue_type);
                $continueTypeClass->setJob();
            }
            $res = $common->addConfig($config, $this->attributes);

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }
}
