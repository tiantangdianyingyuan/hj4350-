<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/28
 * Time: 15:02
 */

namespace app\forms\common\share;


use app\forms\mall\export\ShareCashExport;
use app\models\Mall;
use app\models\Model;
use app\models\ShareCash;
use app\models\ShareSetting;
use app\models\User;

/**
 * @property Mall $mall
 */
class CommonShareCashList extends Model
{
    public $mall;

    public $page;
    public $limit;

    public $status;

    public $start_date;
    public $end_date;
    public $keyword;
    public $platform;
    public $user_id;
    public $fields;
    public $flag;
    public $type; // 数据获取来源 api--前端接口 mall--后端

    public function search()
    {
        $this->mall = \Yii::$app->mall;
        $pagination = null;

        $query = ShareCash::find()
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0])
            ->with(['user.userInfo'])
            ->keyword($this->status >= 0, ['status' => $this->status])
            ->keyword($this->user_id, ['user_id' => $this->user_id]);

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new ShareCashExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        if ($this->type == 'api') {
            $query->orderBy(['created_at' => SORT_DESC]);
        } else {
            $query->orderBy(['status' => SORT_ASC, 'created_at' => SORT_DESC]);
        }

        if ($this->keyword && empty($this->user_id)) {
            $subQuery = User::find()->select('id')->where(['like','nickname',$this->keyword])
                ->andWhere(['mall_id' => $this->mall]);
            $query = $query->andWhere(['in','user_id',$subQuery]);
        }

        $list = $query->page($pagination, $this->limit, $this->page)->all();
        $newList = [];

        /* @var ShareCash[] $list */
        foreach ($list as $item) {
            $serviceCharge = round($item->price * $item->service_charge / 100, 2);
            $extra = \Yii::$app->serializer->decode($item->extra);
            $newItem = [
                'id' => $item->id,
                'order_no' => $item->order_no,
                'pay_type' => ShareSetting::PAY_TYPE_LIST[$item->type],
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

        return [
            'list' => $newList,
            'pagination' => $pagination,
            'export_list' => (new ShareCashExport())->fieldsList(),
        ];
    }
}
