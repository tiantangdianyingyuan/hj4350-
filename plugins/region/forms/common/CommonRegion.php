<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019年12月14日 10:53:47
 * Time: 18:39
 */

namespace app\plugins\region\forms\common;

use app\forms\api\share\ShareForm;
use app\models\DistrictArr;
use app\models\Model;
use app\models\Order;
use app\models\Share;
use app\plugins\region\models\RegionArea;
use app\plugins\region\models\RegionCash;
use app\plugins\region\models\RegionLevelUp;
use app\plugins\region\models\RegionRelation;
use app\plugins\region\models\RegionUser;
use app\plugins\region\models\RegionUserInfo;

/**
 * Class CommonRegion
 * @package app\plugins\region\forms\common
 */
class CommonRegion extends Model
{
    const ALL_MEMBERS = 1; //下线总人数
    const ALL_SHARE_ORDERS = 4; //分销订单总数
    const TOTAL_BONUS = 5; //分销订单总金额
    const ALL_BONUS = 2; //累计佣金总额
    const CASHED_BONUS = 3; //已提现佣金总额
    const ALL_CONSUME = 6; //总消费金额

    private static $instance;
    private $mall;
    private $share;
    /**@var RegionUser $user * */
    private $user;

    public $user_id;
    public $province_id;
    public $city_id;
    public $district_id;
    public $status;
    public $area_id;
    public $level;
    public $agreed_at;
    public $name;
    public $mobile;
    public $reason;
    public $applyed_at;


    /**
     * @param null $mall
     * @return CommonRegion
     */
    public static function getInstance($mall = null)
    {
        if (!self::$instance) {
            self::$instance = new self();
            if (!$mall) {
                $mall = \Yii::$app->mall;
            }
            self::$instance->mall = $mall;
        }
        return self::$instance;
    }

    /**
     * 获取分销商信息
     * @return Share|null
     * @throws \Exception
     */
    public function getShare()
    {
        if ($this->share) {
            return $this->share;
        }
        $this->share = Share::findOne(['mall_id' => $this->mall->id, 'user_id' => $this->user_id, 'is_delete' => 0]);
        if (!$this->share) {
            throw new \Exception('你不是分销商');
        }
        return $this->share;
    }

    /**
     * @param mixed $share
     */
    public function setShare($share): void
    {
        $this->share = $share;
    }

    /**
     * @param int $level
     * @return mixed
     * @throws \Exception
     */
    public function index($level)
    {
        if (!in_array($level, ['1', '2', '3'])) {
            throw new \Exception('不合法的等级');
        }
        /**@var RegionArea $area * */
        $area = $this->area();
        $levels = [
            '1' => $area->province_condition,
            '2' => $area->city_condition,
            '3' => $area->district_condition
        ];
        if ($level) {
            $levels = [$level => $levels[$level]];
        }
        $condition = $this->condition($levels, $area->become_type);
        return $condition;
    }

