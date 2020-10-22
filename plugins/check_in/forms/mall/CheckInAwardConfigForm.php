<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/28
 * Time: 9:20
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\forms\mall;


use app\plugins\check_in\forms\Model;

class CheckInAwardConfigForm extends Model
{
    public $day;
    public $number;
    public $type;
    public $status;


    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['integral'] = ['day', 'number', 'type', 'status'];
        $scenarios['balance'] = ['day', 'number', 'type', 'status'];
        return $scenarios;
    }

    public function rules()
    {
        return [
            [['day', 'type', 'status', 'number'], 'required', 'on' => ['integral', 'balance']],
            [['day', 'status'], 'integer', 'on' => ['integral', 'balance']],
            [['type'], 'in', 'range' => ['integral', 'balance'], 'on' => ['integral', 'balance']],
            [['number'], 'integer', 'min' => 0, 'on' => 'integral'],
            [['number'], 'number', 'min' => 0, 'on' => 'balance']
        ];
    }

    public function attributeLabels()
    {
        return [
            'day' => '签到天数',
            'number' => '签到奖励数量',
            'type' => '签到奖励类型',
            'status' => '签到类型'
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            throw new \Exception($this->getErrorMsg($this));
        }
        return $this;
    }
}
