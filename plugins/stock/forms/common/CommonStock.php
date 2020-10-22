<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019年12月12日 11:18:00
 * Time: 13:37
 */

namespace app\plugins\stock\forms\common;

use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\models\UserIdentity;
use app\plugins\stock\events\StockEvent;
use app\plugins\stock\models\StockCash;
use app\plugins\stock\models\StockSetting;
use app\plugins\stock\models\StockUser;
use app\plugins\stock\models\StockUserInfo;

/**
 * @property Mall $mall;
 */
class CommonStock extends Model
{
    const STATUS_REAPPLYING = -2;
    const STATUS_REMOVE = -1;
    const STATUS_APPLYING = 0;
    const STATUS_BECOME = 1;
    const STATUS_REJECT = 2;

    public $user_id;
    public $reason;
    /**@var UserIdentity $user * */
    public $user;

    public $is_stock;

    public $status;

    public $mall;

    public function rules()
    {
        return [
            [['user_id', 'status'], 'required'],
            [['reason'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_id' => '用户id',
            'status' => '审核状态',
            'reason' => '理由',
            'is_stock' => '是否开启股东分红',
        ];
    }

    public function __construct(array $config = [])
    {
        $this->mall = \Yii::$app->mall;
        $this->is_stock = StockSetting::get($this->mall->id, 'is_stock', 0);
        parent::__construct($config);
    }

    /**
     * 审核成为股东
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
            $stock = StockUser::findOne(['user_id' => $this->user_id, 'is_delete' => 0, 'mall_id' => $this->mall->id]);
            if (!$stock) {
                throw new \Exception('该审核记录不存在');
            }

            if ($stock->status == self::STATUS_BECOME) {
                throw new \Exception('已经是股东了');
            }

            if (!$this->is_stock) {
                throw new \Exception('未开启股东分红');
            }

            if ($this->user->is_distributor != 1) {
                throw new \Exception('申请的用户不是分销商，无法成为股东');
            }

            if ($this->status != self::STATUS_BECOME && $this->status != self::STATUS_REJECT) {
                throw new \Exception('错误的审核状态');
            }
            if ($this->status == self::STATUS_REJECT) {
                if (!$this->reason) {
                    throw new \Exception('请填写拒绝理由');
                }
                $user_info = StockUserInfo::findOne(['user_id' => $this->user_id]);
                $user_info->reason = $this->reason;
                if (!$user_info->save()) {
                    throw new \Exception($this->getErrorMsg($user_info));
                }
            }
            $stock->status = $this->status;
            $stock->agreed_at = mysql_timestamp();
            if (!$stock->save()) {
                throw new \Exception($this->getErrorMsg($stock));
            }

            \Yii::$app->trigger(StockUser::EVENT_BECOME, new StockEvent([
                'stock' => $stock,
            ]));

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => '操作成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }

    }

    //解除股东
    public function remove()
    {
        try {
            $stock = StockUser::findOne(['user_id' => $this->user_id, 'is_delete' => 0, 'mall_id' => $this->mall->id, 'status' => 1]);
            if (!$stock) {
                throw new \Exception('该股东状态错误');
            }
            if (!$this->reason) {
                throw new \Exception('请填写解除理由');
            }
            $user_info = StockUserInfo::findOne(['user_id' => $this->user_id]);
            $user_info->reason = $this->reason;
            if (!$user_info->save()) {
                throw new \Exception($this->getErrorMsg($user_info));
            }
            $stock->status = self::STATUS_REMOVE;
            $stock->applyed_at = mysql_timestamp();
            if (!$stock->save()) {
                throw new \Exception($this->getErrorMsg($stock));
            }

            \Yii::$app->trigger(StockUser::EVENT_REMOVE, new StockEvent([
                'stock' => $stock,
            ]));

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }

    public function getPrice($user_id)
    {
        /* @var StockCash[] $list */
        $list = StockCash::find()->where([
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
            'un_pay' => price_format($unPay),
            'cash_bonus' => price_format($cashMoney),  //已提现
        ];
    }
}
