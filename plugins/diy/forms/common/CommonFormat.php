<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\forms\common;

class CommonFormat
{
    public function nav()
    {
        return [
            'navType' => 'fixed',
            'aloneNum' => 3,
            'lineNum' => 2,
            'swiperType' => 'circle',
            'swiperColor' => '#409EFF',
            'swiperNoColor' => '#a9a9a9',
            'color' => '#353535',
            'rows' => 1,
            'columns' => 4,
            'scroll' => true,
            'navs' => array_fill(0, 4, [
                'icon' => '',
                'name' => '导航一',
                'url' => '',
                'openType' => '',
                'labelType' => '',
                'labelName' => '',
            ]),
            'showImg' => false,
            'backgroundColor' => '#ffffff',
            'backgroundPicUrl' => '',
            'position' => 5,
            'mode' => 1,
            'backgroundHeight' => 100,
            'backgroundWidth' => 100,
            'modeType' => 'img',
        ];
    }

    public function pintuan()
    {
        return [
            'buttonColor' => '#ff4544',
            'list' => [],
            'listStyle' => 1,
            'textStyle' => 1,
            'fill' => 1,
            'goodsCoverProportion' => '1-1',
            'goodsStyle' => 1,
            'showGoodsName' => true,
            'showBuyBtn' => true,
            'buyBtnStyle' => 1,
            'buyBtnText' => '去拼团',
            'showGoodsTag' => false,
            'customizeGoodsTag' => false,
            'goodsTagPicUrl' => '',
            'showImg' => false,
            'backgroundColor' => '#fff',
            'backgroundPicUrl' => '',
            'position' => 5,
            'mode' => 1,
            'backgroundHeight' => 100,
            'backgroundWidth' => 100,
            'isUnderLinePrice' => true,
        ];
    }

    public function miaosha()
    {
        return [
            'buttonColor' => '#ff4544',
            'list' => [],
            'listStyle' => 1,
            'fill' => 1,
            'goodsCoverProportion' => '1-1',
            'goodsStyle' => 1,
            'textStyle' => 1,
            'showGoodsName' => true,
            'showBuyBtn' => true,
            'buyBtnStyle' => 1,
            'buyBtnText' => '马上秒',
            'showGoodsTag' => false,
            'customizeGoodsTag' => false,
            'goodsTagPicUrl' => '',
            'showImg' => false,
            'backgroundColor' => '#fff',
            'backgroundPicUrl' => '',
            'position' => 5,
            'mode' => 1,
            'backgroundHeight' => 100,
            'backgroundWidth' => 100,
            'isUnderLinePrice' => true,
        ];
    }

    public function booking()
    {
        return [
            'buttonColor' => '#ff4544',
            'list' => [],
            'listStyle' => 1,
            'fill' => 1,
            'goodsCoverProportion' => '1-1',
            'goodsStyle' => 1,
            'textStyle' => 1,
            'showGoodsName' => true,
            'showBuyBtn' => true,
            'buyBtnStyle' => 1,
            'buyBtnText' => '预约',
            'showGoodsTag' => false,
            'customizeGoodsTag' => false,
            'goodsTagPicUrl' => '',
            'showImg' => false,
            'backgroundColor' => '#fff',
            'backgroundPicUrl' => '',
            'position' => 5,
            'mode' => 1,
            'backgroundHeight' => 100,
            'backgroundWidth' => 100,
            'isUnderLinePrice' => true,
        ];
    }

    public function bargain()
    {
        return [
            'buttonColor' => '#ff4544',
            'list' => [],
            'listStyle' => 1,
            'fill' => 1,
            'goodsCoverProportion' => '1-1',
            'goodsStyle' => 1,
            'textStyle' => 1,
            'showGoodsName' => true,
            'showBuyBtn' => true,
            'buyBtnStyle' => 1,
            'buyBtnText' => '去参与',
            'showGoodsTag' => false,
            'customizeGoodsTag' => false,
            'goodsTagPicUrl' => '',
            'showImg' => false,
            'backgroundColor' => '#fff',
            'backgroundPicUrl' => '',
            'position' => 5,
            'mode' => 1,
            'backgroundHeight' => 100,
            'backgroundWidth' => 100,
            'isUnderLinePrice' => true,
        ];
    }

    public function integralmall()
    {
        return [
            'showCoupon' => true,
            'showGoods' => true,
            'couponColor' => '#ffffff',
            'couponPicUrl' => '<?=$baseAssetsUrl?>/images/coupon-background.png',
            'buttonColor' => '#ff4544',
            'list' => [],
            'listStyle' => 1,
            'fill' => 1,
            'goodsCoverProportion' => '1-1',
            'goodsStyle' => 1,
            'textStyle' => 1,
            'showGoodsName' => true,
            'showBuyBtn' => true,
            'buyBtnStyle' => 1,
            'buyBtnText' => '立即兑换',
            'showGoodsTag' => false,
            'customizeGoodsTag' => false,
            'goodsTagPicUrl' => '',
            'showImg' => false,
            'backgroundColor' => '#fff',
            'backgroundPicUrl' => '',
            'position' => 5,
            'mode' => 1,
            'backgroundHeight' => 100,
            'backgroundWidth' => 100,
            'isUnderLinePrice' => true,
        ];
    }

