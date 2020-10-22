<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/3/19
 * Time: 9:32
 */

namespace app\forms\mall\finance;

use app\core\Pagination;
use app\forms\mall\export\FinanceCashExport;
use app\models\Finance;
use app\models\Model;
use app\models\ShareCash;
use app\models\ShareSetting;
use app\models\Store;
use app\models\User;
use app\models\UserInfo;
use app\plugins\mch\models\MchCash;
use yii\db\Expression;
use yii\db\Query;

class FinanceForm extends Model
{
    public $mall;

    public $page;
    public $limit = 20;

    public $status;

    public $date_start;
    public $date_end;
    public $keyword;
    public $platform;
    public $user_id;
    public $fields;
    public $flag;
    public $type = 'api'; // 数据获取来源 api--前端接口 mall--后端
    public $model_type;

    public function init()
    {
        $this->mall = \Yii::$app->mall;
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function rules()
    {
        return [
            [['page', 'limit', 'status', 'user_id'], 'integer'],
            [['fields'], 'safe'],
            [['flag', 'model_type', 'type'], 'string'],
            [['keyword', 'date_start', 'date_end',], 'trim'],
            [['status'], 'default', 'value' => -1]
        ];
    }

    public function getPermission()
    {
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);

        $list = [];
        if (in_array('share', $permission)) {
            array_push($list, [
                'name' => '分销',
                'key' => 'share',
                'class' => 'app\\models\\ShareCash',
                'user_class' => 'app\\models\\Share',
                'user_alias' => 'share_user'
            ]);
        }
        foreach ($permission as $item) {
            try {
                $plugin = \Yii::$app->plugin->getPlugin($item);
                if (!method_exists($plugin, 'getCashConfig')) {
                    continue;
                }
                $list[] = $plugin->getCashConfig();
            } catch (\Exception $exception) {
                continue;
            }
        }

        return $list;
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $pagination = null;

        $query = $this->getQuery();
        if (empty($query)) {
            return [
                'list' => [],
                'pagination' => new Pagination(),
                'export_list' => (new FinanceCashExport())->fieldsList(),
            ];
        }
        $count = $query->count();
        if ($this->page) {
            $currentPage = $this->page - 1;
        } else {
            $currentPage = \Yii::$app->request->get('page', 1) - 1;
        }

        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $currentPage]);
        $tempQuery = (new Query())
            ->select(['q.*', 'ui.avatar', 'ui.platform', 'u.nickname'])
            ->from(['q' => $query])
            ->leftjoin(
                ["u" => User::tableName()],
                "u.id = q.user_id"
            )
            ->leftjoin(
                ['ui' => UserInfo::tableName()],
                "ui.user_id = q.user_id"
            );

        if ($this->keyword && empty($this->user_id)) {
            $subQuery = User::find()->select('id')->where(['like', 'nickname', $this->keyword])
                ->andWhere(['mall_id' => $this->mall->id]);
            $tempQuery->andWhere(
                [
                   'or',
                   ['in', "q.user_id", $subQuery],
                   ['like', "q.name", $this->keyword],
                ]
            );
        }

        if ($this->flag == "EXPORT") {
            $new_query = clone $tempQuery;
            $exp = new FinanceCashExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $newQuery = $tempQuery->limit($this->limit)->offset($pagination->offset);

        if ($this->type == 'api') {
            $newQuery->orderBy(['created_at' => SORT_DESC]);
        } else {
            $newQuery->orderBy(['status' => SORT_ASC, 'created_at' => SORT_DESC]);
        }

        $list = $newQuery->all();
        $newList = [];
        $permission = $this->getPermission();
        $cashType = array_reduce($permission, function ($res, $model) {
            $res[$model['key']] = $model['name'] . '提现';
            return $res;
        }, []);
        /* @var ShareCash[] $list */
        foreach ($list as $item) {
            if ($item['model'] == 'mch') {
                $serviceCharge = 0;
                $extra = \Yii::$app->serializer->decode($item['extra']);
                $extra['name'] = $extra['nickname'] ?? '';
                $extra['mobile'] = $extra['account'] ?? '';
                $extra['bank_name'] = $extra['bank_name'] ?? '';
                $extra['remittance_at'] = $extra['remittance_at'] ?? '';
                $extra['remittance_content'] = $extra['remittance_content'] ?? '';
                $extra['apply_at'] = $extra['apply_at'] ?? '';
                $extra['apply_content'] = $extra['apply_content'] ?? '';
                $extra['reject_at'] = $extra['reject_at'] ?? '';
                $extra['reject_content'] = $extra['reject_content'] ?? '';
                if ($item['type'] == 'wx') {
                    $item['type'] = 'wechat';
                }
                $item['status'] = $this->parseMchStatus($item);
                $mchCash = MchCash::findOne($item['id']);
                $store = Store::findOne(['mch_id' => $mchCash->mch_id]);
                $extra['shop_name'] = $store->name;
            } else {
                $serviceCharge = round($item['price'] * $item['service_charge'] / 100, 2);
                $extra = \Yii::$app->serializer->decode($item['extra']);
            }

            $cashTypeText = isset($cashType[$item['model']]) ? $cashType[$item['model']] : '未知状态' . $item['model'];
            $newItem = [
                'id' => $item['id'],
                'order_no' => $item['order_no'],
                'pay_type' => ShareSetting::PAY_TYPE_LIST[$item['type']],
                'type' => $item['type'],
                'status' => $item['status'],
                'user' => [
                    'name' => $item['name'],
                    'phone' => $item['phone'],
                    'avatar' => $item['avatar'],
                    'nickname' => $item['nickname'],
                    'platform' => $item['platform'],
                ],
                'cash' => [
                    'price' => round($item['price'], 2),
                    'service_charge' => $serviceCharge,
                    'actual_price' => round($item['price'] - $serviceCharge, 2)
                ],
                'extra' => [
                    'name' => $extra['name'] ? $extra['name'] : '',
                    'mobile' => $extra['mobile'] ? $extra['mobile'] : '',
                    'bank_name' => $extra['bank_name'] ? $extra['bank_name'] : ''
                ],
                'time' => [
                    'created_at' => $item['created_at'],
                    'apply_at' => isset($extra['apply_at']) ? $extra['apply_at'] : '',
                    'remittance_at' => isset($extra['remittance_at']) ? $extra['remittance_at'] : '',
                    'reject_at' => isset($extra['reject_at']) ? $extra['reject_at'] : '',
                ],
                'content' => [
                    'apply_content' => isset($extra['apply_content']) ? $extra['apply_content'] : '',
                    'remittance_content' => isset($extra['remittance_content']) ? $extra['remittance_content'] : '',
                    'reject_content' => isset($extra['reject_content']) ? $extra['reject_content'] : '',
                ],
                'model' => $item['model'],
                'model_text' => $cashTypeText,
                'remark' => $item['content']
            ];
            if ($item['model'] == 'mch') {
                $newItem['shop_name'] = $extra['shop_name'];
            }
            $newList[] = $newItem;
        }

        return [
            'list' => $newList,
            'pagination' => $pagination,
            'export_list' => (new FinanceCashExport())->fieldsList(),
        ];
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function getQuery()
    {
        $permission = $this->getPermission();
        $hasIn = array_reduce($permission, function ($res, $model) {
            if (isset($model['finance']) && $model['finance'] && (!$this->model_type || $model['key'] == $this->model_type)) {
                $res[] = $model['key'];
            }
            return $res;
        }, []);

        $baseQuery = Finance::find()
            ->alias('f')
            ->select(["f.*"])
            ->where(["f.mall_id" => $this->mall->id, "f.is_delete" => 0])
            ->andWhere(['f.model' => $hasIn])
            ->keyword($this->status >= 0, ["f.status" => $this->status])
            ->keyword($this->user_id, ["f.user_id" => $this->user_id])
            ->keyword($this->date_start, ['>=', "f.created_at", $this->date_start])
            ->keyword($this->date_end, ['<=', "f.created_at", $this->date_end]);
        $queryList = [];
        foreach ($permission as $model) {
            if (isset($model['finance']) && $model['finance']) {
                continue;
            }
            try {
                if ($model['key'] == 'mch') {
                    $queryList[$model['key']] = $model['class']::find()
                        ->alias($model['key'])
                        ->select([
                            "{$model['key']}.id",
                            "{$model['key']}.mall_id",
                            "{$model['user_alias']}.user_id as user_id",
                            "{$model['key']}.order_no",
                            "{$model['key']}.money as price",
                            new Expression("'0' service_charge"),
                            "{$model['key']}.type",
                            "{$model['key']}.type_data as extra",
                            "{$model['key']}.status",
                            "{$model['key']}.is_delete",
                            "{$model['key']}.created_at",
                            "{$model['key']}.updated_at",
                            "{$model['key']}.deleted_at",
                            "{$model['key']}.content",
                            "{$model['user_alias']}.realname as name",
                        ])
                        ->addSelect(new Expression("'{$model['key']}' model"))
                        ->addSelect(new Expression("{$model['key']}.transfer_status"))
                        ->where(["{$model['key']}.mall_id" => $this->mall->id, "{$model['key']}.is_delete" => 0])
                        ->keyword($this->user_id, ["{$model['user_alias']}.user_id" => $this->user_id])
                        ->keyword($this->date_start, ['>=', "{$model['key']}.created_at", $this->date_start])
                        ->keyword($this->date_end, ['<=', "{$model['key']}.created_at", $this->date_end])
                        ->leftJoin(
                            ["{$model['user_alias']}" => $model['user_class']::tableName()],
                            "{$model['user_alias']}.id = {$model['key']}.mch_id"
                        );
                    $queryList[$model['key']] = $queryList[$model['key']]
                        ->addSelect(["{$model['user_alias']}.mobile AS `phone`"]);

                    if ($this->status == 0) {
                        $queryList[$model['key']] = $queryList[$model['key']]
                            ->andWhere(["{$model['key']}.status" => 0]);
                    } elseif ($this->status == 1) {
                        $queryList[$model['key']] = $queryList[$model['key']]
                            ->andWhere(["{$model['key']}.status" => 1])
                            ->andWhere(["{$model['key']}.transfer_status" => 0]);
                    } elseif ($this->status == 2) {
                        $queryList[$model['key']] = $queryList[$model['key']]
                            ->andWhere(["{$model['key']}.status" => 1])
                            ->andWhere(["{$model['key']}.transfer_status" => 1]);
                    } elseif ($this->status == 3) {
                        $queryList[$model['key']] = $queryList[$model['key']]
                            ->andWhere([
                                'or',
                                ["{$model['key']}.status" => 2],
                                ["{$model['key']}.transfer_status" => 2]
                            ]);
                    } elseif ($this->status == [0, 1]) {
                        $queryList[$model['key']] = $queryList[$model['key']]
                            ->andWhere([
                                'or',
                                ["{$model['key']}.status" => 0],
                                [
                                    'and',
                                    ["{$model['key']}.status" => 1],
                                    ["{$model['key']}.transfer_status" => 0],
                                ],
                            ]);
                    }
                } else {
                    $queryList[$model['key']] = $model['class']::find()
                        ->alias($model['key'])
                        ->select([
                            "{$model['key']}.*",
                            "{$model['user_alias']}.name",
                        ])
                        ->addSelect(new Expression("'{$model['key']}' model"))
                        ->addSelect(new Expression("'' transfer_status"))
                        ->where(["{$model['key']}.mall_id" => $this->mall->id, "{$model['key']}.is_delete" => 0])
                        ->keyword($this->status >= 0, ["{$model['key']}.status" => $this->status])
                        ->keyword($this->user_id, ["{$model['key']}.user_id" => $this->user_id])
                        ->keyword($this->date_start, ['>=', "{$model['key']}.created_at", $this->date_start])
                        ->keyword($this->date_end, ['<=', "{$model['key']}.created_at", $this->date_end])
                        ->leftJoin(
                            ["{$model['user_alias']}" => $model['user_class']::tableName()],
                            "{$model['user_alias']}.user_id = {$model['key']}.user_id"
                        );
                    if (in_array($model['key'], ['stock', 'region'])) {
                        $queryList[$model['key']] = $queryList[$model['key']]
                            ->addSelect(["{$model['user_alias']}.phone"]);
                    } else {
                        $queryList[$model['key']] = $queryList[$model['key']]
                            ->addSelect(["{$model['user_alias']}.mobile AS `phone`"]);
                    }
                }
            } catch (\Exception $exception) {
                continue;
            }
        }
        if (count($queryList) == 0) {
            return $baseQuery;
        }

        if ($this->model_type) {
            if (!isset($queryList[$this->model_type])) {
                return $baseQuery;
            }
            $query = $queryList[$this->model_type];
        } else {
            $query = reset($queryList);
            if (count($queryList) > 1) {
                array_shift($queryList);
                foreach ($queryList as $item) {
                    $query = $query->union($item, true);
                }
            }
        }

        return $query->union($baseQuery, true);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getCount()
    {
        $this->status = [0, 1];
        $query = $this->getQuery();
        try {
            $count = $query->count();
        } catch (\Error $error) {
            $count = 0;
        }
        return $count;
    }

    private function parseMchStatus($status)
    {
        if ($status['status'] == 1) {
            if ($status['transfer_status'] == 2) {
                return 3;
            } elseif ($status['transfer_status'] == 1) {
                return 2;
            }
        } elseif ($status['status'] == 2) {
            return 3;
        }
        return $status['status'];
    }

    /**
     * @return Query
     * @throws \Exception
     */
    public function getNewQuery()
    {
        $this->status = 2;
        $query = $this->getQuery();
        $tempQuery = (new Query())->from($query);
        return $tempQuery;
    }
}
