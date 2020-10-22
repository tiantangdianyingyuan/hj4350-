<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-08-06
 * Time: 11:58
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\collect\collect_data;


use app\forms\common\collect\collect_api\Suning;
use app\helpers\CurlHelper;
use yii\helpers\Json;

class SuningData extends BaseData
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->api = new Suning();
    }

    public function getItemId($url)
    {
        $id = $this->pregSubstr('/product.suning.com\//', '/.html/', $url);
        if (empty($id)) {
            throw new \Exception($url . '链接错误，没有包含商品id');
        }
        $params = explode('/', $id[0]);
        $this->api->shopid = $params['0'];
        $itemId = $params[1];
        return $itemId;
    }

    public function handleData($data)
    {
        $goods = $data['data'];
        // 价格处理
        $price = $this->getPrice($goods);
        // 商品图片处理
        $picList = $this->getPicList($goods);
        // 视频处理
        $video = $this->getVideo($goods);
        // 商品详情处理
        $newDesc = $this->getDesc($goods);
        // 规格处理
        $handleAttr = $this->getAttr($goods);
        $newData = [
            'name' => $goods['title'],
            'price' => $price['price'],
            'original_price' => $price['original_price'],
            'cost_price' => $price['cost_price'],
            'detail' => $newDesc ? $newDesc : '-',
            'cover_pic' => count($picList) >= 1 ? $picList[0]['pic_url'] : '',
            'pic_url' => $picList,
            'unit' => isset($goods['unit']) && $goods['unit'] ? $goods['unit'] : '件',
            'attr' => $handleAttr['attr'],

            'goods_num' => $handleAttr['goods_num'],
            'attrGroups' => $handleAttr['attrGroups'],
            'video_url' => $video,
            'use_attr' => empty($handleAttr['attrGroups']) ? 0 : 1,
            'member_price' => [],
        ];
        return $newData;
    }

    // 价格处理
    public function getPrice($goods)
    {
        $price = 0;
        $originalPrice = 0;
        $costPrice = 0;

        if (isset($goods['price'])) {
            $price = $originalPrice = $costPrice = $goods['price'];
        }

        return [
            'price' => $price,
            'original_price' => $originalPrice,
            'cost_price' => $costPrice
        ];
    }

    // 商品缩略图处理
    private function getPicList($goods)
    {
        if (!isset($goods['images'])) {
            return [];
        }
        $picList = [];
        foreach ($goods['images'] as $item) {
            $picList[] = [
                'pic_url' => $this->handleImg($this->changeImgUrl($item))
            ];
        }
        return $picList;
    }

    // 商品详情处理
    private function getDesc($goods)
    {
        $res = '';
        if (isset($goods['desc'])) {
            preg_match_all('/(http|https):{1}\D{2,4}.*?\.(jpeg|jpg|png)/', $goods['desc'], $res);

            $desc = [];
            foreach ($res[0] as $item) {
                $desc[] = $this->handleImg($this->changeImgUrl($item));
            }
            $res = str_replace($res[0], $desc, $goods['desc']);
            $res = str_replace('src2', 'src', $goods['desc']);
        }
        return $res;
    }

    // 视频处理
    private function getVideo($goods)
    {
        return '';
    }

    // 规格处理
    public function getAttr($goods)
    {
        $checkedAttrList = [];
        $goodsNum = 0;
        $attrGroups = [];
        return [
            'attr' => $checkedAttrList,
            'goods_num' => $goodsNum,
            'attrGroups' => $attrGroups
        ];
    }
}