    public function lottery()
    {
        return [
            'buttonColor' => '#ff4544',
            'list' => [],
            'listStyle' => 1,
            'fill' => 1,
            'goodsCoverProportion' => '1-1',
            'goodsStyle' => 1,
            'textStyle' => 1,
            'showGoodsName' => true,
            'showBuyBtn' => true,
            'buyBtnStyle' => 1,
            'buyBtnText' => '去参与',
            'showGoodsTag' => false,
            'customizeGoodsTag' => false,
            'goodsTagPicUrl' => '',
            'showImg' => false,
            'backgroundColor' => '#fff',
            'backgroundPicUrl' => '',
            'position' => 5,
            'mode' => 1,
            'backgroundHeight' => 100,
            'backgroundWidth' => 100,
            'isUnderLinePrice' => true,
        ];
    }

    public function goods()
    {
        return [
            'showCat' => false,
            'catPosition' => 'top',
            'catStyle' => 1,
            'catList' => [],
            'list' => [],
            'addGoodsType' => 0,
            'goodsLength' => 10,
            'listStyle' => 1,
            'goodsCoverProportion' => '1-1',
            'fill' => 1,
            'goodsStyle' => 1,
            'textStyle' => 1,
            'showGoodsName' => true,
            'showGoodsPrice' => true,
            'showBuyBtn' => true,
            'buyBtn' => 'cart',
            'buyBtnStyle' => 1,
            'buyBtnText' => '购买',
            'buttonColor' => '#ff4544',
            'showGoodsTag' => false,
            'customizeGoodsTag' => false,
            'goodsTagPicUrl' => '',
            'showImg' => false,
            'backgroundColor' => '#fff',
            'backgroundPicUrl' => '',
            'position' => 5,
            'mode' => 1,
            'backgroundHeight' => 100,
            'backgroundWidth' => 100,
            'isUnderLinePrice' => true,
        ];
    }

    public function advance()
    {
        return [
            'buttonColor' => '#ff4544',
            'list' => [],
            'listStyle' => 1,
            'fill' => 1,
            'goodsCoverProportion' => '1-1',
            'goodsStyle' => 1,
            'textStyle' => 1,
            'showGoodsName' => true,
            'showBuyBtn' => true,
            'buyBtnStyle' => 1,
            'buyBtnText' => '抢购',
            'showGoodsTag' => false,
            'customizeGoodsTag' => false,
            'goodsTagPicUrl' => '',
            'goodsIndex' => 0,
            'start_x' => 0,
            'showImg' => false,
            'backgroundColor' => '#fff',
            'backgroundPicUrl' => '',
            'position' => 5,
            'mode' => 1,
            'backgroundHeight' => 100,
            'backgroundWidth' => 100,
            'isUnderLinePrice' => true,
        ];
    }

    public function coupon()
    {
        $mallUrl = \Yii::$app->request->hostInfo
            . \Yii::$app->request->baseUrl
            . '/statics/img/app';
        return [
            'addType' => '',
            'has_hide' => false,
            'coupons' => [],
            'couponBg' => '#D9BC8B',
            'couponBgType' => 'pure',
            'textColor' => '#ffffff',
            'receiveBg' => $mallUrl . '/coupon/icon-coupon-no.png',
            'unclaimedBg' => $mallUrl . '/coupon/icon-coupon-index.png',
            'showImg' => false,
            'backgroundColor' => '#fff',
            'backgroundPicUrl' => '',
            'position' => 5,
            'mode' => 1,
            'backgroundHeight' => 100,
            'backgroundWidth' => 100,
            'has_limit' => '',
            'limit_num' => '',
        ];
    }

    public function flashsale()
    {
        return [
            'buttonColor' => '#ff4544',
            'list' => [],
            'listStyle' => 1,
            'fill' => 1,
            'goodsCoverProportion' => '1-1',
            'goodsStyle' => 1,
            'textStyle' => 1,
            'showGoodsName' => true,
            'showBuyBtn' => true,
            'buyBtnStyle' => 1,
            'buyBtnText' => '马上抢',
            'showGoodsTag' => false,
            'customizeGoodsTag' => false,
            'goodsTagPicUrl' => '',
            'showImg' => false,
            'backgroundColor' => '#fff',
            'backgroundPicUrl' => '',
            'position' => 5,
            'mode' => 1,
            'backgroundHeight' => 100,
            'backgroundWidth' => 100,
            'showProgressBar' => false,
            'isUnderLinePrice' => true,
        ];
    }

    public function handleOne($template)
    {
        if (isset($template['id']) && method_exists($this, $method = str_replace('-', '', $template['id']))) {
            $data = $template['data'];
            $default = $this->$method();

            $func = function ($newData, $default) use (&$func) {
                foreach ($newData as $key => $value) {
                    if (is_array($value)) {
                        if (array_keys($value) === range(0, count($value) - 1)) {
                            if (isset($default[$key][0])) {
                                foreach ($value as $key1 => $item1) {
                                    $newData[$key][$key1] = $func($item1, $default[$key][0]);
                                }
                            }
                        } else {
                            if (isset($default[$key])) {
                                $new = array_merge($default[$key], $value);
                                $newData[$key] = $func($new, $default[$key]);
                            }
                        }
                    }
                }
                return $newData;
            };
            $newData = array_merge($default, $data);
            $template['data'] = $func($newData, $default);
        }
        return $template;
    }

    public function handleAll(array $arr)
    {
        foreach ($arr as $key => $template) {
            $arr[$key] = $this->handleOne($template);
        }
        return $arr;
    }
}
