<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/5/18
 * Time: 11:37
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\collect\collect_data;


abstract class AliData extends BaseData
{
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
            'name' => $goods['title'],
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
        foreach ($goods['images'] as $item) {
            $picList[] = [
                'pic_url' => $this->handleImg($this->changeImgUrl($item))
            ];
        }
        return $picList;
    }

    // 商品详情处理
    protected function getDesc($goods)
    {
        $res = '';
        if (isset($goods['descImgs'])) {
            $desc = [];
            $pattern = [];
            foreach ($goods['descImgs'] as $item) {
                $desc[] = sprintf("<image src='%s'>", $this->handleImg($this->changeImgUrl($item)));
                $item = str_replace('/', '\/', $item);
                $pattern[] = sprintf('/<img.*?>%s<\/img>/', $item);
            }
            $res = preg_replace($pattern, $desc, $goods['desc']);
            $res = str_replace('<image', '<img', $res);
        }
        return $res;
    }

    // 视频处理
    private function getVideo($goods)
    {
        if (isset($goods['videos']) && !empty($goods['videos']) && $goods['videos'][0]['url']) {
            return $goods['videos'][0]['url'];
        }
        return '';
    }

    public function getPrice($goods)
    {
        $price = 0;
        $originalPrice = 0;
        $costPrice = 0;

        if (isset($goods['priceRange'])) {
            if (is_numeric($goods['priceRange'])) {
                $price = $goods['priceRange'];
            } else {
                $res = explode('-', $goods['priceRange']);
                $price = $res[0];
            }
        }

        if (isset($goods['marketPriceRange'])) {
            if (is_numeric($goods['marketPriceRange'])) {
                $originalPrice = $goods['marketPriceRange'];
                $costPrice = $goods['marketPriceRange'];
            } else {
                $res = explode('-', $goods['marketPriceRange']);
                $originalPrice = $res[0];
                $costPrice = $res[0];
            }
        }

        return [
            'price' => $price,
            'original_price' => $originalPrice,
            'cost_price' => $costPrice
        ];
    }

    // 规格处理
    private function getAttr($goods)
    {
        $checkedAttrList = [];
        $goodsNum = 0;
        $attrGroups = [];
        if (isset($goods['props']) && !empty($goods['props']) && isset($goods['sku']) && !empty($goods['sku'])) {
            $count = 0;
            foreach ($goods['props'] as $item) {
                $attrList = [];
                $flag = false;
                foreach ($item['values'] as $value) {
                    if (isset($value['image']) && $value['image'] && !$flag) {
                        $flag = true;
                    }
                    $attrList[] = [
                        'attr_name' => $value['name'],
                        'pic_url' => isset($value['image']) && $value['image'] ? $this->handleImg($this->changeImgUrl($value['image'])) : '',
                        'vid' => $value['vid']
                    ];
                }
                $attrGroup = [
                    'attr_group_name' => $item['name'],
                    'attr_list' => $attrList,
                    'pid' => $item['pid'],
                    'count' => $count
                ];
                $count++;
                if ($flag) {
                    array_unshift($attrGroups, $attrGroup);
                } else {
                    $attrGroups[] = $attrGroup;
                }
            }
            $checkedAttrList = $this->getAttrList($attrGroups, 0);
            $skuList = [];
            foreach ($goods['sku'] as $item) {
                if (isset($item['propPath']) && $item['propPath']) {
                    $skuList[$item['propPath']] = $item;
                }
            }

            foreach ($checkedAttrList as $index => $item) {
                $num = 0;
                $price = 0;
                if (isset($skuList[$item['dataId']])) {
                    $num = $skuList[$item['dataId']]['quantity'];
                    $price = $skuList[$item['dataId']]['price'];
                }
                $checkedAttrList[$index]['stock'] = $num;
                $checkedAttrList[$index]['price'] = $price;
                $checkedAttrList[$index]['no'] = '';
                $checkedAttrList[$index]['weight'] = '';
                $goodsNum += $num;
            }
        }
        return [
            'attr' => $checkedAttrList,
            'goods_num' => $goodsNum,
            'attrGroups' => $attrGroups
        ];
    }

    private function getAttrList($list, $level, $attrList = [], $n = [], $newList = [])
    {
        if (isset($list[$level]['attr_list'])) {
            foreach ($list[$level]['attr_list'] as $key => $item) {
                $a = [];
                $newAttrList = $attrList;
                $dataId = $n;
                $dataId[$list[$level]['count']] = $list[$level]['pid'] . ':' . $item['vid'];
                $a['attr_group_name'] = $list[$level]['attr_group_name'];
                $a['attr_name'] = $item['attr_name'];
                array_push($newAttrList, $a);
                if ($level < count($list) - 1) {
                    $newList = $this->getAttrList($list, $level + 1, $newAttrList, $dataId, $newList);
                } else {
                    ksort($dataId);
                    $newList[] = [
                        'attr_list' => $newAttrList,
                        'dataId' => implode(';', $dataId)
                    ];
                }
            }
        }
        return $newList;
    }
}
