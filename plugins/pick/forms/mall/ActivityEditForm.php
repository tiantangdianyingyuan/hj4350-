<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/2/12
 * Time: 15:57
 */

namespace app\plugins\pick\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\pick\forms\common\CommonForm;
use app\plugins\pick\models\PickActivity;
use app\plugins\pick\models\PickGoods;
use yii\helpers\ArrayHelper;

class ActivityEditForm extends Model
{
    public $id;
    public $keyword;
    public $status;
    public $title;
    public $start_at;
    public $end_at;
    public $is_area_limit;
    public $area_limit;
    public $rule_num;
    public $rule_price;

    public function rules()
    {
        return [
            [['title', 'start_at', 'end_at', 'rule_num', 'rule_price'], 'required'],
            [['id', 'status', 'is_area_limit', 'rule_num'], 'integer'],
            [['keyword'], 'default', 'value' => ''],
            [['is_area_limit'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => PickActivity::ACTIVITY_UP],
            [['start_at', 'end_at', 'title', 'area_limit'], 'string'],
            [['rule_num'], 'integer', 'min' => 2, 'max' => 999999999, 'tooSmall' => '{attribute}必须大于1'],
            [['rule_price'], 'number', 'min' => 0, 'max' => 999999999],
            [['area_limit',], 'default', 'value' => ''],
            [['rule_price'], 'number'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '活动名称',
            'start_at' => '活动开始时间',
            'end_at' => '活动结束时间',
            'rule_price' => '组合方案价格',
            'rule_num' => '组合方案件数',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (price_format($this->rule_price) == '0.00') {
                throw new \Exception('组合方案价格必须大于0');
            }

            if ($this->id) {
                $pickActivity = PickActivity::find()->where([
                    'id' => $this->id,
                    'is_delete' => 0,
                    'mall_id' => \Yii::$app->mall->id
                ])->one();

                if (empty($pickActivity)) {
                    throw new \Exception('活动不存在');
                }
            } else {
                $pickActivity = new PickActivity();
                $pickActivity->mall_id = \Yii::$app->mall->id;
            }

            $pickActivity->status = $this->status;
            $pickActivity->title = $this->title;
            $pickActivity->start_at = $this->start_at;
            $pickActivity->end_at = $this->end_at;
            $pickActivity->rule_num = $this->rule_num;
            $pickActivity->rule_price = $this->rule_price;
            $pickActivity->is_area_limit = $this->is_area_limit;
            $pickActivity->area_limit = $this->area_limit;
            $res = $pickActivity->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($pickActivity));
            }

            $this->id = $pickActivity->id;
            $this->checkPick();

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    /**
     * 检测是活动时间是否冲突
     * @throws \Exception
     */
    public function checkPick()
    {
        $check = PickActivity::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'status' => PickActivity::ACTIVITY_UP,
            'is_delete' => 0,
        ])
            ->andWhere([
                '>=',
                'end_at',
                mysql_timestamp()
            ])
            ->andWhere([
                'or',
                ['between', 'start_at', $this->start_at, $this->end_at],
                ['between', 'end_at', $this->start_at, $this->end_at],
                [
                    'and',
                    [
                        '<=',
                        'start_at',
                        $this->start_at
                    ],
                    [
                        '>=',
                        'end_at',
                        $this->end_at
                    ]
                ]
            ])
            ->keyword($this->id, ['!=', 'id', $this->id])->one();
        if ($check) {
            throw new \Exception('该时间段已有活动,请修改活动时间日期');
        }
    }
}
