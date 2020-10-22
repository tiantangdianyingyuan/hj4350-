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
use app\plugins\copy\models\CopyStoreGoods;

;

class GoodsListForm extends Model
{
    use HttpForm;


    public $store_id;
    public $cat_id;
    public $status;


    public function rules()
    {
        return [
            [['store_id',"cat_id"], 'required'],
            [['store_id',"cat_id",'status'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'store_id' => '门店',
            'cat_id' => '分类',
        ];
    }

    public function getHomeGoods()
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
        try {
            $goodsList = $this->getHOmeGoodsList($store->store_id,$store->url,$store->ver);

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ]
            ];
        }

        $storeGoodsList = CopyStoreGoods::find()
            ->where(["is_delete"=>0,"store_id"=>$store->id,"cat_id"=>$this->cat_id])
            ->select("id,cat_id,name,goods_id,is_copy,pic_url")->asArray()->all();
        $goodsIds = array_column($storeGoodsList,"goods_id");
        $count = 0;
        foreach($goodsList as &$goods){
            if(!in_array($goods['id'],$goodsIds)){

                if($store->ver == 4){
                    $pic_url = $goods['cover_pic'];
                }else{
                    $pic_url = $goods['pic_url'];
                }

                $model = new CopyStoreGoods();
                $model->store_id = $store->id;
                $model->goods_id = $goods['id'];
                $model->cat_id = $this->cat_id;
                $model->name = $goods['name'];
                $model->pic_url = $pic_url;
                $model->goods_info = json_encode($goods,true);
                $model->is_copy = 0;

                $res = $model->save();
                if($res){
                    $count++;
                    $storeGoodsList[] = [
                        'is_copy' => 0,
                        'pic_url' => $goods['pic_url'],
                        'name' => $goods['name'],
                        'goods_id' => $goods['id'],
                        'cat_id' => $this->cat_id,
                    ];
                }
            }
        }


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => "加载成功，更新商品{$count}个",
            'data' => [
                'list' =>$storeGoodsList,
            ],
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
        try {
            $goodsList = $this->getGoodsListByCatId($store->store_id,$store->url,$this->cat_id,$store->ver);

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ]
            ];
        }

        $storeGoodsList = CopyStoreGoods::find()
            ->where(["is_delete"=>0,"store_id"=>$store->id,"cat_id"=>$this->cat_id])
            ->select("id,cat_id,name,goods_id,is_copy,pic_url")->asArray()->all();
        $goodsIds = array_column($storeGoodsList,"goods_id");
        $count = 0;
        foreach($goodsList as &$goods){
            if(!in_array($goods['id'],$goodsIds)){

                if($store->ver == 4){
                    $pic_url = $goods['cover_pic'];
                }else{
                    $pic_url = $goods['pic_url'];
                }

                $model = new CopyStoreGoods();
                $model->store_id = $store->id;
                $model->goods_id = $goods['id'];
                $model->cat_id = $this->cat_id;
                $model->name = $goods['name'];
                $model->pic_url = $pic_url;
                $model->goods_info = json_encode($goods,true);
                $model->is_copy = 0;

                $res = $model->save();
                if($res){
                    $count++;
                    $storeGoodsList[] = [
                        'is_copy' => 0,
                        'pic_url' => $goods['pic_url'],
                        'name' => $goods['name'],
                        'goods_id' => $goods['id'],
                        'cat_id' => $this->cat_id,
                    ];
                }
            }
        }


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => "加载成功，更新商品{$count}个",
            'data' => [
                'list' =>$storeGoodsList,
            ],
        ];
    }
}