    /**
     * 区域是否开启代理
     * @return mixed
     * @throws \Exception
     */
    public function area()
    {
        if (empty($this->user_id)) {
            throw new \Exception('user_id不能为空');
        }

        if (empty($this->province_id)) {
            throw new \Exception('province_id不能为空');
        }

        $area = RegionArea::find()
            ->alias('a')
            ->where(['a.mall_id' => $this->mall->id, 'a.is_delete' => 0])
            ->joinWith(
                [
                    'areaDetail ad' => function ($query) {
                        $query->andWhere(['ad.province_id' => $this->province_id, 'ad.is_delete' => 0]);
                    }
                ]
            )
            ->one();

        if (!$area) {
            throw new \Exception('你所选择的区域暂未开启代理功能');
        }

        return $area;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function nextLevel()
    {
        if (empty($this->user_id)) {
            throw new \Exception('user_id不能为空');
        }

        $user = RegionUser::find()
            ->where(
                [
                    'mall_id' => $this->mall->id,
                    'is_delete' => 0,
                    'user_id' => $this->user_id,
                    'status' => RegionUser::STATUS_BECOME
                ]
            )
            ->with(['area'])
            ->one();

        if ($user && $user->area) {
            if ($user->level == 1) {
                throw new \Exception('你已经是省代理了');
            }
            $this->user = $user;
            $levels = [
                '1' => $user->area->province_condition,
                '2' => $user->area->city_condition
            ];

            $condition = $this->condition($levels, $user->area->become_type, true);
            return $condition;
        }

        throw new \Exception('你不是代理');
    }

    /**
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function levelUp()
    {
        $pass = false;
        $condition = $this->nextLevel();
        foreach ($condition as $item) {
            if ($item['pass'] == true) {
                $pass = true;
            }
        }
        if ($pass != true) {
            throw new \Exception('你还未满足条件');
        }

        $model = RegionLevelUp::find()
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'user_id' => $this->user_id, 'status' => 0])
            ->one();
        if (!$model) {
            $model = new RegionLevelUp();
            $model->mall_id = $this->mall->id;
            $model->user_id = $this->user_id;
        }
        $model->level = $this->level;
        if (!$model->save()) {
            throw new \Exception($this->getErrorMsg($model));
        }

        RegionRelation::updateAll(
            [
                'is_delete' => 1
            ],
            [
                'mall_id' => $this->mall->id,
                'is_delete' => 0,
                'user_id' => $this->user_id,
                'is_update' => 1
            ]
        );

        $this->province_id = $this->user->province_id;
        $district = $this->checkArea();
        foreach ($district as $key => $item) {
            array_push($district[$key], 1);
        }
        \Yii::$app->db->createCommand()->batchInsert(
            RegionRelation::tableName(),
            ['district_id', 'mall_id', 'user_id', 'is_delete', 'created_at', 'is_update'],
            $district
        )->execute();
    }

    /**
     * 判断申请条件
     * @param $levels
     * @param $type
     * @param bool $array
     * @return mixed
     * @throws \Exception
     */
    private function condition($levels, $type, $array = false)
    {
        $form = new ShareForm();
        $price = $form->getPrice($this->user_id);
        $becomeType = $type;
        $newList = [];
        foreach ($levels as $key => $item) {
            switch ($becomeType) {
                case self::ALL_BONUS:
                    $info['type'] = self::ALL_BONUS;
                    $info['level'] = $key;
                    $info['level_desc'] = $this->parseLevel($key);
                    $info['now_target'] = $price['total_money'];
                    $info['target'] = price_format($item);
                    $info['pass'] = $info['now_target'] >= $info['target'] ? true : false;
                    if (!$array) {
                        return $info;
                    }
                    $newList[] = $info;
                    break;

                case self::CASHED_BONUS:
                    $info['type'] = self::CASHED_BONUS;
                    $info['level'] = $key;
                    $info['level_desc'] = $this->parseLevel($key);
                    $info['now_target'] = $price['cash_money'];
                    $info['target'] = price_format($item);
                    $info['pass'] = $info['now_target'] >= $info['target'] ? true : false;
                    if (!$array) {
                        return $info;
                    }
                    $newList[] = $info;
                    break;

                case self::ALL_MEMBERS:
                    $count = $this->getShare()->all_children;
                    $info['type'] = self::ALL_MEMBERS;
                    $info['level'] = $key;
                    $info['level_desc'] = $this->parseLevel($key);
                    $info['now_target'] = $count ? $count : 0;
                    $info['target'] = (int)$item;
                    $info['pass'] = $info['now_target'] >= $info['target'] ? true : false;
                    if (!$array) {
                        return $info;
                    }
                    $newList[] = $info;
                    break;

                case self::ALL_SHARE_ORDERS:
                    $count = $this->getShare()->all_order;
                    $info['type'] = self::ALL_SHARE_ORDERS;
                    $info['level'] = $key;
                    $info['level_desc'] = $this->parseLevel($key);
                    $info['now_target'] = $count ? $count : 0;
                    $info['target'] = (int)$item;
                    $info['pass'] = $info['now_target'] >= $info['target'] ? true : false;
                    if (!$array) {
                        return $info;
                    }
                    $newList[] = $info;
                    break;

                case self::TOTAL_BONUS:
                    $count = $this->getShare()->all_money;
                    $info['type'] = self::TOTAL_BONUS;
                    $info['level'] = $key;
                    $info['level_desc'] = $this->parseLevel($key);
                    $info['now_target'] = $count ? $count : 0;
                    $info['target'] = price_format($item);
                    $info['pass'] = $info['now_target'] >= $info['target'] ? true : false;
                    if (!$array) {
                        return $info;
                    }
                    $newList[] = $info;
                    break;

                case self::ALL_CONSUME:
                    $info['type'] = self::ALL_CONSUME;
                    $info['level'] = $key;
                    $info['level_desc'] = $this->parseLevel($key);
                    $info['now_target'] = Order::find()
                        ->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'user_id' => $this->user_id, 'is_pay' => 1, 'is_sale' => 1])
                        ->sum('total_pay_price');
                    $info['target'] = price_format($item);
                    $info['pass'] = $info['now_target'] >= $info['target'] ? true : false;
                    if (!$array) {
                        return $info;
                    }
                    $newList[] = $info;
                    break;

                default:
                    throw new \Exception('未知的条件');
            }
        }

