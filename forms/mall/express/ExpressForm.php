<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\express;

use app\core\response\ApiCode;
use app\forms\common\CommonDistrict;
use app\models\Delivery;
use app\models\Express;
use app\models\Model;

class ExpressForm extends Model
{
    public $id;
    public $page_size;
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'page_size'], 'integer'],
            [['page_size'], 'default', 'value' => 10],
            [['keyword'], 'string'],
        ];
    }

    //GET
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $express = Express::getExpressList();
        $query = Delivery::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0
        ]);
        if ($this->keyword) {
            $ids = [];
            foreach($express as $v) {
                if(strstr($v['name'], $this->keyword)!==false) {
                    array_push($ids,$v['id']);
                }
            }
            $query->andWhere(['or',['in', 'express_id', $ids],['like', 'outlets_name', $this->keyword]]);
        }
        $list = $query->orderBy('id desc')->page($pagination, $this->page_size)->asArray()->all();

        foreach ($list as &$item) {
            foreach ($express as $i) {
                if ($i['id'] == $item['express_id']) {
                    $item['express_name'] = $i['name'];
                    break;
                }
            }
        }
        unset($item);

        //默认收件人
        $form = new SenderOptionForm();
        $sender_default = $form->getList();
        $sender_default['default'] = [
            $sender_default['province'],
            $sender_default['city'],
            $sender_default['district']
        ];

        $district = $this->defaultData();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
                'district' => $district,
                'sender_default' => $sender_default,
            ]
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $delivery = Delivery::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
            'id' => $this->id,
        ])
            ->asArray()
            ->one();

        if ($delivery) {
            $delivery['express_id'] = (int)$delivery['express_id'];
            $delivery['is_sms'] = (int)$delivery['is_sms'];
            $delivery['is_goods'] = (int)$delivery['is_goods'];
            $delivery['is_goods_alias'] = (int)$delivery['is_goods_alias'];
            $delivery['default'] = [$delivery['province'], $delivery['city'], $delivery['district']];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $delivery,
                'district' => $this->defaultData(),
                'express' => Express::getExpressList(),
                'template_size_list' => Express::getTemplateSize(),
                'business_list' => Express::getBusiness(),
                'kd100_business_list' => Express::getKd100Business(),
            ]
        ];
    }

    //DELETE
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = Delivery::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已经删除',
            ];
        }
        $model->is_delete = 1;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    private function defaultData()
    {
        //省市区
        $commonDistrict = new CommonDistrict();
        $district = $commonDistrict->search();

        $new_district = [];
        foreach ($district as $k1 => $v1) {
            $new_district[$k1]['value'] = $v1['name']; //$v1['id'];
            $new_district[$k1]['parent_id'] = $v1['parent_id'];
            $new_district[$k1]['label'] = $v1['name'];
            $new_district[$k1]['level'] = $v1['level'];
            foreach ($v1['list'] as $k2 => $v2) {
                $new_district[$k1]['children'][$k2]['value'] = $v2['name']; //$v2['id'];
                $new_district[$k1]['children'][$k2]['parent_id'] = $v2['parent_id'];
                $new_district[$k1]['children'][$k2]['label'] = $v2['name'];
                $new_district[$k1]['children'][$k2]['level'] = $v2['level'];
                foreach ($v2['list'] as $k3 => $v3) {
                    $new_district[$k1]['children'][$k2]['children'][$k3]['value'] = $v3['name']; //$v3['id'];
                    $new_district[$k1]['children'][$k2]['children'][$k3]['parent_id'] = $v3['parent_id'];
                    $new_district[$k1]['children'][$k2]['children'][$k3]['label'] = $v3['name'];
                    $new_district[$k1]['children'][$k2]['children'][$k3]['level'] = $v3['level'];
                }
            }
        };
        return $new_district;
    }

    public function getExpressList()
    {
        $list = Express::getExpressList();

        $allData = Delivery::findAll([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0
        ]);
        foreach ($list as &$v) {
            foreach ($allData as $v1) {
                !isset($v['delivery']) && $v['delivery'] = [];
                $v1['express_id'] === $v['id'] && array_push($v['delivery'], [
                    'id' => $v1['id'],
                    'express_id' => $v1['express_id'],
                    'customer_account' => $v1['customer_account']
                ]);
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => "请求成功",
            'data' => [
                'list' => $list
            ]
        ];
    }
}
