<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/5/18
 * Time: 11:47
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\collect\collect_data;


use app\forms\common\collect\collect_api\Jd;

/**
 * Class JdData
 * @package app\forms\common\collect\collect_data
 */
class JdData extends BaseData
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->api = new Jd();
    }

    public function getItemId($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $id = $this->pregSubstr('/' . $host . '\//', '/.html/', $url);
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
        // 商品详情处理
        $newDesc = $this->getDesc($goods);
        // 商品图片处理
        $picList = $this->getPicList($goods);
        // 视频处理
        $video = $this->getVideo($goods);
        // 规格处理
        $handleAttr = $this->getAttr($goods);
        $newData = [
            'name' => $goods['name'],
            'price' => $goods['price'],
            'original_price' => $goods['originalPrice'],
            'cost_price' => $goods['originalPrice'],
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
    public function getDesc($goods)
    {
        $desc = '';
        foreach ($goods['descImgs'] as $item) {
            try {
                $img = $this->handleImg($this->changeImgUrl($item));
                list($width, $height) = getimagesize($img);
                $desc .= sprintf("<img src='%s' style='width: %s;height: %s;'></img>", $img, $width . 'px', $height . 'px');
            } catch (\Exception $exception) {
            }
        }
        return $desc;
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
        if (isset($goods['saleProp']) && isset($goods['skuProps'])) {
            foreach ($goods['saleProp'] as $index => $item) {
                if ($item == '') {
                    continue;
                }
                $attrList = [];
                foreach ($goods['skuProps'][$index] as $value) {
                    if ($value == '') {
                        continue;
                    }
                    $attrList[] = [
                        'attr_name' => $value,
                        'pic_url' => '',
                    ];
                }
                if (empty($attrList)) {
                    continue;
                }
                $attrGroup = [
                    'attr_group_name' => $item,
                    'attr_list' => $attrList,
                ];
                $attrGroups[] = $attrGroup;
            }
        }
        if (isset($goods['saleProp']) && isset($goods['sku'])) {
            foreach ($goods['sku'] as $item) {
                $attrList = [];
                foreach ($goods['saleProp'] as $key => $value) {
                    if (!isset($item[$key]) || $item[$key] == '') {
                        continue;
                    }
                    $attrList[] = [
                        'attr_group_name' => $value,
                        'attr_name' => $item[$key]
                    ];
                }
                $checkedAttrList[] = [
                    'attr_list' => $attrList,
                    'stock' => 0,
                    'price' => $item['price'] ?? 0,
                    'no' => '',
                    'weight' => ''
                ];
            }
        }
        return [
            'attr' => $checkedAttrList,
            'goods_num' => $goodsNum,
            'attrGroups' => $attrGroups
        ];
    }
}
