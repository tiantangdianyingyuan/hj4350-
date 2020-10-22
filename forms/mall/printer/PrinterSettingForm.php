<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\printer;

use app\core\response\ApiCode;
use app\forms\mall\store\StoreForm;
use app\models\Model;
use app\models\Printer;
use app\models\PrinterSetting;
use yii\helpers\ArrayHelper;

class PrinterSettingForm extends Model
{
    public $id;
    public $page_size;
    public $status;

    public function rules()
    {
        return [
            [['id', 'page_size', 'status'], 'integer'],
            [['page_size'], 'default', 'value' => 10],
        ];
    }

    //GET
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = PrinterSetting::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0
        ])->with('printer', 'store');

        is_null($this->status) || $query->andWhere(['status' => $this->status]);
        $list = $query->orderBy('id desc')->page($pagination, $this->page_size)->all();
        $newList = [];
        if ($list) {
            /**
             * @var  $k
             * @var PrinterSetting $v
             */
            foreach ($list as $k => $v) {
                $printerItem = ArrayHelper::toArray($v);
                $printerItem['printer'] = $v->printer ? ArrayHelper::toArray($v->printer) : [];
                $printerItem['store_name'] = $v->store ? $v->store->name : '全门店通用';
                $printerItem['type'] = json_decode($v['type'], true);
                $printerItem['show_type'] = \yii\helpers\BaseJson::decode($v->show_type);
                $printerItem['order_send_type'] = \yii\helpers\BaseJson::decode($v->order_send_type);
                $newList[] = $printerItem;
            };
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
            ]
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $list = PrinterSetting::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
            'id' => $this->id,
        ])->one();
        if ($list) {
            $list = ArrayHelper::toArray($list);
            $list['type'] = json_decode($list['type'], true);
            $list['show_type'] = \yii\helpers\BaseJson::decode($list['show_type']);
            $list['order_send_type'] = \yii\helpers\BaseJson::decode($list['order_send_type']);
        }
        $select = Printer::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
        ])->all();


        $storeForm = new StoreForm();
        $stores = $storeForm->getAllStore();
        $stores = $stores ? ArrayHelper::toArray($stores) : [];
        array_unshift($stores, [
            'id' => 0,
            'name' => '全门店订单'
        ]);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'select' => $select,
                'stores' => $stores
            ]
        ];
    }

    //DELETE
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = PrinterSetting::findOne([
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
}
