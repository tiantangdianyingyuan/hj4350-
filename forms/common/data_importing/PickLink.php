<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\data_importing;


use app\forms\PickLinkForm;
use app\models\Model;

class PickLink extends Model
{
    public static function getNewLink($link)
    {
        $res = explode('?', $link);
        if (!$res) {
            return '';
        }
        $newLink = $res[0];
        $newParams = [];
        if (count($res) > 1) {
            $params = explode('&', $res[1]);
            foreach ($params as $param) {
                $res = explode('=', $param);
                if ($res) {
                    $newParams[$res[0]] = $res[1];
                }
            }
        }

        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/pick-link/';

        switch ($newLink) {
            case '/pages/index/index':
                if (isset($newParams['page_id'])) {
                    $newData = [
                        'url' => '/pages/index/index?page_id=' . $newParams['page_id'],
                        'data' => []
                    ];
                } else {
                    $newData = [
                        'url' => '/pages/index/index',
                        'data' => []
                    ];
                }
                break;
            case '/pages/cat/cat':
                // cat_id
                $catId = isset($newParams['cat_id']) ? $newParams['cat_id'] : '';
                $newData = [
                    'url' => '/pages/cats/cats?cat_id=' . $catId,
                    'data' => [
                        'type' => 'base',
                        'name' => '分类',
                        'open_type' => PickLinkForm::OPEN_TYPE_2,
                        'icon' => $iconUrlPrefix . 'icon-cats.png',
                        'value' => '/pages/cats/cats',
                        'params' => [
                            [
                                'key' => 'cat_id',
                                'value' => $catId,
                                'desc' => 'cat_id 请填写在商品分类中相关分类的ID',
                                'is_required' => false,
                                'data_type' => 'number',
                                'page_url' => 'mall/cat/index',
                                'pic_url' => $iconUrlPrefix . 'example_image/cat-id.png',
                                'page_url_text' => '商品管理->分类'
                            ]
                        ]
                    ]
                ];
                break;
            case '/pages/cart/cart':
                $newData = [
                    'url' => '/pages/cart/cart',
                    'data' => []
                ];
                break;
            case '/pages/member/member':
                $newData = [
                    'url' => '/pages/member/index/index',
                    'data' => []
                ];
                break;
            case '/pages/user/user':
                $newData = [
                    'url' => '/pages/user-center/user-center',
                    'data' => []
                ];
                break;
            case '/pages/list/list':
                // cat_id
                $catId = isset($newParams['cat_id']) ? $newParams['cat_id'] : '';
                $newData = [
                    'url' => '/pages/goods/list?cat_id=' . $catId,
                    'data' => [
                        'type' => 'base',
                        'name' => '商品列表',
                        'open_type' => PickLinkForm::OPEN_TYPE_2,
                        'icon' => $iconUrlPrefix . 'icon-goods.png',
                        'value' => '/pages/goods/list',
                        'params' => [
                            [
                                'key' => 'cat_id',
                                'value' => $catId,
                                'desc' => 'cat_id 请填写在商品分类中相关分类的ID',
                                'is_required' => false,
                                'data_type' => 'number',
                                'page_url' => 'mall/cat/index',
                                'pic_url' => $iconUrlPrefix . 'example_image/cat-id.png',
                                'page_url_text' => '商品管理->分类'
                            ]
                        ]
                    ]
                ];
                break;
            case '/pages/goods/goods':
                // id
                $id = isset($newParams['id']) ? $newParams['id'] : '';
                $newData = [
                    'url' => '/pages/goods/goods?id=' . $id,
                    'data' => [
                        'type' => 'base',
                        'name' => '商品详情',
                        'open_type' => PickLinkForm::OPEN_TYPE_2,
                        'icon' => $iconUrlPrefix . 'icon-goods-detail.png',
                        'value' => '/pages/goods/goods',
                        'params' => [
                            [
                                'key' => 'id',
                                'value' => $id,
                                'desc' => 'id请填写在商品列表中相关商品的ID',
                                'is_required' => true,
                                'data_type' => 'number',
                                'page_url' => 'mall/goods/index',
                                'pic_url' => $iconUrlPrefix . 'example_image/goods-id.png',
                                'page_url_text' => '商品管理->商品列表'
                            ]
                        ],
                        'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
                    ]
                ];
                break;
            case '/pages/order/order':
                if ($newParams['status'] == -1) {
                    $newData = [
                        'url' => '/pages/order/index/index?status=0',
                        'data' => []
                    ];
                } elseif ($newParams['status'] == 0) {
                    $newData = [
                        'url' => '/pages/order/index/index?status=1',
                        'data' => []
                    ];
                } elseif ($newParams['status'] == 1) {
                    $newData = [
                        'url' => '/pages/order/index/index?status=2',
                        'data' => []
                    ];
                } elseif ($newParams['status'] == 2) {
                    $newData = [
                        'url' => '/pages/order/index/index?status=3',
                        'data' => []
                    ];
                } elseif ($newParams['status'] == 3) {
                    $newData = [
                        'url' => '/pages/order/index/index?status=4',
                        'data' => []
                    ];
                } elseif ($newParams['status'] == 4) {
                    $newData = [
                        'url' => '/pages/order/index/index?status=5',
                        'data' => []
                    ];
                } else {
                    $newData = [
                        'url' => '/pages/order/index/index?status=0',
                        'data' => []
                    ];
                }
                break;
            case '/pages/share/index':
                $newData = [
                    'url' => '/pages/share/index/index',
                    'data' => []
                ];
                break;
            case '/pages/coupon/coupon':
                $newData = [
                    'url' => '/pages/coupon/index/index',
                    'data' => []
                ];
                break;
            case '/pages/favorite/favorite':
                $newData = [
                    'url' => '/pages/favorite/favorite',
                    'data' => []
                ];
                break;
            case '/pages/article-detail/article-detail?id=about_us':
                $newData = [
                    'url' => '/pages/article/article-list/article-list',
                    'data' => []
                ];
                break;
            case '/pages/article-list/article-list':
                $newData = [
                    'url' => '/pages/article/article-list/article-list',
                    'data' => []
                ];
                break;
            case '/pages/video/video-list':
                $newData = [
                    'url' => '/pages/video/video',
                    'data' => []
                ];
                break;
            case '/pages/topic-list/topic-list':
                // type
                $newData = [
                    'url' => '/pages/topic/list?type=' . $newParams['type'],
                    'data' => [
                        'type' => 'base',
                        'name' => '专题列表',
                        'open_type' => PickLinkForm::OPEN_TYPE_2,
                        'icon' => $iconUrlPrefix . 'icon-topic.png',
                        'value' => '/pages/topic/list',
                        'params' => [
                            [
                                'key' => 'type',
                                'value' => $newParams['type'],
                                'desc' => 'type请填写在专题分类中的ID',
                                'is_required' => false,
                                'data_type' => 'number',
                                'page_url' => 'mall/topic-type/index',
                                'pic_url' => $iconUrlPrefix . 'example_image/topic-cat-id.png',
                                'page_url_text' => '内容管理->专题分类'
                            ]
                        ],
                        'key' => 'topic',
                    ]
                ];
                break;
            case '/pages/topic/topic':
                // id
                $id = isset($newParams['id']) ? $newParams['id'] : '';
                $newData = [
                    'url' => '/pages/topic/topic?id=' . $id,
                    'data' => [
                        'type' => 'base',
                        'name' => '专题详情',
                        'open_type' => PickLinkForm::OPEN_TYPE_2,
                        'icon' => $iconUrlPrefix . 'icon-topic-detail.png',
                        'value' => '/pages/topic/topic',
                        'params' => [
                            [
                                'key' => 'id',
                                'value' => $id,
                                'desc' => 'id 请填写在专题列表中相关专题的ID',
                                'is_required' => true,
                                'data_type' => 'number',
                                'page_url' => 'mall/topic/index',
                                'pic_url' => $iconUrlPrefix . 'example_image/topic-id.png',
                                'page_url_text' => '内容管理->专题'
                            ]
                        ],
                        'key' => 'topic',
                        'ignore' => [PickLinkForm::IGNORE_NAVIGATE],
                    ]
                ];
                break;
            case '/pages/coupon-list/coupon-list':
                $newData = [
                    'url' => '/pages/coupon/list/list',
                    'data' => []
                ];
                break;
            case 'wxapp':
                // 小程序
                // appId
                // path
                $appId = $newParams['appId'] ?? '';
                $path = $newParams['path'] ?? '';
                $newData = [
                    'url' => 'app?app_id=' . $appId . '&path=' . $path,
                    'data' => [
                        'type' => 'base',
                        'key' => PickLinkForm::OPEN_TYPE_3,
                        'name' => '跳转小程序',
                        'open_type' => PickLinkForm::OPEN_TYPE_3,
                        'icon' => $iconUrlPrefix . 'icon-mini.png',
                        'value' => PickLinkForm::OPEN_TYPE_3,
                        'remark' => '每次设置跳转,都需到小程序发布,重新添加跳转小程序appId,并重新发布。',
                        'params' => [
                            [
                                'key' => 'app_id',
                                'value' => $appId,
                                'desc' => '要打开的小程序 appId',
                                'is_required' => true
                            ],
                            [
                                'key' => 'path',
                                'value' => $path,
                                'desc' => '打开的页面路径，如pages/index/index，开头请勿加“/”',
                                'is_required' => true
                            ],
                        ],
                        'ignore' => [PickLinkForm::IGNORE_TITLE],
                    ]
                ];
                break;
            case '/':
                // 小程序
                // appId
                // path
                $newData = [
                    'url' => 'app?app_id=' . $newParams['appId'] . '&path=' . $newParams['path'],
                    'data' => [
                        'type' => 'base',
                        'key' => PickLinkForm::OPEN_TYPE_3,
                        'name' => '跳转小程序',
                        'open_type' => PickLinkForm::OPEN_TYPE_3,
                        'icon' => $iconUrlPrefix . 'icon-mini.png',
                        'value' => PickLinkForm::OPEN_TYPE_3,
                        'remark' => '每次设置跳转,都需到小程序发布,重新添加跳转小程序appId,并重新发布。',
                        'params' => [
                            [
                                'key' => 'app_id',
                                'value' => $newParams['appId'],
                                'desc' => '要打开的小程序 appId',
                                'is_required' => true
                            ],
                            [
                                'key' => 'path',
                                'value' => $newParams['path'],
                                'desc' => '打开的页面路径，如pages/index/index，开头请勿加“/”',
                                'is_required' => true
                            ],
                        ],
                        'ignore' => [PickLinkForm::IGNORE_TITLE],
                    ]
                ];
                break;
            case '/pages/miaosha/miaosha':
                $newData = [
                    'url' => '/plugins/miaosha/advance/advance',
                    'data' => []
                ];
                break;
            case '/pages/miaosha/order/order':
                $newData = [
                    'url' => '/pages/order/index/index?status=0',
                    'data' => []
                ];
                break;
            case 'web':
                // url
                $newData = [
                    'url' => '/pages/web/web?url=' . $newParams['url'],
                    'data' => [
                        'type' => 'base',
                        'key' => 'web',
                        'name' => '网页链接',
                        'open_type' => PickLinkForm::OPEN_TYPE_2,
                        'icon' => $iconUrlPrefix . 'icon-web-link.png',
                        'value' => '/pages/web/web',
                        'params' => [
                            [
                                'key' => 'url',
                                'value' => $newParams['url'],
                                'desc' => '打开的网页链接（注：域名必须已在微信官方小程序平台设置业务域名）',
                                'is_required' => true
                            ]
                        ],
                        'ignore' => [PickLinkForm::IGNORE_TITLE],
                    ]
                ];
                break;
            case '/pages/web/web':
                // url
                $newData = [
                    'url' => '/pages/web/web?url=' . $newParams['url'],
                    'data' => [
                        'type' => 'base',
                        'key' => 'web',
                        'name' => '网页链接',
                        'open_type' => PickLinkForm::OPEN_TYPE_2,
                        'icon' => $iconUrlPrefix . 'icon-web-link.png',
                        'value' => '/pages/web/web',
                        'params' => [
                            [
                                'key' => 'url',
                                'value' => $newParams['url'],
                                'desc' => '打开的网页链接（注：域名必须已在微信官方小程序平台设置业务域名）',
                                'is_required' => true
                            ]
                        ],
                        'ignore' => [PickLinkForm::IGNORE_TITLE],
                    ]
                ];
                break;
            case '/pages/shop/shop':
                $newData = [
                    'url' => '/pages/store/store',
                    'data' => []
                ];
                break;
            case '/pages/pt/index/index':
                if (isset($newParams['cid'])) {
                    $newData = [
                        'url' => '/plugins/pt/index/index?cat_id=' . $newParams['cid'],
                        'data' => [
                            'key' => 'pintuan',
                            'name' => '拼团首页',
                            'open_type' => PickLinkForm::OPEN_TYPE_2,
                            'icon' => '',
                            'value' => '/plugins/pt/index/index',
                            'params' => [
                                [
                                    'key' => 'cat_id',
                                    'value' => $newParams['cid'],
                                    'desc' => '请填写拼团分类ID,不填则显示热销',
                                    'is_required' => false,
                                    'data_type' => 'number',
                                    'page_url' => 'plugin/pintuan/mall/cats',
                                    'pic_url' => '',
                                    'page_url_text' => '商品分类'
                                ]
                            ]
                        ]
                    ];
                } else {
                    $newData = [
                        'url' => '/plugins/pt/index/index?cat_id=',
                        'data' => []
                    ];
                }
                break;
            case '/pages/pt/order/order':
                $newData = [
                    'url' => '/plugins/pt/order/order',
                    'data' => []
                ];
                break;
            case '/pages/pt/details/details':
                // gid
                $newData = [
                    'url' => '/plugins/pt/goods/goods?goods_id=' . $newParams['gid'],
                    'data' => [
                        'key' => 'pintuan',
                        'name' => '拼团商品详情',
                        'open_type' => PickLinkForm::OPEN_TYPE_2,
                        'icon' => '',
                        'value' => '/plugins/pt/goods/goods',
                        'params' => [
                            [
                                'key' => 'goods_id',
                                'value' => $newParams['gid'],
                                'desc' => '请填写拼团商品ID',
                                'is_required' => true,
                                'data_type' => 'number',
                                'page_url' => 'plugin/pintuan/mall/goods',
                                'pic_url' => '',
                                'page_url_text' => '商品列表'
                            ]
                        ],
                        'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
                    ]
                ];
                break;
            case '/pages/book/details/details':
                // id
                $newData = [
                    'url' => '/plugins/book/goods/goods?goods_id=' . $newParams['id'],
                    'data' => [
                        'key' => 'booking',
                        'name' => '预约商品详情',
                        'open_type' => PickLinkForm::OPEN_TYPE_2,
                        'icon' => '',
                        'value' => '/plugins/book/goods/goods',
                        'params' => [
                            [
                                'key' => 'goods_id',
                                'value' => $newParams['id'],
                                'desc' => '请填写预约商品ID',
                                'is_required' => true,
                                'data_type' => 'number',
                                'page_url' => 'plugin/booking/mall/goods/index',
                                'pic_url' => '',
                                'page_url_text' => '商品管理'
                            ]
                        ],
                        'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
                    ]
                ];
                break;
            case '/pages/book/index/index':
                // cid
                $cid = isset($newParams['cid']) ? $newParams['cid'] : '';
                $newData = [
                    'url' => '/plugins/book/index/index?cat_id=' . $cid,
                    'data' => [
                        'key' => 'booking',
                        'name' => '预约',
                        'open_type' => PickLinkForm::OPEN_TYPE_2,
                        'icon' => '',
                        'value' => '/plugins/book/index/index',
                        'params' => [
                            [
                                'key' => 'cat_id',
                                'value' => $cid,
                                'desc' => '请填写预约分类ID,不填显示全部',
                                'is_required' => false,
                                'data_type' => 'number',
                                'page_url' => 'plugin/booking/mall/cats',
                                'pic_url' => '',
                                'page_url_text' => '分类管理'
                            ]
                        ]
                    ]
                ];
                break;
            case '/pages/book/order/order':
                $newData = [
                    'url' => '/pages/order/index/index?status=0',
                    'data' => []
                ];
                break;
            case '/pages/quick-purchase/index/index':
                $newData = [
                    'url' => '/pages/quick-shop/quick-shop',
                    'data' => []
                ];
                break;
            case '/pages/fxhb/open/open':
                $newData = [
                    'url' => '/plugins/fxhb/detail/detail',
                    'data' => []
                ];
                break;
            case '/pages/recharge/recharge':
                $newData = [
                    'url' => '/pages/balance/recharge',
                    'data' => []
                ];
                break;
            case '/mch/shop-list/shop-list':
                $newData = [
                    'url' => '/plugins/mch/list/list',
                    'data' => []
                ];
                break;
            case '/mch/shop/shop':
                // mch_id
                $newData = [
                    'url' => '/plugins/mch/shop/shop?mch_id=' . $newParams['mch_id'],
                    'data' => [
                        'key' => 'mch',
                        'name' => '多商户店铺',
                        'open_type' => PickLinkForm::OPEN_TYPE_2,
                        'icon' => '',
                        'value' => '/plugins/mch/shop/shop',
                        'params' => [
                            [
                                'key' => 'mch_id',
                                'value' => $newParams['mch_id'],
                                'desc' => '请填写入驻商户ID',
                                'is_required' => true,
                                'data_type' => 'number',
                                'page_url' => 'plugin/mch/mall/mch/index',
                                'pic_url' => '',
                                'page_url_text' => '商户列表'
                            ]
                        ]
                    ]
                ];
                break;
            case '/mch/m/myshop/myshop':
                $newData = [
                    'url' => '/plugins/mch/mch/myshop/myshop',
                    'data' => []
                ];
                break;
            case '/pages/integral-mall/index/index':
                $newData = [
                    'url' => '/plugins/integral_mall/index/index',
                    'data' => []
                ];
                break;
            case '/pages/integral-mall/order/order':
                $newData = [
                    'url' => '/pages/order/index/index?status=0',
                    'data' => []
                ];
                break;
            case '/pages/integral-mall/register/index':
                $newData = [
                    'url' => '/plugins/check_in/index/index',
                    'data' => []
                ];
                break;
            case '/pages/search/search':
                $newData = [
                    'url' => '/pages/search/search',
                    'data' => []
                ];
                break;
            case '/pages/address/address':
                $newData = [
                    'url' => '/pages/address/address',
                    'data' => []
                ];
                break;
            case '/pages/card/card':
                $newData = [
                    'url' => '/pages/card/index/index',
                    'data' => []
                ];
                break;
            case '/pages/bangding/bangding':
                $newData = [
                    'url' => '/pages/binding/binding',
                    'data' => []
                ];
                break;
            case '/pond/pond/pond':
                $newData = [
                    'url' => '/plugins/pond/index/index',
                    'data' => []
                ];
                break;
            case '/scratch/index/index':
                $newData = [
                    'url' => '/plugins/scratch/index/index',
                    'data' => []
                ];
                break;
            case '/bargain/list/list':
                $newData = [
                    'url' => '/plugins/bargain/index/index',
                    'data' => []
                ];
                break;
            case '/bargain/goods/goods':
                // goods_id
                $value = '/plugins/bargain/goods/goods';
                $newData = [
                    'url' => '/plugins/bargain/goods/goods?goods_id=' . $newParams['goods_id'],
                    'data' => [
                        'name' => '砍价商品详情',
                        'key' => 'bargain',
                        'open_type' => PickLinkForm::OPEN_TYPE_2,
                        'icon' => '',
                        'value' => '/plugins/bargain/goods/goods',
                        'params' => [
                            [
                                'key' => 'goods_id',
                                'value' => $newParams['goods_id'],
                                'desc' => '请填写砍价商品ID',
                                'is_required' => true,
                                'data_type' => 'number',
                                'page_url' => 'plugin/bargain/mall/goods/index',
                                'pic_url' => '',
                                'page_url_text' => '商品管理->商品列表'
                            ]
                        ],
                        'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
                    ]
                ];
                break;
            case '/lottery/index/index':
                $newData = [
                    'url' => '/plugins/lottery/index/index',
                    'data' => []
                ];
                break;
            case '/lottery/goods/goods':
                // id
                $newData = [
                    'url' => '/plugins/lottery/goods/goods?lottery_id=' . $newParams['id'],
                    'data' => [
                        'key' => 'lottery',
                        'name' => '抽奖商品详情',
                        'open_type' => PickLinkForm::OPEN_TYPE_2,
                        'icon' => '',
                        'value' => '/plugins/lottery/goods/goods',
                        'params' => [
                            [
                                'key' => 'lottery_id',
                                'value' => $newParams['id'],
                                'desc' => '请填写抽奖商品ID',
                                'is_required' => true,
                                'data_type' => 'number',
                                'page_url' => 'plugin/lottery/mall/lottery',
                                'pic_url' => '',
                                'page_url_text' => '奖品列表'
                            ]
                        ],
                        'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
                    ]
                ];
                break;
            case '/step/index/index':
                $newData = [
                    'url' => '/plugins/step/index/index',
                    'data' => []
                ];
                break;
            case '/step/goods/goods':
                // goods_id
                $newData = [
                    'url' => '/plugins/step/goods/goods?goods_id=' . $newParams['goods_id'],
                    'data' => [
                        'key' => 'step',
                        'name' => '步数宝商品详情',
                        'open_type' => PickLinkForm::OPEN_TYPE_2,
                        'icon' => '',
                        'value' => '/plugins/step/goods/goods',
                        'params' => [
                            [
                                'key' => 'goods_id',
                                'value' => $newParams['goods_id'],
                                'desc' => '请填写步数宝商品ID',
                                'is_required' => true,
                                'data_type' => 'number',
                                'page_url' => 'plugin/step/mall/goods',
                                'pic_url' => '',
                                'page_url_text' => '商品列表'
                            ]
                        ],
                        'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
                    ]
                ];
                break;
            case 'tel':
                // tel
                $newData = [
                    'url' => 'tel?tel=' . $newParams['tel'],
                    'data' => [
                        'type' => 'base',
                        'key' => PickLinkForm::OPEN_TYPE_6,
                        'name' => '联系我们',
                        'open_type' => PickLinkForm::OPEN_TYPE_6,
                        'icon' => $iconUrlPrefix . 'icon-contact.png',
                        'value' => 'tel',
                        'params' => [
                            [
                                'key' => 'tel',
                                'value' => $newParams['tel'],
                                'desc' => '请填写联系电话',
                                'is_required' => true,
                                'data_type' => 'text'
                            ]
                        ]
                    ]
                ];
                break;
            case 'contact':
                $newData = [
                    'url' => 'contact',
                    'data' => [
                        'type' => 'base',
                        'key' => PickLinkForm::OPEN_TYPE_4,
                        'name' => '客服',
                        'open_type' => PickLinkForm::OPEN_TYPE_4,
                        'icon' => $iconUrlPrefix . 'icon-service.png',
                        'value' => PickLinkForm::OPEN_TYPE_4,
                    ]
                ];
                break;
            case 'clear_cache':
                $newData = [
                    'url' => 'clear_cache',
                    'data' => [
                        'type' => 'base',
                        'key' => PickLinkForm::OPEN_TYPE_5,
                        'name' => '清除缓存',
                        'open_type' => PickLinkForm::OPEN_TYPE_5,
                        'icon' => $iconUrlPrefix . 'icon-clear-cache.png',
                        'value' => PickLinkForm::OPEN_TYPE_5,
                        'ignore' => [PickLinkForm::IGNORE_TITLE],
                    ]
                ];
                break;
            default:
                $newData = [
                    'url' => '',
                    'data' => []
                ];
                break;
        }

        return $newData;
    }
}
