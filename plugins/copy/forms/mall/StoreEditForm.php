<?php
/**
 * link: 域名
 * copyright: Copyright (c) 2018 人人禾匠商城
 * author: wxf
 */

namespace app\plugins\copy\forms\mall;

use app\core\response\ApiCode;
use app\helpers\CurlHelper;
use app\models\Model;
use app\plugins\copy\models\CopyStore;;

class StoreEditForm extends Model
{
    use HttpForm;


    public $store_id;
    public $url;
    public $name;
    public $store_name;
    public $ver;


    public function rules()
    {
        return [
            [['store_id','url',"name"], 'required'],
            [['store_id','ver'], 'integer'],
            [['url',"name"], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '会员ID',
        ];
    }

    public function del($id){
        if(empty($id)){
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '非法操作',
                'data' => []
            ];
        }
        $model = CopyStore::findOne($id);
        if(!$model){
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '门店不存在',
                'data' => []
            ];
        }
        $model->is_delete = 1;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功',
        ];
    }


    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if(!$store = $this->getStoreInfo($this->url,$this->store_id,$this->ver)){
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '商城不存在，添加失败',
                'data' => []
            ];
        }
        try {

            $model = new CopyStore();
            $model->store_id = $this->store_id;
            $model->url = $this->url;
            $model->name = $this->name;
            $model->ver = $this->ver;
            $model->store_name = $this->ver == 4 ? $store['mall']['name']:$store['store_name'];
            $res = $model->save();
            if (!$res) {
                throw new \Exception("添加失败");
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {

            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ]
            ];
        }
    }
}
