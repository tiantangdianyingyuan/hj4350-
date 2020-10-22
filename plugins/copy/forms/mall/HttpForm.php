<?php
/**
 * link: 域名
 * copyright: Copyright (c) 2018 人人禾匠商城
 * author: wxf
 */

namespace app\plugins\copy\forms\mall;

use app\helpers\CurlHelper;

trait HttpForm
{

    public function getStoreInfo($url,$store_id,$v)
    {

        if($v == 4){
            $param = [
               '_mall_id' =>$this->store_id,
                "r" => "api/index/config",
                "_version" => "2.8.9",
                "_platform" => "wx",
            ];
        }else{
            $param = [
                "store_id" => $this->store_id,
                "r" => "api/default/store",
                "_version" => "2.8.9",
                "_platform" => "wx",
            ];
        }

        try {
            $curl = CurlHelper::getInstance();
            $data = $curl->httpGet($this->url . "/web/index.php", $param);
            if ($data['code'] == 0) {
                return $data["data"];
            }
            throw new \Exception('商城不存在');
        }catch (\Exception $e){
            return false;
        }
        return false;
    }

    /**
     * 获取商品分类
     * @throws \Exception
     */
    public function getGoodsCat($url,$store_id,$ver)
    {
        if($ver == 4){
            $param = [
                "_mall_id" =>$store_id,
                "r" => "api/cat/list",
                "_version" => "2.8.9",
                "_platform" => "wx",
            ];
        }else{
            $param = [
                "store_id" =>$store_id,
                "r" => "api/default/cat-list",
                "_version" => "2.8.9",
                "_platform" => "wx",
            ];
        }
        $curl = CurlHelper::getInstance();
        $data = $curl->httpGet($url. "/web/index.php", $param);
        if ($data['code'] == 0) {
            $data = $this->parseCat($data['data']);
            return $data;
        }
        return [];
    }

    public function parseCat($list)
    {
        $data = [];
        foreach ($list['list'] as $cat) {
            if (isset($cat['list'])) {
                $data = array_merge($data, $this->parseCat($cat));
                unset($cat['list']);
            }
            $data[] = $cat;
        }
        return $data;
    }


    public function getHOmeGoodsList($store_id,$url, $ver)
    {

        $param =  [
        "_mall_id" => $store_id,
        "r" => "api/index/index",
        "page" => 0,
        "_version" => "2.8.9",
        "_platform" => "wx",
    ];

        $curl = CurlHelper::getInstance();
        $data = $curl->httpGet($url . "/web/index.php", $param);

        $goods = [];

        if ($data['code'] == 0 && isset($data['data']['home_pages']) ) {
            if(isset($data['data']['type']) && $data['data']['type'] == "diy" ){

                foreach($data['data']['home_pages']['navs'] as $item){
                    if(count($item['template']['data'])){
                        foreach($item['template']['data'] as $val){
                            if($val['id'] == "goods" && count($val['data']['catList'])){
                                foreach($val['data']['catList'] as $cat){
                                    $goods = array_merge($goods,$cat['goodsList']);
                                }
                            }
                        }
                    }
                }

            }else{
                foreach($data['data']['home_pages'] as $item) {
                    if ($item['key'] == "cat" && count($item['goods'])) {
                        $goods = array_merge($goods,$item['goods']);
                    }
                }
            }



            return $goods;
        }
        return [];
    }


    public function getGoodsListByCatId($store_id,$url,$cat_id, $ver,$page = 1)
    {

        if($ver){

               $param =  [
                    "_mall_id" => $store_id,
                    "cat_id" => $cat_id,
                    "r" => "api/default/goods-list",
                    "page" => $page,
                    "_version" => "2.8.9",
                    "_platform" => "wx",
                ];
        }else{
            $param =  [
                "store_id" => $store_id,
                "cat_id" => $cat_id,
                "r" => "api/default/goods-list",
                "page" => $page,
                "_version" => "2.8.9",
                "_platform" => "wx",
            ];
        }

        $curl = CurlHelper::getInstance();
        $data = $curl->httpGet($url . "/web/index.php", $param);
        if ($data['code'] == 0) {
            $goods = $data["data"]['list'];
            if (count($goods) > 0) {
                $goods = array_merge($goods,
                    $this->getGoodsListByCatId($store_id,$url,$cat_id, $ver,$page + 1)
                );
            }
            return $goods;
        }
        return [];
    }


    public function getGoodsDetail($store,$goods_id)
    {

        if($store->ver ==4 ){
            $param = [
                "_mall_id" => $store->store_id,
                "id" => $goods_id,
                "r" => "api/goods/detail",
                "_version" => "2.8.9",
                "_platform" => "wx",
            ];
        }else{
            $param = [
                "store_id" => $store->store_id,
                "id" => $goods_id,
                "r" => "api/default/goods",
                "_version" => "2.8.9",
                "_platform" => "wx",
            ];
        }
        $curl = CurlHelper::getInstance();
        $data = $curl->httpGet($store->url."/web/index.php",$param);
        if($data['code'] == 0){
            return $data['data'];
        }
        return [];
    }




    public function getHomePage($store_id,$url,$ver)
    {

        $param =  [
            "_mall_id" => $store_id,
            "r" => "api/index/index",
            "page" => 0,
            "_version" => "2.8.9",
            "_platform" => "wx",
        ];
        $curl = CurlHelper::getInstance();
        $data = $curl->httpGet($url . "/web/index.php", $param);
        if ($data['code'] == 0 && $data['data']['type'] == 'diy') {

            return $data['data']['home_pages'] ;
        }
        return [];
    }

}
