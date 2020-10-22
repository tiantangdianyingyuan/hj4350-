<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\home_block;


use app\core\response\ApiCode;
use app\models\HomeBlock;
use app\models\Model;

class HomeBlockEditForm extends Model
{
    public $name;
    public $type;
    public $value;
    public $id;

    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['name',], 'string'],
            [['type', 'id'], 'integer'],
            [['value'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '名称',
            'type' => '样式类型',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if (!$this->type) {
                throw new \Exception('请选择魔方样式');
            }

            if ($this->id) {
                $homeBlock = HomeBlock::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

                if (!$homeBlock) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '数据异常,该条数据不存在',
                    ];
                }
            } else {
                $homeBlock = new HomeBlock();
            }

            $homeBlock->name = $this->name;
            $homeBlock->type = $this->type;
            $homeBlock->mall_id = \Yii::$app->mall->id;
            $homeBlock->value = json_encode($this->value);
            $res = $homeBlock->save();

            if (!$res) {
                throw new \Exception('保存失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
