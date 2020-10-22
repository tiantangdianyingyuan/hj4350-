<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/15
 * Time: 14:47
 */

namespace app\plugins\bonus\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\bonus\forms\export\CashExport;
use app\plugins\bonus\models\BonusCash;

class CashListForm extends Model
{
    public $mall;

    public $page;
    public $limit;

    public $status;

    public $date_start;
    public $date_end;
    public $keyword;
    public $platform;
    public $search_type;

    public $fields;
    public $flag;

    public $user_id;

    public function rules()
    {
        return [
            [['status'], 'required'],
            [['page', 'limit', 'status', 'search_type'], 'integer'],
            [['date_start', 'date_end', 'keyword', 'status', 'platform'], 'trim'],
            [['fields'], 'safe'],
            [['keyword', 'platform', 'flag'], 'string'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $this->mall = \Yii::$app->mall;
        $pagination = null;

        $query = $this->where();

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new CashExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $list = $query->orderBy(['b.created_at' => SORT_DESC])
            ->page($pagination, $this->limit, $this->page)->all();
        $newList = [];

        /* @var BonusCash[] $list */
        foreach ($list as $item) {
            $serviceCharge = round($item->price * $item->service_charge / 100, 2);
            $extra = \Yii::$app->serializer->decode($item->extra);
            $newItem = [
                'id' => $item->id,
                'order_no' => $item->order_no,
                'pay_type' => $item->type,
                'type' => $item->type,
                'status' => $item->status,
                'status_text' => $item->getStatusText($item->status),
                'user' => [
                    'name' => $item->captain->name,
                    'mobile' => $item->captain->mobile,
                    'avatar' => $item->user->userInfo->avatar,
                    'nickname' => $item->user->nickname,
                    'platform' => 'wx',
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
                ],
                'remark' => $item->content
            ];
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
                'export_list' => (new CashExport())->fieldsList(),
            ]
        ];
    }

    public function where()
    {
        $query = BonusCash::find()
            ->alias('b')
            ->where(['b.mall_id' => $this->mall->id, 'b.is_delete' => 0])
            ->joinWith(['user u' => function ($query) {
                $query->select(['nickname', 'id', 'username', 'mobile', 'mall_id']);
                if ($this->keyword && $this->search_type == 1) {
                    $query->andWhere(['like', 'u.nickname', $this->keyword]);
                } elseif ($this->keyword && $this->search_type == 4) {
                    $query->andWhere(['u.id' => $this->keyword]);
                }
            }, 'user.userInfo',
                'captain c' => function ($query) {
                    if ($this->keyword && $this->search_type == 2) {
                        $query->andWhere([
                            'or',
                            ['like', 'c.name', $this->keyword],
                        ]);
                    }
                    if ($this->keyword && $this->search_type == 3) {
                        $query->andWhere([
                            'c.mobile' => $this->keyword
                        ]);
                    }
                }])
            ->keyword($this->status >= 0, ['b.status' => $this->status])
            ->keyword($this->user_id, ['b.user_id' => $this->user_id]);

        if ($this->date_start) {
            $query->andWhere(['>=', 'b.created_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'b.created_at', $this->date_end]);
        }

        return $query;
    }
}