<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api;

use app\core\response\ApiCode;
use app\forms\mall\recharge\RechargeSettingForm;
use app\models\BalanceLog;
use app\models\Model;

class BalanceForm extends Model
{
    public $limit;
    public $type;
    public $date;
    public $id;

    public function rules()
    {
        return [
            [['limit', 'type', 'id'], 'integer',],
            [['limit',], 'default', 'value' => 20],
            [['date'], 'string']
        ];
    }

    public function getIndex()
    {
        $option = (new RechargeSettingForm())->setting();
        if (!$option['bj_pic_url']) {
            $option['bj_pic_url'] = [];
            $option['bj_pic_url']['url'] = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/icon-balance-bg.png';
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $option,
                'balance' => !\Yii::$app->user->isGuest ? \Yii::$app->user->identity->userInfo->balance : 0
            ]
        ];
    }

    public function getLogs()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = BalanceLog::find();

        if ((int)$this->type === 1 || (int)$this->type === 2) {
            $query->andWhere(['type' => $this->type]);
        }

        if (!$this->date) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '请传入date参数'
            ];
        }
        if ($this->date) {
            $dateArr = $this->getTheMonth($this->date);
            $query->andWhere(['>=', 'created_at', $dateArr[0] . ' 00:00:00']);
            $query->andWhere(['<=', 'created_at', $dateArr[1] . ' 23:59:59']);
        }

        if (!\Yii::$app->user->isGuest) {
            $list = $query->andWhere(['user_id' => \Yii::$app->user->id])
                ->page($pagination, $this->limit)
                ->orderBy('created_at DESC')
                ->asArray()->all();
        } else {
            $list = [];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list
            ]
        ];
    }

    /**
     * 获取月份的第一天和最后一天
     * @param $date
     * @return array
     */
    public function getTheMonth($date)
    {
        $firstDay = date('Y-m-01', strtotime($date));
        $lastDay = date('Y-m-d', strtotime("$firstDay +1 month -1 day"));
        return array($firstDay, $lastDay);
    }

    public function getLogDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if (!$this->id) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '请传入日志ID'
            ];
        }

        $detail = BalanceLog::find()->where([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => \Yii::$app->user->id
        ])->one();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $detail
            ]
        ];
    }
}
