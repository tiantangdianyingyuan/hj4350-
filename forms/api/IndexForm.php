<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api;


use app\core\response\ApiCode;
use app\forms\api\home_page\HomeBannerForm;
use app\forms\api\home_page\HomeBlockForm;
use app\forms\api\home_page\HomeCatForm;
use app\forms\api\home_page\HomeCouponForm;
use app\forms\api\home_page\HomeNavForm;
use app\forms\api\home_page\HomeTopicForm;
use app\forms\common\CommonAppConfig;
use app\forms\common\video\Video;
use app\models\Model;
use app\plugins\diy\Plugin;

class IndexForm extends Model
{
    private $type;

    public $page_id;

    public function rules()
    {
        return [
            [['page_id'], 'integer']
        ];
    }

    public function getIndex()
    {
        try {
            /* @var Plugin $plugin */
            $plugin = \Yii::$app->plugin->getPlugin('diy');
            $this->type = 'diy';
            $page = $plugin->getPage($this->page_id);
        } catch (\Exception $exception) {
            \Yii::warning('diy页面报错');
            \Yii::warning($exception);
            $page = $this->getDefault();
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'home_pages' => $page,
                'type' => $this->type
            ]
        ];
    }

    public function getDefault()
    {
        $homePages = CommonAppConfig::getHomePageConfig();
        $newList = [];
        // 商品分类
        $isOpenCat = 0;
        $isAllCat = 0;
        $catIds = [];

        $isOpenCoupon = 0;
        // 魔方
        $isOpenBlock = 0;
        $blockIds = [];
        // 轮播图
        $isOpenBanner = 0;
        $isOpenHomeNav = 0;
        $isOpenTopic = 0;
        // 统一查询数据
        foreach ($homePages as $homePageKey => $homePage) {
            if ($homePage['key'] === 'cat') {
                $isOpenCat = 1;
                if ($homePage['relation_id'] == 0) {
                    $isAllCat = 1;
                }
                $catIds = array_merge($catIds, [$homePage['relation_id']]);
            } elseif ($homePage['key'] === 'coupon') {
                $isOpenCoupon = 1;
            } elseif ($homePage['key'] === 'block') {
                $isOpenBlock = 1;
                $blockIds[] = $homePage['relation_id'];
            } elseif ($homePage['key'] === 'banner') {
                $isOpenBanner = 1;
            } elseif ($homePage['key'] === 'home_nav') {
                $isOpenHomeNav = 1;
            } elseif ($homePage['key'] === 'topic') {
                $isOpenTopic = 1;
            } else {
            }
        }

        if ($isOpenCat) {
            $homeCatForm = new HomeCatForm();
            $catGoods = $homeCatForm->getCatGoods($catIds, $isAllCat);
        }
        if ($isOpenCoupon) {
            $homeCouponForm = new HomeCouponForm();
            $coupons = $homeCouponForm->getCouponList();
        }
        if ($isOpenBanner) {
            $homeBannerForm = new HomeBannerForm();
            $banners = $homeBannerForm->getBanners();
        }
        if ($isOpenBlock) {
            $homeBlockForm = new HomeBlockForm();
            $blocks = $homeBlockForm->getBlock($blockIds);
        }
        if ($isOpenHomeNav) {
            $homeNavForm = new HomeNavForm();
            $homeNavs = $homeNavForm->getHomeNav();
        }
        if ($isOpenTopic) {
            $homeTopicForm = new HomeTopicForm();
            $topics = $homeTopicForm->getTopics();
        }
        $homePages[] = [
            'key' => 'fxhb',
            'name' => '裂变红包'
        ];

        // 统一处理数据
        foreach ($homePages as $homePageKey => $homePage) {
            if ($homePage['key'] === 'cat') {
                $list = $homeCatForm->getNewCatGoods($homePage, $catGoods);
                foreach ($list as $item) {
                    $newList[] = $item;
                }
            } elseif ($homePage['key'] === 'coupon') {
                $homePage['coupons'] = $coupons;
                $newList[] = $homePage;
            } elseif ($homePage['key'] === 'block') {
                $homeBlockForm = new HomeBlockForm();
                $newList[] = $homeBlockForm->getNewBlocks($homePage, $blocks);
            } elseif ($homePage['key'] === 'banner') {
                $homePage['banners'] = $banners;
                $newList[] = $homePage;
            } elseif ($homePage['key'] === 'home_nav') {
                $homePage['home_navs'] = $homeNavs;
                // TODO 兼容 2019-6-27
                if (!isset($homePage['row_num'])) {
                    $homePage['row_num'] = 4;
                }
                foreach ($homePage['home_navs'] as $i => $v) {
                    if ($v['open_type'] == 'contact' && \Yii::$app->appPlatform === APP_PLATFORM_TTAPP) {
                        array_splice($homePage['home_navs'], $i, 1);
                    }
                }
                $newList[] = $homePage;
            } elseif ($homePage['key'] === 'topic') {
                $homePage['topics'] = $topics;
                $newList[] = $homePage;
            } elseif ($homePage['key'] === 'video') {
                $homePage['video_url'] = Video::getUrl($homePage['video_url']);
                $newList[] = $homePage;
            } else {
                try {
                    $plugin = \Yii::$app->plugin->getPlugin($homePage['key']);
                    $homePage[$homePage['key']] = $plugin->getHomePage('api');
                } catch (\Exception $exception) {
                }
                $newList[] = $homePage;
            }
        }
        $this->type = 'mall';
        return $newList;
    }
}
