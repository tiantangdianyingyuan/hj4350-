<?php

/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/15
 * Time: 15:49
 */

namespace app\plugins\region\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\region\forms\mall\CommonForm;
use app\plugins\region\models\RegionCash;
use app\plugins\region\models\RegionSetting;
use app\plugins\region\models\RegionUser;

class CashForm extends Model
{
    public $price;
    public $type;
    public $name;
    public $mobile;
    public $bank_name;

    public $setting;
    public $mall;
    public $user;
    public $region;

    public function __construct(array $config = [])
    {
        $this->mall = \Yii::$app->mall;
        $this->user = \Yii::$app->user->identity;
        $this->region = RegionUser::find()
            ->alias('su')
            ->where(['su.user_id' => $this->user->id, 'status' => 1, 'is_delete' => 0])
            ->joinWith(['regionInfo'])
            ->one();
        parent::__construct($config);
    }

    public function rules()
    {
        $this->setting = RegionSetting::getList(['mall_id' => \Yii::$app->mall->id]);
        $minPrice = round($this->setting[RegionSetting::MIN_MONEY], 2);
        return [
            [['price', 'type'], 'required'],
            [['price'], 'number', 'min' => $minPrice, 'tooSmall' => '{attribute}不能少于￥{min}'],
            [['type', 'name', 'mobile', 'bank_name'], 'trim'],
            [['type', 'name', 'mobile', 'bank_name'], 'string'],
            [
                ['type'],
                function ($attr, $params) {
                    if (!in_array($this->type, (array)$this->setting[RegionSetting::PAY_TYPE])) {
                        $this->addError($attr, '请选择提现方式');
                    }
                }
            ],
            [
                ['name', 'mobile', 'bank_name'],
                function ($attr, $params) {
                    if (in_array($this->type, ['wechat', 'alipay']) && !$this->$attr && $attr != 'bank_name') {
                        $this->addError($attr, $this->attributeLabels()[$attr] . '不能为空');
                    }
                    if ($this->type == 'bank' && !$this->$attr) {
                        $this->addError($attr, $this->attributeLabels()[$attr] . '不能为空');
                    }
                },
                'skipOnEmpty' => false,
                'skipOnError' => false
            ]
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

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if (!$this->setting) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '区域代理未设置'
            ];
        }

        if (!$this->region || $this->region->status != 1) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '申请的用户不是代理，无法提现'
            ];
        }

        if ($this->price <= 0) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提现佣金必须大于0'
            ];
        }

        if ($this->region->regionInfo->total_bonus < $this->price) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提现金额超出区域代理可提现金额'
            ];
        }

        $exists = RegionCash::find()->where(
            [
                'is_delete' => 0,
                'mall_id' => $this->mall->id,
                'status' => 0,
                'user_id' => $this->user->id
            ]
        )->exists();

        if ($exists) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '尚有未审核的提现申请'
            ];
        }

        $extra = \Yii::$app->serializer->encode(
            [
                'name' => $this->name,
                'mobile' => $this->mobile,
                'bank_name' => $this->bank_name,
            ]
        );

        $t = \Yii::$app->db->beginTransaction();

        $minPrice = round($this->setting[RegionSetting::FREE_CASH_MIN], 2);
        $price = round($this->price, 2);
        $serviceCharge = isset($this->setting[RegionSetting::CASH_SERVICE_CHARGE])
            ? $this->setting[RegionSetting::CASH_SERVICE_CHARGE] : 0;
        if ($price >= $minPrice) {
            $serviceCharge = 0;
        }

        $cash = new RegionCash();
        $cash->mall_id = $this->mall->id;
        $cash->user_id = $this->user->id;
        $cash->price = $price;
        $cash->service_charge = $serviceCharge;
        $cash->type = $this->type;
        $cash->extra = $extra;
        $cash->status = 0;
        $cash->order_no = date('YmdHis') . rand(10000, 99999);
        $cash->is_delete = 0;
        if ($cash->save()) {
            try {
                CommonForm::bonusCash($cash->user_id, $cash->price, 2);
                $t->commit();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '提现申请成功'
                ];
            } catch (\Exception $e) {
                $t->rollBack();
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $e->getMessage()
                ];
            }
        } else {
            $t->rollBack();
            return $this->getErrorResponse($cash);
        }
    }
}
