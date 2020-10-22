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
use app\plugins\copy\models\CopyStore;
use app\plugins\copy\models\CopyStoreCat;

;

class CatListForm extends Model
{
    use HttpForm;


    public $store_id;


    public function rules()
    {
        return [
            [['store_id'], 'required'],
            [['store_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'store_id' => '门店id',
        ];
    }


    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $store = CopyStore::findOne($this->store_id);
        if(!$store){
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '商城不存在',
                'data' => []
            ];
        }
        $catIds = CopyStoreCat::find()->where(["is_delete"=>0,"store_id"=>$store->id])
            ->select("id,cat_id,name")->asArray()->indexBy('cat_id')->all();
        $catList = $this->getGoodsCat($store->url,$store->store_id,$store->ver);
        $count = 0;
        foreach($catList as $cat){
            if(!isset($catIds[$cat['id']])){
                $catModel = new CopyStoreCat();
                $catModel->store_id = $store->id;
                $catModel->cat_id = $cat['id'];
                $catModel->name = $cat['name'];
                $res = $catModel->save();
                if($res){
                    $count++;
                    $catIds[$cat['id']] = $cat;
                }

            }
        }
        if($count > 0){
            $catIds = CopyStoreCat::find()->where(["is_delete"=>0,"store_id"=>$store->id])
                ->select("id,cat_id,name")->asArray()->indexBy('cat_id')->all();
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => "加载成功，新增{$count}个分类",
            'data' => [
                'list' =>$catIds,
            ],
        ];
    }
}
