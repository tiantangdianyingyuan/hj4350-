<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/4
 * Time: 13:37
 */

namespace app\plugins\bonus\forms\common;

use app\core\response\ApiCode;
use app\forms\common\CommonUser;
use app\models\Mall;
use app\models\Model;
use app\models\UserIdentity;
use app\plugins\bonus\events\CaptainEvent;
use app\plugins\bonus\jobs\BecomeCaptainJob;
use app\plugins\bonus\jobs\RemoveCaptainJob;
use app\plugins\bonus\models\BonusCaptain;
use app\plugins\bonus\models\BonusCaptainLog;
use app\plugins\bonus\models\BonusCash;
use app\plugins\bonus\models\BonusSetting;

/**
 * @property Mall $mall;
 */
class CommonCaptain extends Model
{
    const STATUS_AGAIN = -1;
    const STATUS_APPLYING = 0;
    const STATUS_BECOME = 1;
    const STATUS_REJECT = 2;
    const STATUS_DEALING = 3;

    public $user_id;
    public $user;
    public $mall;
    public $members;

    public $is_bonus;
    /**@var BonusCaptain $captain * */
    public $captain;

    /**当前队长所有下级信息**/
    public $temps = [];

    public $status;

    public $reason;

    public function rules()
    {
        return [
            [['user_id', 'status'], 'required'],
            [['reason'], 'trim'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_id' => '用户id',
            'status' => '审核状态',
            'reason' => '理由',
            'is_bonus' => '是否开启团队分红',
        ];
    }

    public function __construct(array $config = [])
    {
        $this->mall = \Yii::$app->mall;
        $this->is_bonus = BonusSetting::get($this->mall->id, 'is_bonus', 0);
        parent::__construct($config);
    }

    /**
     * 审核成为队长
     * @return array
     * @throws \Exception
     */
    public function become()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
			$this->user = UserIdentity::find()->where([
				'user_id' => $this->user_id,
			])->one();
            $captain = BonusCaptain::findOne(['user_id' => $this->user_id, 'is_delete' => 0, 'mall_id' => $this->mall->id]);
            if (!$captain) {
                throw new \Exception('该审核记录不存在');
            }

            if ($captain->status == self::STATUS_BECOME) {
                throw new \Exception('已经是队长了');
            }

            if ($captain->status == self::STATUS_DEALING) {
                throw new \Exception('正在成为队长...');
            }

            $this->captain = $captain;

            if (!$this->is_bonus) {
                throw new \Exception('未开启团队分红');
            }

            if ($this->user->is_distributor != 1) {
                throw new \Exception('申请的用户不是分销商，无法成为队长');
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }

        if ($this->status == self::STATUS_BECOME) {
            $captain->status = self::STATUS_DEALING;
            $captain->save();
            $dataArr = [
                'mall' => $this->mall,
                'user_id' => $this->user_id,
                'captain' => $this->captain
            ];
            $class = new BecomeCaptainJob($dataArr);
            $queueId = \Yii::$app->queue->delay(0)->push($class);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'queue_id' => $queueId
                ],
            ];
        } elseif ($this->status == self::STATUS_REJECT) {
            if (!isset($this->reason) || empty($this->reason)) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '请填写理由'
                ];
            }
            $captain->status = self::STATUS_REJECT;
            $captain->apply_at = mysql_timestamp();
            $captain->reason = $this->reason;
            $captain->save();

            CommonCaptainLog::create(BonusCaptainLog::REJECT_CAPTAIN, $this->user_id, []);

            \Yii::$app->trigger(BonusCaptain::EVENT_BECOME, new CaptainEvent([
                'captain' => $captain,
                'parentId' => 0
            ]));

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => '操作成功'
            ];
        } else {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '错误的审核状态',
            ];
        }
    }

    public function remove()
    {
        try {
            $captain = BonusCaptain::findOne(['user_id' => $this->user_id, 'is_delete' => 0, 'mall_id' => $this->mall->id, 'status' => 1]);
            if (!$captain) {
                throw new \Exception('该队长不存在');
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }

        if (!isset($this->reason) || empty($this->reason)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '请填写理由'
            ];
        }

        $captain->reason = $this->reason;
        $this->captain = $captain;

        $dataArr = [
            'mall' => $this->mall,
            'user_id' => $this->user_id,
            'captain' => $this->captain
        ];
        $class = new RemoveCaptainJob($dataArr);
        $queueId = \Yii::$app->queue->delay(0)->push($class);

        return [
            'code' => 0,
            'data' => [
                'queue_id' => $queueId
            ],
        ];
    }

    public function getPrice($user_id)
    {
        /* @var BonusCash[] $list */
        $list = BonusCash::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => $user_id,
            'is_delete' => 0,
        ])->andWhere(['<', 'status', 3])->all();
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
            'cash_bonus' => price_format($cashMoney),  //已提现
        ];
    }
}