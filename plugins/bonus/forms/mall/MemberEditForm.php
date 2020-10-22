<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/30
 * Time: 16:14
 */

namespace app\plugins\bonus\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\bonus\models\BonusMembers;

class MemberEditForm extends Model
{
    public $level;
    public $name;
    public $pic_url;
    public $bg_pic_url;
    public $update_condition;
    public $update_type;
    public $auto_update;
    public $rate;
    public $status;
    public $id;

    public $member;
    public $isNewRecord;

    public function rules()
    {
        return [
            [['name', 'level', 'rate', 'status',
                'auto_update'], 'required'],
            [['name',], 'string'],
            [['rate',], 'default', 'value' => 1],
            [['rate', 'update_condition', 'update_type',], 'number', 'min' => 0],
            [['rate',], 'number', 'max' => 100],
            [['id', 'level', 'status', 'auto_update'], 'integer'],
            [['auto_update'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'rights' => '等级权益',
            'update_type' => '升级条件类型',
            'update_condition' => '升级条件',
            'rate' => '分红比例',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->id) {
                $member = BonusMembers::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

                if (!$member) {
                    throw new \Exception('数据异常,该条数据不存在');
                }
            } else {
                $member = new BonusMembers();
            }

            $this->member = $member;
            $this->isNewRecord = $member->isNewRecord;

            $member->name = $this->name;
            $member->mall_id = \Yii::$app->mall->id;
            $member->level = $this->level;
            $member->auto_update = $this->auto_update;
            $member->update_type = $this->update_type ?: '0';
            $member->update_condition = $this->update_condition ?: '0';
            $member->rate = $this->rate;
            $member->status = $this->status;
            $res = $member->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($member));
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ]
            ];
        }
    }
}
