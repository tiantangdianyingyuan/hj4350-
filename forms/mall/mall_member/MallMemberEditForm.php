<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\mall_member;


use app\core\response\ApiCode;
use app\models\MallMembers;
use app\models\MallMemberRights;
use app\models\Model;

class MallMemberEditForm extends Model
{
    public $level;
    public $name;
    public $pic_url;
    public $bg_pic_url;
    public $money;
    public $auto_update;
    public $discount;
    public $status;
    public $is_purchase;
    public $price;
    public $rights;
    public $rules;
    public $id;

    public $member;
    public $isNewRecord;

    public function rules()
    {
        return [
            [['name', 'pic_url', 'bg_pic_url', 'level', 'discount', 'status', 'is_purchase',
                'auto_update'], 'required'],
            [['pic_url', 'bg_pic_url', 'name', 'money', 'discount', 'price',], 'string'],
            [['id', 'level', 'status', 'is_purchase', 'auto_update'], 'integer'],
            [['rights', 'rules'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'rights' => '会员权益',
            'price' => '购买会员价格',
            'money' => '会员自动升级消费金额'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->discount < 0.1 || $this->discount > 10) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '会员折扣请输入 0.1 ~ 10之间的值'
            ];
        }
        if ($this->auto_update && !$this->money) {
            throw new \Exception('请填写会员自动升级金额');
        }

        if ($this->is_purchase && !$this->price) {
            throw new \Exception('请填写会员购买金额');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->id) {
                $member = MallMembers::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

                if (!$member) {
                    throw new \Exception('数据异常,该条数据不存在');
                }
            } else {
                $member = new MallMembers();
            }

            $this->member = $member;
            $this->isNewRecord = $member->isNewRecord;

            $member->name = $this->name;
            $member->mall_id = \Yii::$app->mall->id;
            $member->pic_url = $this->pic_url;
            $member->bg_pic_url = $this->bg_pic_url;
            $member->level = $this->level;
            $member->auto_update = $this->auto_update;
            $member->money = $this->money ?: 0;
            $member->discount = $this->discount;
            $member->status = $this->status;
            $member->is_purchase = $this->is_purchase;
            $member->price = $this->price ?: 0;
            $member->rules = $this->rules;
            $res = $member->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($member));
            }
            $this->setRights();

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

    /**
     * 会员权益
     */
    private function setRights()
    {
        if (!$this->isNewRecord) {
            $res = MallMemberRights::updateAll([
                'is_delete' => 1,
            ], [
                'member_id' => $this->member->id
            ]);
        }

        if ($this->rights) {
            foreach ($this->rights as $item) {
                // 检测会员权益数据
                if (!$item['title']) {
                    throw new \Exception('请完善会员权益标题');
                }
                if (!$item['pic_url']) {
                    throw new \Exception('请添加会员权益图标');
                }
                if (!$item['content']) {
                    throw new \Exception('请完善会员权益内容');
                }
            }
        }

        if ($this->rights) {
            $attributes = [];
            foreach ($this->rights as $k => $item) {
                $mallMemberRights = MallMemberRights::findOne([
                    'id' => $item['id']
                ]);
                if ($mallMemberRights) {
                    $mallMemberRights->is_delete = 0;
                    $mallMemberRights->title = $item['title'];
                    $mallMemberRights->content = $item['content'];
                    $mallMemberRights->pic_url = $item['pic_url'];
                    $res = $mallMemberRights->save();

                    if (!$res) {
                        throw new \Exception($this->getErrorMsg($mallMemberRights));
                    }
                } else {
                    $attributes[] = [
                        $this->member->id, $item['title'], $item['content'], $item['pic_url']
                    ];
                }
            }
            $query = \Yii::$app->db->createCommand();
            $res = $query->batchInsert(MallMemberRights::tableName(), [
                'member_id', 'title', 'content', 'pic_url'
            ], $attributes)
                ->execute();

            if ($res != count($attributes)) {
                throw new \Exception('保存失败, 会员权益数据异常');
            }
        }
    }
}
