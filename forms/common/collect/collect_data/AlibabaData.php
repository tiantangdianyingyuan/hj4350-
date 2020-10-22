<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/5/19
 * Time: 10:31
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\collect\collect_data;


use app\forms\common\collect\collect_api\Alibaba;
use app\helpers\CurlHelper;
use yii\helpers\Json;

/**
 * Class AlibabaData
 * @package app\forms\common\collect\collect_data
 */
class AlibabaData extends BaseData
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->api = new Alibaba();
    }

    public function getItemId($url)
    {
        $id = $this->pregSubstr('/1688.com\/[a-z]+\//', '/.html/', $url);
        if (empty($id)) {
            throw new \Exception($url . '链接错误，没有包含商品id');
        }
        $itemId = $id[0];
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
    public function getDesc($goods)
    {
        if (isset($goods['descImgs'])) {
            $desc = [];
            foreach ($goods['descImgs'] as $item) {
                $desc[] = $this->handleImg($this->changeImgUrl($item));
            }
            return str_replace($goods['descImgs'], $desc, $goods['desc']);
        } elseif (isset($goods['descUrl'])) {
            $url = $this->pregSubstr('/(\?url=|&url=)/', '/&/', $goods['descUrl']);
            if (empty($url)) {
                return '';
            }
            $res = CurlHelper::getInstance()->httpGet($url[0]);
            $res = $this->pregSubstr('/(\{)/', '/}/', $res);
            if (empty($res)) {
                return '';
            }
            $json = Json::decode(iconv('GBK', 'UTF-8', '{' . $res[0] . '}'), true);
            if (!isset($json['content'])) {
                return '';
            }
            return $json['content'];
        } else {
            return '';
        }
    }

    // 视频处理
    private function getVideo($goods)
    {
        if (isset($goods['videoInfo']) && !empty($goods['videoInfo']) && isset($goods['videoInfo']['videoUrl'])) {
            if (is_array($goods['videoInfo']['videoUrl'])) {
                return isset($goods['videoInfo']['videoUrl']['android']) ? $goods['videoInfo']['videoUrl']['android'] : '';
            } else {
                return $goods['videoInfo']['videoUrl'];
            }
        }
        return '';
    }

    // 价格处理
    public function getPrice($goods)
    {
        $price = 0;
        $originalPrice = 0;
        $costPrice = 0;

        if (isset($goods['showPriceRanges']) && !empty($goods['showPriceRanges'])) {
            $price = $originalPrice = $costPrice = $goods['showPriceRanges'][0]['price'];
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
        if (isset($goods['fenxiao']) && isset($goods['fenxiao']['skuMap']) && isset($goods['fenxiao']['skuProps'])) {
            $skuMap = $goods['fenxiao']['skuMap'];
            $skuProps = $goods['fenxiao']['skuProps'];
        } elseif (isset($goods['skuMap']) && isset($goods['skuProps'])) {
            $skuMap = $goods['skuMap'];
            $skuProps = $goods['skuProps'];
        } else {
            $skuMap = [];
            $skuProps = [];
        }
        $count = 0;
        foreach ($skuProps as $item) {
            $attrList = [];
            $flag = false;
            foreach ($item['value'] as $value) {
                if (isset($value['imageUrl']) && $value['imageUrl'] && !$flag) {
                    $flag = true;
                }
                $attrList[] = [
                    'attr_name' => $value['name'],
                    'pic_url' => isset($value['imageUrl']) && $value['imageUrl'] ? $this->handleImg($this->changeImgUrl($value['imageUrl'])) : '',
                ];
            }
            $attrGroup = [
                'attr_group_name' => $item['prop'],
                'attr_list' => $attrList,
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

        foreach ($checkedAttrList as $index => $item) {
            $num = 0;
            $price = 0;
            if (isset($skuMap[$item['dataId']])) {
                $num = $skuMap[$item['dataId']]['canBookCount'];
                if (isset($skuMap[$item['dataId']]['price'])) {
                    $price = $skuMap[$item['dataId']]['price'];
                } else {
                    $res = $this->getPrice($goods);
                    $price = $res['price'];
                }
            }
            $checkedAttrList[$index]['stock'] = $num;
            $checkedAttrList[$index]['price'] = $price;
            $checkedAttrList[$index]['no'] = '';
            $checkedAttrList[$index]['weight'] = '';
            $goodsNum += $num;
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
                $dataId[$list[$level]['count']] = $item['attr_name'];
                $a['attr_group_name'] = $list[$level]['attr_group_name'];
                $a['attr_name'] = $item['attr_name'];
                array_push($newAttrList, $a);
                if ($level < count($list) - 1) {
                    $newList = $this->getAttrList($list, $level + 1, $newAttrList, $dataId, $newList);
                } else {
                    ksort($dataId);
                    $newList[] = [
                        'attr_list' => $newAttrList,
                        'dataId' => implode('&gt;', $dataId)
                    ];
                }
            }
        }
        return $newList;
    }
}
