<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/4/2
 * Time: 13:44
 */

namespace app\forms\api\finance;

use app\core\response\ApiCode;
use app\models\Finance;
use app\models\Mall;
use app\models\Model;
use app\models\User;

/**
 * Class FinanceListForm
 * @package app\forms\api\finance
 * @property Mall $mall
 * @property User $user
 */
class FinanceListForm extends Model
{
    public $mall;
    public $user;

    public $page;
    public $limit;
    public $model;

    public $status;

    public function rules()
    {
        return [
            [['model', 'status'], 'required'],
            [['page', 'limit'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $this->mall = \Yii::$app->mall;
        $this->user = \Yii::$app->user->identity;
        $pagination = null;

        $query = Finance::find()
            ->where([
                'mall_id' => $this->mall->id, 'is_delete' => 0, 'model' => $this->model, 'user_id' => $this->user->id
            ])
            ->with(['user.userInfo'])
            ->keyword($this->status >= 0, ['status' => $this->status])
            ->orderBy(['created_at' => SORT_DESC]);

        $list = $query->page($pagination, $this->limit, $this->page)->all();
        $newList = [];

        foreach ($list as $item) {
            /* @var Finance $item */
            $serviceCharge = round($item->price * $item->service_charge / 100, 2);
            $extra = \Yii::$app->serializer->decode($item->extra);
            $newItem = [
                'id' => $item->id,
                'order_no' => $item->order_no,
                'pay_type' => Finance::PAY_TYPE_LIST[$item->type],
                'type' => $item->type,
                'status' => $item->status,
                'status_text' => $item->getStatusText($item->status),
                'user' => [
                    'avatar' => $item->user->userInfo->avatar,
                    'nickname' => $item->user->nickname,
                    'platform' => $item->user->userInfo->platform,
                ],
                'cash' => [
                    'price' => round($item->price, 2),
                    'service_charge' => $serviceCharge,
                    'actual_price' => round($item->price - $serviceCharge, 2)
                ],
                'extra' => [
                    'name' => $extra['name'] ? $extra['name'] : '',
                    'mobile' => $extra['mobile'] ? $extra['mobile'] : '',
                    'bank_name' => $extra['bank_name'] ? $extra['bank_name'] : ''
                ],
                'time' => [
                    'created_at' => $item->created_at,
                    'apply_at' => isset($extra['apply_at']) ? $extra['apply_at'] : '',
                    'remittance_at' => isset($extra['remittance_at']) ? $extra['remittance_at'] : '',
                    'reject_at' => isset($extra['reject_at']) ? $extra['reject_at'] : '',
                ],
                'content' => [
                    'apply_content' => isset($extra['apply_content']) ? $extra['apply_content'] : '',
                    'remittance_content' => isset($extra['remittance_content']) ? $extra['remittance_content'] : '',
                    'reject_content' => isset($extra['reject_content']) ? $extra['reject_content'] : '',
                ]
            ];
            $newList[] = $newItem;
        }

        $cashList = $newList;
        $newList = [];
        foreach ($cashList as $item) {
            $time = date('Y-m-d', strtotime($item['time']['created_at']));
            if (isset($newList[$time])) {
                $newList[$time]['list'][] = $item;
            } else {
                $newItem = [
                    'date' => date('m月d日', strtotime($time)),
                    'list' => [
                        $item
                    ]
                ];
                $newList[$time] = $newItem;
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => 'success',
            'data' => [
                'list' => $newList
            ]
        ];
    }
}
