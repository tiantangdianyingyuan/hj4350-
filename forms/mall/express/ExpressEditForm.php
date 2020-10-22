<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\express;

use app\core\response\ApiCode;
use app\models\Delivery;
use app\validators\PhoneNumberValidator;
use app\models\Model;

class ExpressEditForm extends Model
{
    public $id;
    public $express_id;
    public $customer_account;
    public $customer_pwd;
    public $month_code;
    public $outlets_name;
    public $outlets_code;
    public $company;
    public $name;
    public $tel;
    public $mobile;
    public $zip_code;
    public $province;
    public $city;
    public $district;
    public $address;
    public $template_size;
    public $is_sms;
    public $is_goods;
    public $goods_alias;
    public $is_goods_alias;
    public $business_type;

    public $kd100_business_type;
    public $kd100_template;

    public function rules()
    {
        return [
            [['name', 'province', 'city', 'district', 'address'], 'required'],
            [['id', 'express_id', 'is_sms', 'is_goods', 'is_goods_alias'], 'integer'],
            [['template_size', 'customer_account', 'customer_pwd', 'month_code', 'outlets_name', 'outlets_code',
                'company', 'name', 'tel', 'mobile', 'zip_code', 'province', 'city', 'district',
                'address', 'goods_alias', 'business_type', 'kd100_business_type', 'kd100_template'], 'string', 'max' => 255],
            [['goods_alias'], 'default', 'value' => '商品'],
            [['express_id', 'is_sms', 'is_goods'], 'default', 'value' => 0],
            [['mobile', 'zip_code', 'template_size', 'company', 'customer_account', 'customer_pwd',
                'month_code','outlets_name', 'outlets_code', 'business_type'], 'default', 'value' => ''],
            [['mobile'], PhoneNumberValidator::className()],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'express_id' => '快递公司id',
            'customer_account' => '电子面单客户账号',
            'customer_pwd' => '电子面单密码',
            'month_code' => '月结编码',
            'outlets_name' => '网点名称',
            'outlets_code' => '网点编码',
            'company' => '发件人公司',
            'name' => '发件人名称',
            'tel' => '发件人电话',
            'mobile' => '发件人手机',
            'zip_code' => '发件人邮政编码',
            'province' => '发件人省份',
            'city' => '发件人市',
            'district' => '发件人区',
            'address' => '发件人详细地址',
            'template_size' => '快递鸟电子面单模板规格',
            'is_sms' => '是否订阅短信',
            'is_goods' => '是否打印商品',
            'goods_alias' => '自定义商品别名',
            'is_goods_alias' => '自定义商品别名开关',
            'business_type' => '业务类型',
            'kd100_business_type' => '快递100 业务类型',
            'kd100_template' => '快递100 模板',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = Delivery::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'id' => $this->id,
        ]);
        if (!$model) {
            $model = new Delivery();
        }
        if (!$this->tel && !$this->mobile) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '发件人电话或手机不能为空'
            ];
        }

        $model->attributes = $this->attributes;
        $model->mall_id = \Yii::$app->mall->id;
        $model->mch_id = \Yii::$app->user->identity->mch_id;

        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }
}
