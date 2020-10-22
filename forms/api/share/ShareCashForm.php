<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/25
 * Time: 16:11
 */

namespace app\forms\api\share;


use app\core\response\ApiCode;
use app\forms\common\mptemplate\MpTplMsgDSend;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\forms\common\share\CommonShareConfig;
use app\models\Mall;
use app\models\Model;
use app\models\Share;
use app\models\ShareCash;
use app\models\ShareSetting;
use app\models\User;
use yii\db\Exception;
use yii\helpers\Json;

/**
 * @property User $user;
 * @property Mall $mall;
 */
class ShareCashForm extends Model
{
    public $price;
    public $type;
    public $name;
    public $mobile;
    public $bank_name;

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
        $this->setting = CommonShareConfig::config();
        $minPrice = round($this->setting[ShareSetting::MIN_MONEY], 2);
        return [
            [['price', 'type'], 'required'],
            [['price'], 'number', 'min' => $minPrice],
            [['type', 'name', 'mobile', 'bank_name'], 'trim'],
            [['type', 'name', 'mobile', 'bank_name'], 'string'],
            [['type'], function ($attr, $params) {
                if (!in_array($this->type, (array)$this->setting[ShareSetting::PAY_TYPE])) {
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

    public function save()
    {
        $key = 'share_cash_mall_' . $this->mall->id . '_user_' . $this->user->id;
        $cashToken = \Yii::$app->cache->get($key);
        if ($cashToken) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '请不要重复提交'
            ];
        } else {
            $data = $this->attributes;
            $data['user_id'] = $this->user->id;
            $data['time'] = date('YmdHis');
            $token = sha1(Json::encode($data, JSON_UNESCAPED_UNICODE));
            \Yii::$app->cache->set($key, $token);
        }
        try {
            if (!$this->validate()) {
                throw new \Exception($this->getErrorMsg());
            }

            if (!$this->setting) {
                throw new \Exception('分销设置未设置');
            }

            if ($this->user->identity->is_distributor != 1) {
                throw new \Exception('申请的用户不是分销商，无法提现');
            }

            $share = Share::findOne([
                'is_delete' => 0, 'mall_id' => $this->mall->id, 'status' => 1, 'user_id' => $this->user->id
            ]);

            if (!$share) {
                throw new \Exception('申请的用户不是分销商，无法提现');
            }

            if ($this->price <= 0) {
                throw new \Exception('提现佣金必须大于0');
            }

            if ($share->money < $this->price) {
                throw new \Exception('提现佣金超出分销商可提现佣金');
            }
            $cashMaxDay = $this->setting[ShareSetting::CASH_MAX_DAY];

            if ($cashMaxDay > -1) {
                $surplus = $this->setting['surplusCash'];
                if ($surplus == 0) {
                    throw new \Exception('今日可提现额度已用完');
                }

                if (floatval($this->price) > floatval($surplus)) {
                    throw new \Exception('提现金额大于今日剩余可提现额度');
                }
            }

            $exists = ShareCash::find()->where([
                'is_delete' => 0, 'mall_id' => $this->mall->id, 'status' => 0, 'user_id' => $this->user->id
            ])->exists();

            if ($exists) {
                throw new \Exception('尚有未审核的提现申请');
            }

            $extra = \Yii::$app->serializer->encode([
                'name' => $this->name,
                'mobile' => $this->mobile,
                'bank_name' => $this->bank_name,
            ]);

            $t = \Yii::$app->db->beginTransaction();
            $cash = new ShareCash();
            $cash->mall_id = $this->mall->id;
            $cash->user_id = $this->user->id;
            $cash->price = round($this->price, 2);
            $cash->service_charge = isset($this->setting[ShareSetting::CASH_SERVICE_CHARGE])
                ? $this->setting[ShareSetting::CASH_SERVICE_CHARGE] : 0;
            $cash->type = $this->type;
            $cash->extra = $extra;
            $cash->status = 0;
            $cash->order_no = date('YmdHis') . rand(10000, 99999);
            $cash->is_delete = 0;
            if ($cash->save()) {
                try {
                    \Yii::$app->currency->setUser($this->user)->brokerage
                        ->sub(round($this->price, 2), "分销商申请提现{$this->price}");
                    $t->commit();

                    try {
                        $tplMsg = new MpTplMsgSend();
                        $tplMsg->method = 'shareWithdrawTpl';
                        $tplMsg->params = [
                            'time' => date('Y-m-d H:i:s'),
                            'money' => $this->price,
                            'user' => $this->user->nickname
                        ];
                        $tplMsg->sendTemplate(new MpTplMsgDSend());
                    } catch (\Exception $exception) {
                        \Yii::error('公众号模板消息发送: ' . $exception->getMessage());
                    }

                    \Yii::$app->cache->delete($key);
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'msg' => '提现申请成功'
                    ];
                } catch (Exception $e) {
                    $t->rollBack();
                    throw $e;
                }
            } else {
                $t->rollBack();
                throw new \Exception($this->getErrorMsg($cash));
            }
        } catch (\Exception $exception) {
            \Yii::$app->cache->delete($key);
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
