<?php
/**
 * Created by zjhj_mall_v4_gift
 * User: jack_guo
 * Date: 2019/10/17
 * Email: <657268722@qq.com>
 */

namespace app\plugins\gift\forms\api;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\gift\jobs\GiftOpenJob;
use app\plugins\gift\models\GiftOpenResult;
use app\plugins\gift\models\GiftUserOrder;
use yii\db\Exception;

class GiftJoinForm extends Model
{

    public $gift_id;
    public $token;
    public $queue_id;

    public function rules()
    {
        return [
            [['gift_id'], 'required'],
            [['gift_id', 'queue_id'], 'integer'],
            [['token'], 'string'],
        ];
    }

    public function join()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            $token = \Yii::$app->security->generateRandomString();
            $dateArr = [
                'gift_id' => $this->gift_id,
                'mall' => \Yii::$app->mall,
                'user' => \Yii::$app->user->identity,
                'appVersion' => \Yii::$app->appVersion,
                'token' => $token
            ];
            $class = new GiftOpenJob($dateArr);
            $queue_id = \Yii::$app->queue->delay(0)->push($class);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'queue_id' => $queue_id,
                    'token' => $token
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function joinStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            if (!$this->queue_id || !$this->token) {
                throw new Exception('缺失入参');
            }
            if (!\Yii::$app->queue->isDone($this->queue_id)) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'retry' => 1,
                    ],
                ];
            }
            /** @var GiftUserOrder $order */
            $order = GiftUserOrder::find()->where([
                'token' => $this->token,
                'is_delete' => 0,
                'user_id' => \Yii::$app->user->id,
            ])->one();
            if (!$order) {
                $result = GiftOpenResult::findOne([
                    'token' => $this->token,
                ]);
                if ($result) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => $result->data,
                    ];
                }
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '抢礼物失败。',
                ];
            }
            //成功，返回信息
            $form = new GiftForm();
            $form->gift_id = $this->gift_id;
            return $form->search();
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}