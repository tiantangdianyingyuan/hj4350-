<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/10/19
 * Time: 14:17
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\share;


use app\core\response\ApiCode;
use app\forms\common\share\CommonShareLevel;
use app\models\Model;
use app\models\ShareLevel;

class LevelEditForm extends Model
{
    public $id;
    public $level;
    public $name;
    public $condition_type;
    public $condition;
    public $price_type;
    public $first;
    public $second;
    public $third;
    public $status;
    public $is_auto_level;
    public $rule;

    public function rules()
    {
        return [
            [['level', 'name', 'condition_type', 'condition', 'price_type', 'first', 'status', 'rule'], 'required'],
            [['level', 'name', 'condition_type', 'condition', 'price_type', 'first', 'status', 'rule'], 'trim'],
            [['level', 'condition_type', 'price_type', 'status', 'id', 'is_auto_level'], 'integer'],
            [['name'], 'string'],
            [['condition', 'first', 'second', 'third'], 'number', 'min' => 0],
            [['condition_type', 'price_type', 'is_auto_level'], 'default', 'value' => 1],
            [[ 'status'], 'default', 'value' => 0],
            [[ 'rule'], 'string', 'max' => 80],
        ];
    }

    public function attributeLabels()
    {
        return [
            'level' => '分销商等级',
            'name' => '分销商等级名称',
            'first' => '一级分销佣金数（元）',
            'second' => '二级分销佣金数（元）',
            'third' => '三级分销佣金数（元）',
            'rule' => '等级说明',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if ($this->condition_type == 1) {
                if (!is_numeric($this->condition) || strpos($this->condition, ".") !== false) {
                    throw new \Exception('下线人数必须是整数');
                }
                $this->condition = intval($this->condition);
            }
            if ($this->price_type == 1) {
                if ($this->first > 100 || $this->second > 100 || $this->third > 100) {
                    throw new \Exception('分销佣金百分比不能大于100%');
                }
            }
            $commonShareLevel = CommonShareLevel::getInstance();
            $shareLevel = $commonShareLevel->getDetail($this->id);
            if (!$shareLevel) {
                $shareLevel = new ShareLevel();
                $shareLevel->is_delete = 0;
                $shareLevel->mall_id = \Yii::$app->mall->id;
            }
            $shareLevel->attributes = $this->attributes;
            if (!$shareLevel->save()) {
                throw new \Exception($this->getErrorMsg($shareLevel));
            } else {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功'
                ];
            }
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
