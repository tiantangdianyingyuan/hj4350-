<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/6/30
 * Time: 15:14
 */

namespace app\forms\api\finance;

use app\core\response\ApiCode;
use app\models\Finance;
use app\models\Mall;
use app\models\Model;
use app\models\OrderSubmitResult;
use app\models\User;

/**
 * Class BaseFinanceCashForm
 * @package app\forms\api\finance
 * @property Mall $mall
 * @property User $user
 */
abstract class BaseFinanceCashForm extends Model
{
    public $price;
    public $type;
    public $name;
    public $mobile;
    public $bank_name;

    public $token;
    public $setting;
    public $mall;
    public $user;

    public function __construct(array $config = [])
    {
        $this->mall = \Yii::$app->mall;
        $this->user = \Yii::$app->user->identity;
        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['price', 'type'], 'required'],
            [['price'], 'number', 'min' => 0],
            [['type', 'name', 'mobile', 'bank_name'], 'trim'],
            [['type', 'name', 'mobile', 'bank_name'], 'string'],
            [['type'], function ($attr, $params) {
                if (!in_array($this->type, (array)$this->setting[Finance::PAY_TYPE])) {
                    $this->addError($attr, '错误的提现方式');
                }
            }],
            [['name', 'mobile', 'bank_name'], function ($attr, $params) {
                if (in_array($this->type, ['wechat', 'alipay']) && !$this->$attr && $attr != 'bank_name') {
                    $this->addError($attr, $this->attributeLabels()[$attr] . '不能为空');
                }
                if ($this->type == 'bank' && !$this->$attr) {
                    $this->addError($attr, $this->attributeLabels()[$attr] . '不能为空');
                }
            }, 'skipOnEmpty' => false, 'skipOnError' => false]
        ];
    }

    public function attributeLabels()
    {
        return [
            'price' => '提现金额',
            'type' => '提现方式',
            'name' => '微信昵称/支付宝昵称/开户人',
            'mobile' => '微信号/支付宝账号/开户号',
            'bank_name' => '开户行'
        ];
    }

    public function check()
    {
        if (!$this->validate()) {
            throw new \Exception($this->getErrorMsg());
        }

        if ($this->price <= 0) {
            throw new \Exception('提现金额必须大于0');
        }

        /**@var UserInfo $userInfo**/
        $userInfo = $this->setUserInfo(new UserInfo());
        $this->beforeCashValidate();

        $exists = Finance::find()->where(
            [
                'is_delete' => 0, 'mall_id' => $this->mall->id, 'status' => 0, 'user_id' => $this->user->id
            ]
        )->exists();

        if ($exists) {
            throw new \Exception('尚有未审核的提现申请');
        }
        return $userInfo;
    }

    public function save()
    {
        try {
            $this->check();
            $token = generate_order_no();
            $queueId = \Yii::$app->queue->delay(0)->push(new FinanceJob([
                'token' => $token,
                'price' => $this->price,
                'type' => $this->type,
                'name' => $this->name,
                'mobile' => $this->mobile,
                'bank_name' => $this->bank_name,
                'setting' => $this->setting,
                'mall' => $this->mall,
                'user' => $this->user,
                'appVersion' => \Yii::$app->appVersion,
                'financeCashFormClass' => static::class,
            ]));
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'queue_id' => $queueId,
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

    public function job()
    {
        try {
            $userInfo = $this->check();

            $extra = \Yii::$app->serializer->encode([
                'name' => $this->name,
                'mobile' => $this->mobile,
                'bank_name' => $this->bank_name,
            ]);

            $t = \Yii::$app->db->beginTransaction();
            $cash = new Finance();
            $cash->mall_id = $this->mall->id;
            $cash->user_id = $this->user->id;
            $cash->price = round($this->price, 2);
            $cash->service_charge = $this->getServiceCharge();
            $cash->type = $this->type;
            $cash->extra = $extra;
            $cash->status = 0;
            $cash->order_no = $this->token;
            $cash->is_delete = 0;
            $cash->name = $userInfo->name;
            $cash->phone = $userInfo->phone;
            $cash->model = $this->setModel();
            if ($cash->save()) {
                try {
                    $this->afterSave();

                    $t->commit();
                    return true;
                } catch (\Exception $e) {
                    $t->rollBack();
                    throw $e;
                }
            } else {
                $t->rollBack();
                throw new \Exception($this->getErrorMsg($cash));
            }
        } catch (\Exception $exception) {
            \Yii::error($exception->getMessage());
            \Yii::error($exception);
            $orderSubmitResult = new OrderSubmitResult();
            $orderSubmitResult->token = $this->token;
            $orderSubmitResult->data = $exception->getMessage();
            $orderSubmitResult->save();
            return false;
        }
    }

    /**
     * 申请提现前插件各自的逻辑验证
     * @return mixed
     */
    abstract protected function beforeCashValidate();

    /**
     * 保存申请提现记录后
     * @return mixed
     */
    abstract protected function afterSave();

    /**
     * 设置一些额外的信息
     * 真实姓名和手机号
     * @param UserInfo $userInfo
     * @return UserInfo
     */
    abstract protected function setUserInfo(UserInfo $userInfo);

    /**
     * 返回一个插件标识
     * @return mixed
     */
    abstract protected function setModel();

    /**
     * @return int
     * 提现手续费
     */
    public function getServiceCharge()
    {
        return 0;
    }
}
