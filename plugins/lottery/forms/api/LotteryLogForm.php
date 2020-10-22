<?php
namespace app\plugins\lottery\forms\api;

use app\core\response\ApiCode;
use app\models\Goods;
use app\models\Model;
use app\plugins\lottery\models\LotteryLog;

class LotteryLogForm extends Model
{
    public $type;
    public $lottery_id;

    public function rules()
    {
        return [
            [['type', 'lottery_id'], 'integer'],
        ];
    }

    public function search()
    {
        //type=1进行中 2中奖 3未中奖
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->type == 1) {
            $query = LotteryLog::find()->alias('ll')->where([
                'll.mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'll.status' => 1,
                'child_id' => 0
            ])->joinwith(['lottery l'=>function ($query) {
                $query->where([
                    'l.is_delete' => 0,
                    'l.status' => 1,
                ]);
            }])->orderBy('end_at ASC,id DESC');
        }

        if ($this->type == 2) {
            $query = LotteryLog::find()->where([
                'AND',
                ['mall_id' => \Yii::$app->mall->id],
                ['user_id' => \Yii::$app->user->id],
                ['in','status',[3,4]],
            ])
                ->with('lottery')
                ->groupBy('lottery_id')
                ->orderBy('id DESC');
        }
        if ($this->type == 3) {
            $childQuery = LotteryLog::find()->select('lottery_id')->where('l.user_id = user_id and l.lottery_id = lottery_id')->andWhere(['status'=>[3,4]]);
            $query = LotteryLog::find()->alias('l')->where([
                'AND',
                ['mall_id' => \Yii::$app->mall->id],
                ['user_id' => \Yii::$app->user->id],
                ['not in', 'lottery_id', $childQuery],
                ['status' => 2],
                ['child_id' => 0]//附加？
            ])
                ->with('lottery')
                ->groupBy('lottery_id')
                ->orderBy('id DESC');
        }

        $list = $query->with(['goods.goodsWarehouse', 'goods.attr'])
            ->page($pagination, 10)
            ->asArray()
            ->all();

        array_walk($list, function (&$v) {
            $attr = $v['goods']['attr'][0];
            $v['attr_list'] = (new Goods())->signToAttr($attr['sign_id'], $v['goods']['attr_groups']);
            $v['time'] = date('Y.m.d H:i 开奖', strtotime($v['lottery']['end_at']));
        });
        unset($v);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list
            ],
        ];
    }

    public function luckyCode()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $own = LotteryLog::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'lottery_id' => $this->lottery_id,
            'user_id' => \Yii::$app->user->id,
            'child_id' => 0,
        ])
            ->with('user.userInfo')
            ->asArray()
            ->one();

        $award = [];
        if ($own['status'] == 2) {
            $child = LotteryLog::find()->select('*,child_id as user_id')->where([
                'AND',
                ['mall_id' => \Yii::$app->mall->id],
                ['user_id' => \Yii::$app->user->id],
                ['lottery_id' => $this->lottery_id],
                ['in','status',[3,4]]
            ])->with('user.userInfo')
                ->asArray()
                ->one();
            $award = $child ?: [];
        } elseif ($own['status'] == 3 || $own['status'] == 4) {
            $award = $own;
        }

        $log_id = $award['id'] ?? 0;

        $query = LotteryLog::find()->alias('l')->where([
            'AND',
            ['l.mall_id' => \Yii::$app->mall->id],
            ['l.user_id' => \Yii::$app->user->id],
            ['l.lottery_id' => $own['lottery_id']],
            ['not', ['l.id' => $log_id]],
            // ['not', ['l.child_id' => 0]],
            ['not', ['l.status' => 0]],
            ['not', ['l.id' => $own['id']]],
        ]);

        $parent = $query->page($pagination, 9)
            ->with('childUser.userInfo')
            ->asArray()
            ->all();

        if ($award && ($award['lucky_code'] == $own['lucky_code'])) {
            $num = $pagination->totalCount;
        } else {
            $num = $pagination->totalCount + 1;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'own' => $own,
                'parent' => $parent,
                'award' => $award,
                'num' => $num,
            ]
        ];
    }
}
