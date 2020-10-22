<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCash;

class CashForm extends Model
{
    public $status;
    public $keyword;
    public $keyword_nickname;
    public $transfer_status;

    public function rules()
    {
        return [
            [['status','transfer_status'], 'integer'],
            [['keyword'], 'string'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = MchCash::find()->alias('mc')->where([
            'mc.mall_id' => \Yii::$app->mall->id,
            'mc.is_delete' => 0,
        ])->with('mch.store', 'mch.user.userInfo');


        if ($this->status >= 0) {
            $query->andWhere(['mc.status' => $this->status]);
        }

        if (isset($this->transfer_status)) {
            $query->andWhere(['mc.transfer_status' => $this->transfer_status]);
        }

        if ($this->keyword) {
            $query->andWhere(['like', 'mc.order_no', $this->keyword]);
        }

        if ($this->keyword_nickname) {
            $mall_id = \Yii::$app->mall->id;
            $subQuery = User::find()->alias('u')->select('id')->where(['like','nickname',$this->keyword_nickname])
                ->andWhere(['u.mall_id' => \Yii::$app->mall->id]);
            $query = $query->leftJoin(['m' => Mch::tableName()], "mc.mch_id = m.id AND m.mall_id = $mall_id")
                ->leftJoin(['mu'=> User::tableName()], "m.user_id = mu.id AND mu.mall_id = $mall_id")
                ->andWhere(['in','mu.id',$subQuery]);
        }

        $list = $query->page($pagination)
            ->orderBy(['mc.created_at' => SORT_DESC])
            ->asArray()
            ->all();

        foreach ($list as &$item) {
            $item['type_data'] = \Yii::$app->serializer->decode($item['type_data']);
        }
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => "请求成功",
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getCount()
    {
        $count = MchCash::find()->where([
            'AND',
            ['mall_id' => \Yii::$app->mall->id],
            ['is_delete' => 0],
            ['or', ['status' => 0], ['transfer_status' => 0,'status' => 1]]
        ])->count();

        return $count;
    }
}