        return $newList;
    }

    /**
     * @param $level
     * @return string
     */
    public function parseLevel($level)
    {
        switch ($level) {
            case 1:
                return '省代理';
            case 2:
                return '市代理';
            case 3:
                return '区/县代理';
            default:
                return '未知级别';
        }
    }

    /**
     * 检查省市县数据是否合法
     * @return array
     * @throws \Exception
     */
    public function checkArea()
    {
        $district = [];
        if ($this->level == 3) {
            if (empty($this->city_id) || (is_array($this->city_id) && empty($this->city_id[0]))) {
                throw new \Exception('请选择市');
            }
            if (empty($this->district_id)) {
                throw new \Exception('请选择区/县');
            }
            foreach ($this->district_id as $item) {
                if (DistrictArr::getDistrict($item)['level'] != 'district') {
                    throw new \Exception('你选择的区/县数据不合法');
                }
                $district[] = [
                    $item,
                    $this->mall->id,
                    $this->user_id,
                    0,
                    mysql_timestamp()
                ];
            }
        } elseif ($this->level == 2) {
            if (empty($this->city_id)) {
                throw new \Exception('请选择市');
            }
            foreach ($this->city_id as $item) {
                if (DistrictArr::getDistrict($item)['level'] != 'city') {
                    throw new \Exception('你选择的市级数据不合法');
                }
                $district[] = [
                    $item,
                    $this->mall->id,
                    $this->user_id,
                    0,
                    mysql_timestamp()
                ];
            }
        } elseif ($this->level == 1) {
            if (DistrictArr::getDistrict($this->province_id)['level'] != 'province') {
                throw new \Exception('你选择的省级数据不合法');
            }
            $district[] = [
                $this->province_id,
                $this->mall->id,
                $this->user_id,
                0,
                mysql_timestamp()
            ];
        }

        return $district;
    }

    /**
     * 检测省市区数据是否对应
     * @throws \Exception
     */
    public function checkRelation()
    {
        if ($this->level == 3) {
            foreach ($this->district_id as $item) {
                if (DistrictArr::getDistrict($item)['parent_id'] != current($this->city_id)) {
                    throw new \Exception('省市区数据不合法x1');
                }
            }
        } elseif ($this->level == 2) {
            foreach ($this->city_id as $item) {
                if (DistrictArr::getDistrict($item)['parent_id'] != $this->province_id) {
                    throw new \Exception('省市区数据不合法x2');
                }
            }
        }
    }

    /**
     * 添加代理方法
     * @return RegionUser|null
     * @throws \yii\db\Exception
     */
    public function saveRegion()
    {
        if (!$this->user_id) {
            throw new \Exception('user_id不能为空');
        }

        $district = $this->checkArea();
        $this->checkRelation();
        $region = RegionUser::findOne(['user_id' => $this->user_id, 'mall_id' => $this->mall->id]);
        if (empty($region)) {
            $region = new RegionUser();
            $region->attributes = $this->attributes;
            $region->mall_id = $this->mall->id;
            $region->user_id = $this->user_id;

            $regionInfo = new RegionUserInfo();
            $regionInfo->user_id = $this->user_id;

            $share = $this->getShare();
            $name = $this->name ?? $share->name;
            $phone = $this->mobile ?? $share->mobile;
            $regionInfo->name = $name;
            $regionInfo->phone = $phone;
        } else {
            $region->is_delete = 0;

            $regionInfo = RegionUserInfo::findOne(['user_id' => $this->user_id]);
            $name = $this->name ?? $regionInfo->name;
            $phone = $this->mobile ?? $regionInfo->phone;
            $regionInfo->name = $name;
            $regionInfo->phone = $phone;
            $regionInfo->remark = '';
        }

        RegionRelation::updateAll(
            [
                'is_delete' => 1
            ],
            [
                'mall_id' => $this->mall->id,
                'is_delete' => 0,
                'user_id' => $this->user_id,
                'is_update' => 0
            ]
        );

        \Yii::$app->db->createCommand()->batchInsert(
            RegionRelation::tableName(),
            ['district_id', 'mall_id', 'user_id', 'is_delete', 'created_at'],
            $district
        )->execute();

        $this->applyed_at && $region->applyed_at = $this->applyed_at;
        $this->agreed_at && $region->agreed_at = $this->agreed_at;
        $region->province_id = $this->province_id;
        $region->level = $this->level;
        $region->status = $this->status;
        $region->area_id = $this->area_id;
        if (!$region->save()) {
            throw new \Exception($this->getErrorMsg($region));
        }

        $regionInfo->reason = $this->reason ?? '';
        if (!$regionInfo->save()) {
            throw new \Exception($this->getErrorMsg($regionInfo));
        }
        return $region;
    }

    /**
     * @param $user_id
     * @return array
     */
    public function getPrice($user_id)
    {
        /* @var RegionCash[] $list */
        $list = RegionCash::find()->where(
            [
                'mall_id' => $this->mall->id,
                'user_id' => $user_id,
                'is_delete' => 0,
            ]
        )->andWhere(['<', 'status', 3])->all();
        $unPay = 0;
        $cashMoney = 0;
        $totalCash = 0;
        foreach ($list as $cash) {
            $totalCash += floatval($cash->price);
            switch ($cash->status) {
                case 0:
                    break;
                case 1:
                    $unPay += floatval($cash->price);
                    break;
                case 2:
                    $cashMoney += floatval($cash->price);
                    break;
                default:
            }
        }

        return [
            'un_pay' => price_format($unPay),
            'cash_bonus' => price_format($cashMoney),  //已提现
        ];
    }
}
