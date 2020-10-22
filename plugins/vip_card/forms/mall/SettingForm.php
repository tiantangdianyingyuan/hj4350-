<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/21
 * Time: 10:41
 */

namespace app\plugins\vip_card\forms\mall;

use app\core\response\ApiCode;
use app\models\MallSetting;
use app\models\Model;
use app\plugins\vip_card\forms\api\IndexForm;
use app\plugins\vip_card\forms\common\CommonVipCardSetting;
use app\plugins\vip_card\models\VipCard;
use app\plugins\vip_card\models\VipCardSetting;
use app\plugins\vip_card\Plugin;
use yii\helpers\ArrayHelper;

class SettingForm extends Model
{
    public $is_vip_card;
    public $payment_type;
    public $is_share;
    public $is_sms;
    public $is_mail;
    public $is_buy_become_share;
    public $share_type;
    public $share_commission_first;
    public $share_commission_second;
    public $share_commission_third;
    public $form;
    public $rules;
    public $shareLevelList;
    public $is_agreement;
    public $agreement_title;
    public $agreement_content;
    public $is_order_form;
    public $order_form;

    public function rules()
    {
        return [
            [['is_vip_card', 'is_share', 'share_type', 'is_sms', 'is_mail', 'is_buy_become_share'], 'required'],
            [['share_commission_first', 'share_commission_second', 'share_commission_third'], 'number'],
            [['payment_type', 'rules', 'shareLevelList'], 'safe'],
            [['is_agreement', 'is_order_form'], 'integer'],
            [['agreement_title', 'agreement_content',], 'string'],
            [['form', 'order_form'], 'trim'],
            [['share_commission_first', 'share_commission_second', 'share_commission_third'], 'number', 'max' => 99999999],
            [['share_commission_first', 'share_commission_second', 'share_commission_third'], 'number', 'min' => 0],
            [['order_form'], 'default', 'value' => []]
        ];
    }

    public function attributeLabels()
    {
        return [
            'is_vip_card' => 'SVIP会员卡开关',
            'is_share' => '分销开关',
            'payment_type' => '支付类型',
            'is_sms' => '短信提醒',
            'is_mail' => '邮件提醒',
            'share_type' => '分销类型',
            'share_commission_first' => '一级佣金',
            'share_commission_second' => '二级佣金',
            'share_commission_third' => '三级佣金',
            'form' => '自定义页面',
            'rules' => '插件规则',
            'is_agreement' => '申请协议',
            'agreement_title' => '协议名称',
            'agreement_content' => '协议内容',
            'is_buy_become_share' => '购买成为分销商',
            'shareLevelList' => '分销等级',
            'is_order_form' => '下单表单开关',
            'order_form' => '下单表单'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $model = VipCardSetting::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
            if (!$model) {
                $model = new VipCardSetting();
                $model->mall_id = \Yii::$app->mall->id;
            }
            $model->is_vip_card = $this->is_vip_card;
            $model->payment_type = \Yii::$app->serializer->encode($this->payment_type ?: []);
            $model->is_share = $this->is_share;
            $model->is_sms = $this->is_sms;
            $model->is_mail = $this->is_mail;
            $model->is_agreement = $this->is_agreement;
            $model->agreement_title = $this->agreement_title;
            $model->agreement_content = $this->agreement_content;
            $model->form = $this->form;
            $model->rules = \Yii::$app->serializer->encode($this->rules ?: []);
            $model->is_buy_become_share = $this->is_buy_become_share;
            $model->share_type = $this->share_type;
            $model->share_level = \Yii::$app->serializer->encode($this->shareLevelList ?: []);
            $model->is_order_form = $this->is_order_form;
            if ($this->is_order_form) {
                $this->checkData($this->order_form ?? []);
            }
            $model->order_form = \Yii::$app->serializer->encode($this->order_form ?? []);
            if (!empty($this->shareLevelList)) {
                foreach ($this->shareLevelList as $level) {
                    if ($level['level'] == 0 && isset($level['name']) && isset($level['share_commission_first']) && isset($level['share_commission_second']) && isset($level['share_commission_third'])) {
                        $model->share_commission_first = $level['share_commission_first'];
                        $model->share_commission_second = $level['share_commission_second'];
                        $model->share_commission_third = $level['share_commission_third'];
                    }
                }
            }
            $res = $model->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($model));
            }

            if ($this->is_vip_card == 0) {
                $setting = ['is_show_normal_vip', 'is_show_super_vip'];
                foreach ($setting as $k) {
                    $mallSetting = MallSetting::findOne(['key' => $k, 'mall_id' => \Yii::$app->mall->id]);
                    if ($mallSetting) {
                        $mallSetting->value = '0';
                        $mallSetting->save();
                    } else {
                        $mallSetting = new MallSetting();
                        $mallSetting->key = $k;
                        $mallSetting->value = '0';
                        $mallSetting->mall_id = \Yii::$app->mall->id;
                        $mallSetting->save();
                    }
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "保存成功"
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function search()
    {
        (new IndexForm())->getGoods();
        $setting = (new CommonVipCardSetting())->getSetting();
        $permissions = \Yii::$app->role->getPermission();
        if (\Yii::$app->getRole()->getName() == 'operator') {
            foreach ($permissions as $permission) {
                if (strstr($permission, 'mall/share')) {
                    $permissions = [];
                    $permissions[] = 'share';
                    break;
                }
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'setting' => (object)$setting,
                'permissions' => $permissions
            ]
        ];
    }

    public function getDefault()
    {
        $pluginName = (new Plugin())->getName();
        return [
            'is_vip_card' => 0,
            'payment_type' => ['online_pay'],
            'is_share' => 0,
            'is_sms' => 0,
            'is_mail' => 0,
            'is_agreement' => 0,
            'agreement_title' => '',
            'agreement_content' => '',
            'form' => '',
            'rules' => [],
            'is_buy_become_share' => 0,
            'share_type' => 1,
            'share_commission_first' => 0,
            'share_commission_second' => 0,
            'share_commission_third' => 0,
            'name' => '超级会员卡',
            'is_order_form' => 0,
            'order_form' => []
        ];
    }

    // 检测数据
    private function checkData($data)
    {
        if (!is_array($data) || empty($data)) {
            throw new \Exception('请设置表单');
        }
        foreach ($data as $key => $item) {
            if (!$item['name']) {
                throw new \Exception('请填写 ' . $item['key_name'] . ' 名称');
            }
        }
    }
}
