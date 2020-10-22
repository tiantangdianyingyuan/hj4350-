<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\card;


use app\core\response\ApiCode;
use app\models\GoodsCards;
use app\models\Model;

class CardEditForm extends Model
{
    public $name;
    public $pic_url;
    public $description;
    public $id;
    public $expire_type;
    public $time;
    public $expire_day;
    public $total_count;
    public $number;
    public $app_share_title;
    public $app_share_pic;

    public function rules()
    {
        return [
            [['name', 'pic_url', 'description', 'expire_day', 'time', 'expire_type'], 'required'],
            [['pic_url', 'name', 'description', 'app_share_title', 'app_share_pic'], 'string'],
            [['id', 'expire_type', 'expire_day', 'total_count', 'number'], 'integer'],
            [['total_count'], 'default', 'value' => -1],
            [['expire_day'], 'integer', 'max' => 2000,],
        ];
    }

    public function attributeLabels()
    {
        return [
            'expire_day' => '有效天数',
            'number' => '核销总次数',
            'total_count' => '可发放数量',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->number < 1 || $this->number > 50) {
                throw new \Exception('核销总次数限制为：1~50');
            }

            if ($this->expire_type == 1 && ($this->expire_day < 1 || $this->expire_day > 9999999)) {
                throw new \Exception('有效天数限制为：1~999999');
            }

            if ($this->expire_type == 2 && strtotime($this->time[0]) < strtotime('1970-01-01') || strtotime($this->time[1]) > strtotime('2038-01-01')) {
                throw new \Exception('有效日期限制为:1970-01-01 ~ 2038-01-01');
            }

            if ($this->total_count != -1 && ($this->total_count < 1 || $this->total_count > 9999999)) {
                throw new \Exception('可发放数量限制为：1~999999');
            }

            if ($this->id) {
                $card = GoodsCards::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);
                if (!$card) {
                    throw new \Exception('数据异常,该条数据不存在');
                }
            } else {
                $card = new GoodsCards();
                $card->mall_id = \Yii::$app->mall->id;
                $card->is_allow_send = 0;
            }

            $card->name = $this->name;
            $card->expire_type = $this->expire_type;
            $card->expire_day = $this->expire_day;
            $card->begin_time = $this->time[0];
            $card->end_time = $this->time[1];
            $card->pic_url = $this->pic_url;
            $card->description = $this->description;
            $card->total_count = $this->total_count;
            $card->number = $this->number;
            $card->app_share_pic = $this->app_share_pic;
            $card->app_share_title = $this->app_share_title;
            $res = $card->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($card));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
