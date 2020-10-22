<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/5/18
 * Time: 15:51
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\collect\collect_data;


use app\forms\common\collect\collect_api\Pdd;

/**
 * Class PddData
 * @package app\forms\common\collect\collect_data
 */
class PddData extends BaseData
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->api = new Pdd();
    }

    public function getItemId($url)
    {
        $id = $this->pregSubstr('/(\?goods_id=|&goods_id=)/', '/&/', $url);
        if (empty($id)) {
            throw new \Exception($url . '链接错误，没有包含商品id');
        }
        $itemId = $id[0];
        return $itemId;
    }

    public function handleData($data)
    {
        $jsonData = $data['data'];
        $goods = $jsonData['item'];
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
            'name' => $goods['goodsName'],
            'price' => $price['price'],
            'original_price' => $price['original_price'],
            'cost_price' => $price['cost_price'],
            'detail' => $newDesc ? $newDesc : '-',
            'cover_pic' => count($picList) >= 1 ? $picList[0]['pic_url'] : '',
            'pic_url' => $picList,
            'unit' => '件',
            'attr' => $handleAttr['attr'],

            'goods_num' => $handleAttr['goods_num'],
            'attrGroups' => $handleAttr['attrGroups'],
            'video_url' => $video,
            'use_attr' => empty($handleAttr['attrGroups']) ? 0 : 1,
            'member_price' => [],
        ];
        return $newData;
    }

    // 商品缩略图处理
    private function getPicList($goods)
    {
        $picList = [];
        foreach ($goods['banner'] as $item) {
            if (is_array($item)) {
                $url = $item['url'];
            } else {
                $url = $item;
            }
            $picList[] = [
                'pic_url' => $this->handleImg($this->changeImgUrl($url))
            ];
        }
        return $picList;
    }

    // 商品详情处理
    private function getDesc($goods)
    {
        $desc = '';
        if (isset($goods['goodsDesc'])) {
            $desc .= "<p>{$goods['goodsDesc']}</p>";
        }
        if (isset($goods['detail']) && is_array($goods['detail'])) {
//            rsort($goods['detail']);
            foreach ($goods['detail'] as $item) {
                $img = $this->handleImg($this->changeImgUrl($item['url']));
                $desc .= sprintf("<img src='%s' style='width: %s;height: %s;'></img>", $img, $item['width'], $item['height']);
            }
        }
        return $desc;
    }

    // 视频处理
    private function getVideo($goods)
    {
        if (isset($goods['video']) && !empty($goods['video']) && $goods['video'][0]['url']) {
            return $goods['video'][0]['url'];
        }
        return '';
    }

    public function getPrice($goods)
    {
        $price = 0;
        $originalPrice = 0;
        $costPrice = 0;

        if (isset($goods['maxNormalPrice'])) {
            $price = $goods['maxNormalPrice'];
        }

        if (isset($goods['marketPrice'])) {
            $originalPrice = $goods['marketPrice'];
            $costPrice = $goods['marketPrice'];
        }

        return [
            'price' => $price,
            'original_price' => $originalPrice,
            'cost_price' => $costPrice
        ];
    }

    // 规格处理
    public function getAttr($goods)
    {
        $checkedAttrList = [];
        $goodsNum = 0;
        $attrGroups = [];
        if (isset($goods['skus'])) {
            $temp = [];
            foreach ($goods['skus'] as $item) {
                $attrList = [];
                $picUrl = isset($item['thumbUrl']) ? $this->handleImg($this->changeImgUrl($item['thumbUrl'])) : '';
                foreach ($item['specs'] as $spec) {
                    if (!isset($temp[$spec['spec_key_id']])) {
                        $temp[$spec['spec_key_id']] = [];
                        $attrGroups[$spec['spec_key_id']] = [
                            'attr_group_name' => $spec['spec_key'],
                            'attr_list' => []
                        ];
                    }
                    if (!in_array($spec['spec_value_id'], $temp[$spec['spec_key_id']])) {
                        $temp[$spec['spec_key_id']][] = $spec['spec_value_id'];
                        $attrGroups[$spec['spec_key_id']]['attr_list'][] = [
                            'attr_name' => $spec['spec_value'],
                            'pic_url' => $picUrl
                        ];
                    }
                    $attrList[] = [
                        'attr_group_name' => $spec['spec_key'],
                        'attr_name' => $spec['spec_value']
                    ];
                }
                $checkedAttrList[] = [
                    'attr_list' => $attrList,
                    'price' => $item['normalPrice'],
                    'stock' => $item['quantity'],
                    'no' => '',
                    'weight' => '',
                ];
                $goodsNum += $item['quantity'];
            }
            $attrGroups = array_values($attrGroups);
        }
        return [
            'attr' => $checkedAttrList,
            'goods_num' => $goodsNum,
            'attrGroups' => $attrGroups
        ];
    }
}
