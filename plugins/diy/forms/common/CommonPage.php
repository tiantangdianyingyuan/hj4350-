<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/24
 * Time: 9:16
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\forms\common;


use app\forms\api\app_platform\Transform;
use app\forms\common\order\CommonOrder;
use app\forms\permission\CheckPermission;
use app\forms\common\video\Video;
use app\models\Mall;
use app\models\Model;
use app\plugins\diy\models\DiyPage;
use app\plugins\diy\models\DiyTemplate;
use yii\helpers\Json;

/**
 * @property Mall $mall
 */
class CommonPage extends Model
{
    public $mall;
    public $longitude;
    public $latitude;

    public static function getCommon($mall, $longitude = null, $latitude = null)
    {
        $common = new self();
        $common->mall = $mall;
        $common->longitude = $longitude;
        $common->latitude = $latitude;
        return $common;
    }

    /**
     * @param null $pageId 自定义页面ID
     * @param bool $isIndex 是否查找首页
     * @return array
     * @throws \Exception
     * 获取自定义页面
     */
    public function getPage($pageId = null, $isIndex = false)
    {
        if (!$pageId) {
            if (!$isIndex) {
                throw new \Exception('页面不存在');
            }
            $exists = DiyPage::find()->where([
                'is_home_page' => 1,
                'mall_id' => $this->mall->id,
                'is_disable' => 0,
                'is_delete' => 0
            ])->exists();
            if (!$exists) {
                throw new \Exception('页面不存在');
            }
        }
        $query = DiyPage::find()->select('id,title,show_navs,is_home_page')
            ->where([
                'mall_id' => $this->mall->id,
                'is_disable' => 0,
                'is_delete' => 0,
            ])->with(['navs' => function ($query) {
                $query->select('id,name,page_id,template_id')->with(['template' => function ($query) {
                    $query->select('id,name,data')->where(['is_delete' => 0]);
                }]);
            }]);
        if ($pageId) {
            $query->andWhere(['id' => $pageId]);
        } else {
            if ($isIndex) {
                $query->andWhere(['is_home_page' => 1]);
            } else {
                throw new \Exception('页面不存在');
            }
        }
        $page = $query->asArray()->one();
        if (!$page) {
            throw new \Exception('页面不存在');
        }

        if (!empty($page['navs'])) {
            try {
                // 商品组件数据
                $goodsIds = [];
                $goodsCats = [];
                // 优惠券数据
                $coupons = [];
                // 多商户数据
                $mchIds = [];
                $mchGoodsIds = [];
                // 门店数据
                $storeIds = [];
                $pintuanGoodsIds = [];
                $bookingGoodsIds = [];
                $miaoshaGoodsIds = [];
                $bargainGoodsIds = [];
                $integralMallGoodsIds = [];
                $lotteryGoodsIds = [];

                //预售
                $advanceGoodsIds = [];
                //N元任选
                $pickGoodsIds = [];
                //限时购买
                $flashSaleGoodsIds = [];

                //小程序管理入口权限
                $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
                $permissionFlip = array_flip($permission);
                $app_admin = true;
                if (empty(\Yii::$app->plugin->getInstalledPlugin('app_admin')) || !in_array('app_admin', $permission) || empty(\Yii::$app->user->identity->identity->is_admin) || \Yii::$app->user->identity->identity->is_admin != 1) {
                    $app_admin = false;
                }
                // 统一数据查询
                foreach ($page['navs'] as &$nav) {
                    if (!empty($nav['template']['data'])) {
                        $nav['template']['data'] = json_decode($nav['template']['data'], true);
                        foreach ($nav['template']['data'] as $dIndex => &$item) {
                            if (isset($item['permission_key']) && $item['permission_key'] && !isset($permissionFlip[$item['permission_key']])) {
                                unset($nav['template']['data'][$dIndex]);
                                continue;
                            }

                            //小程序入口权限
                            if ($item['id'] == 'nav') {
                                foreach ($item['data']['navs'] as $i => $v) {
                                    // 判断权限显示
                                    if (isset($v['key']) && $v['key'] && !isset($permissionFlip[$v['key']])) {
                                        unset($item['data']['navs'][$i]);
                                        continue;
                                    }
                                    if ($v['openType'] == 'app_admin' && !$app_admin) {
                                        unset($item['data']['navs'][$i]);
                                        continue;
                                    }
                                    if ($v['openType'] == 'contact' && \Yii::$app->appPlatform === APP_PLATFORM_TTAPP) {
                                        unset($item['data']['navs'][$i]);
                                        continue;
                                    }
                                }
                                $item['data']['navs'] = array_values($item['data']['navs']);
                            }

                            // 图片广告
                            if ($item['id'] == 'rubik') {
                                // 判断权限显示
                                foreach ($item['data']['list'] as $rIndex => $rItem) {
                                    if (isset($rItem['link']['key']) && $rItem['link']['key'] && !isset($permissionFlip[$rItem['link']['key']])) {
                                        $item['data']['list'][$rIndex]['link']['value'] = '';
                                        $item['data']['list'][$rIndex]['link']['new_link_url'] = '';
                                        continue;
                                    }
                                }
                                foreach ($item['data']['hotspot'] as $hIndex => $hItem) {
                                    if (isset($hItem['link']['key']) && $hItem['link']['key'] && !isset($permissionFlip[$hItem['link']['key']])) {
                                        $item['data']['hotspot'][$hIndex]['link']['value'] = '';
                                        $item['data']['hotspot'][$hIndex]['link']['new_link_url'] = '';
                                        continue;
                                    }
                                }
                            }

                            // 优惠券
                            if ($item['id'] == 'coupon') {
                                // 防止重复查询
                                if (!$coupons) {
                                    $diyCouponForm = new DiyCouponForm();
                                    $coupons = $diyCouponForm->getCoupons();
                                }
                            }
                            // 好店推荐
                            if ($item['id'] == 'mch') {
                                $diyMchForm = new DiyMchForm();
                                $res = $diyMchForm->getMchData($item['data']);
                                $mchIds = array_merge($res['mchIds'], $mchIds);
                                $mchGoodsIds = array_merge($res['mchGoodsIds'], $mchGoodsIds);
                            }
                            // 专题
                            if ($item['id'] == 'topic') {
                                $diyTopicsForm = DiyTopicsForm::getInstance();
                                if (isset($item['data']['style']) && $item['data']['style'] == 'list') {
                                    if (isset($item['data']['cat_show']) && !$item['data']['cat_show']) {
                                        foreach ($item['data']['topic_list'] as $topic) {
                                            $diyTopicsForm->setIdList($topic['id']);
                                        }
                                    } else {
                                        foreach ($item['data']['list'] as $type) {
                                            if ($type['custom']) {
                                                foreach ($type['children'] as $topic) {
                                                    $diyTopicsForm->setIdList($topic['id']);
                                                }
                                            } else {
                                                $diyTopicsForm->setTypeList($type['cat_id']);
                                            }
                                        }
                                    }
                                }
                            }
                            // 商品
                            if ($item['id'] == 'goods') {
                                $diyGoodsForm = new DiyGoodsForm();
                                $goodsIds = array_merge($diyGoodsForm->getGoodsIds($item['data']), $goodsIds);
                                $goodsCats = $diyGoodsForm->getCats($item['data'], $goodsCats);
                            }
                            // 门店
                            if ($item['id'] == 'store') {
                                $diyStoreForm = new DiyStoreForm();
                                $storeIds = array_merge($diyStoreForm->getStoreIds($item['data']), $storeIds);
                            }

                            // 拼团
                            if ($item['id'] == 'pintuan') {
                                $diyPintuanForm = new DiyPintuanForm();
                                $pintuanGoodsIds = array_merge(
                                    $diyPintuanForm->getGoodsIds($item['data']),
                                    $pintuanGoodsIds
                                );
                            }
                            // 预约
                            if ($item['id'] == 'booking') {
                                $diyBookingForm = new DiyBookingForm();
                                $bookingGoodsIds = array_merge(
                                    $diyBookingForm->getGoodsIds($item['data']),
                                    $bookingGoodsIds
                                );
                            }
                            // 秒杀
                            if ($item['id'] == 'miaosha') {
                                $diyMiaoshaForm = new DiyMiaoshaForm();
                                $miaoshaGoodsIds = array_merge(
                                    $diyMiaoshaForm->getGoodsIds($item['data']),
                                    $miaoshaGoodsIds
                                );
                            }
                            // 砍价
                            if ($item['id'] == 'bargain') {
                                $diyBargainForm = new DiyBargainForm();
                                $bargainGoodsIds = array_merge(
                                    $diyBargainForm->getGoodsIds($item['data']),
                                    $bargainGoodsIds
                                );
                            }
                            // 积分商城
                            if ($item['id'] == 'integral-mall') {
                                $diyIntegralMallForm = new DiyIntegralMallForm();
                                $integralMallGoodsIds = array_merge(
                                    $diyIntegralMallForm->getGoodsIds($item['data']),
                                    $integralMallGoodsIds
                                );
                            }
                            // 幸运抽奖
                            if ($item['id'] == 'lottery') {
                                $diyLotteryForm = new DiyLotteryForm();
                                $lotteryGoodsIds = array_merge(
                                    $diyLotteryForm->getGoodsIds($item['data']),
                                    $lotteryGoodsIds
                                );
                            }
                            // 快捷导航
                            if ($item['id'] == 'quick-nav') {
                                if (isset($item['data']['navSwitch']) && $item['data']['navSwitch'] && $item['data']['useMallConfig']) {
                                    $diyQuickNavForm = new DiyQuickNavForm();
                                } else {
                                    if (isset($item['data']['web']['url'])) {
                                        $item['data']['web']['url'] = urlencode($item['data']['web']['url']);
                                    }
                                }
                                // 权限判断
                                if (isset($item['data']['customize']['key']) && $item['data']['customize']['key'] && !isset($permissionFlip[$item['data']['customize']['key']])) {
                                    $item['data']['customize']['opened'] = false;
                                }

                            }
                            // 签到
                            if ($item['id'] == 'check-in') {
                                $diyCheckInForm = new DiyCheckInForm();
                            }

                            // 预售
                            if ($item['id'] == 'advance') {
                                $diyAdvanceForm = new DiyAdvanceForm();
                                $advanceGoodsIds = array_merge(
                                    $diyAdvanceForm->getGoodsIds($item['data']),
                                    $advanceGoodsIds
                                );
                            }

                            //超级会员卡
                            if ($item['id'] == 'vip_card') {
                                $diyVipCardForm = new DiyVipCardForm();
                            }

                            //N元任选
                            if ($item['id'] == 'pick') {
                                $diyPickForm = new DiyPickForm();
                                $pickGoodsIds = array_merge(
                                    $diyPickForm->getGoodsIds($item['data']),
                                    $pickGoodsIds
                                );
                            }
                            //直播
                            if ($item['id'] == 'live') {
                                $diyLiveForm = new DiyLiveForm();
                            }
                            //限时购买
                            if ($item['id'] == 'flash-sale') {

                                $diyFlashSaleForm = new DiyFlashSaleForm();
                                $flashSaleGoodsIds = array_merge(
                                    $diyFlashSaleForm->getGoodsIds($item['data']),
                                    $flashSaleGoodsIds
                                );
                            }
                        }
                        unset($item);
                        $nav['template']['data'] = array_values($nav['template']['data']);
                    }
                }

                // 商品组件数据
                if (isset($diyGoodsForm)) {
                    $diyGoods = $diyGoodsForm->getGoodsById($goodsIds);
                    $diyCatsGoods = $diyGoodsForm->getGoodsByCat($goodsCats);
                }
                // 好店推荐组件数据
                if (isset($diyMchForm)) {
                    $diyMchGoods = $diyMchForm->getMchGoodsById($mchGoodsIds);
                    $diyMch = $diyMchForm->getMch($mchIds);
                }
                // 门店组件数据
                if (isset($diyStoreForm)) {
                    $diyStores = $diyStoreForm->getStoreById($storeIds);
                }
                // 拼团组件数据
                if (isset($diyPintuanForm)) {
                    $diyPintuanGoods = $diyPintuanForm->getGoodsById($pintuanGoodsIds);
                }
                // 预约组件数据
                if (isset($diyBookingForm)) {
                    $diyBookingGoods = $diyBookingForm->getGoodsById($bookingGoodsIds);
                }
                // 秒杀组件数据
                if (isset($diyMiaoshaForm)) {
                    $diyMiaoshaGoods = $diyMiaoshaForm->getGoodsById($miaoshaGoodsIds);
                }
                // 砍价组件数据
                if (isset($diyBargainForm)) {
                    $diyBargainGoods = $diyBargainForm->getGoodsById($bargainGoodsIds);
                }
                // 积分商城组件数据
                if (isset($diyIntegralMallForm)) {
                    $diyIntegralMallGoods = $diyIntegralMallForm->getGoodsById($integralMallGoodsIds);
                }
                // 积分商城组件数据
                if (isset($diyLotteryForm)) {
                    $diyLotteryGoods = $diyLotteryForm->getGoodsById($lotteryGoodsIds);
                }
                // 快捷导航
                if (isset($diyQuickNavForm)) {
                    $quickNav = array_merge($diyQuickNavForm->getQuickNav());
                }
                // 签到
                if (isset($diyCheckInForm)) {
                    $checkIn = $diyCheckInForm->getCheckIn();
                }
                // 预售
                if (isset($diyAdvanceForm)) {
                    $advance = $diyAdvanceForm->getGoodsById($advanceGoodsIds);
                }
                // 超级会员卡
                if (isset($diyVipCardForm)) {
                    $vipCard = $diyVipCardForm->getVipCard();
                }
                //N元任选
                if (isset($diyPickForm)) {
                    $pick = $diyPickForm->getGoodsById($pickGoodsIds);
                }
                //限时购买
                if (isset($diyFlashSaleForm)) {
                    $flashSale = $diyFlashSaleForm->getGoodsById($flashSaleGoodsIds);
                }
                // 直播
                if (isset($diyLiveForm)) {
                    $liveList = $diyLiveForm->getLiveList();
                }
                // 统一数据处理
                foreach ($page['navs'] as $index => &$nav) {
                    if (!empty($nav['template']['data'])) {
                        foreach ($nav['template']['data'] as &$item) {
                            if ($item['id'] == 'nav') {
                                foreach ($item['data']['navs'] as $key => &$value) {
                                    $value['icon_url'] = $value['icon'];
                                    $value['link_url'] = $value['url'];
                                    $value['open_type'] = $value['openType'];
                                }
                                unset($value);
                                $item['data']['navs'] = Transform::getInstance()->transformHomeNav($item['data']['navs']);
                            }
                            // 优惠券
                            if ($item['id'] == 'coupon') {
                                $item['data']['coupon_list'] = $coupons;
                            }
                            // 商品
                            if ($item['id'] == 'goods') {
                                $item['data'] = $diyGoodsForm->getNewGoods($item['data'], $diyGoods, $diyCatsGoods);
                            }
                            // 专题
                            if ($item['id'] == 'topic') {
                                $diyTopicsForm = DiyTopicsForm::getInstance();
                                $item['data'] = $diyTopicsForm->getNewTopics($item['data']);
                            }
                            // 好店推荐
                            if ($item['id'] == 'mch') {
                                $item['data'] = $diyMchForm->getNewMch($item['data'], $diyMchGoods, $diyMch);
                            }
                            // 门店
                            if ($item['id'] == 'store') {
                                $item['data'] = $diyStoreForm->getNewStore(
                                    $item['data'],
                                    $diyStores,
                                    $this->longitude,
                                    $this->latitude
                                );
                            }
                            // 拼团
                            if ($item['id'] == 'pintuan') {
                                $item['data'] = $diyPintuanForm->getNewGoods($item['data'], $diyPintuanGoods);
                            }
                            // 预约
                            if ($item['id'] == 'booking') {
                                $item['data'] = $diyBookingForm->getNewGoods($item['data'], $diyBookingGoods);
                            }
                            // 秒杀
                            if ($item['id'] == 'miaosha') {
                                $item['data'] = $diyMiaoshaForm->getNewGoods($item['data'], $diyMiaoshaGoods);
                            }
                            // 砍价
                            if ($item['id'] == 'bargain') {
                                $item['data'] = $diyBargainForm->getNewGoods($item['data'], $diyBargainGoods);
                            }
                            // 积分商城
                            if ($item['id'] == 'integral-mall') {
                                $item['data'] = $diyIntegralMallForm->getNewGoods($item['data'], $diyIntegralMallGoods);
                            }
                            // 抽奖
                            if ($item['id'] == 'lottery') {
                                $item['data'] = $diyLotteryForm->getNewGoods($item['data'], $diyLotteryGoods);
                            }
                            // 轮播图
                            if ($item['id'] == 'banner') {
                                $bannerForm = new DiyBannerForm();
                                $item['data'] = $bannerForm->getNewBanner($item['data']);
                            }
                            // 快捷导航
                            if ($item['id'] == 'quick-nav') {
                                if (isset($quickNav)) {
                                    $item['data'] = $quickNav;
                                } else {
                                    $arr = explode(',', $item['data']['mapNav']['location']);
                                    $item['data']['mapNav']['latitude'] = isset($arr[0]) ? $arr[0] : 0;
                                    $item['data']['mapNav']['longitude'] = isset($arr[1]) ? $arr[1] : 0;
                                }
                            }
                            // 弹窗广告
                            if ($item['id'] == 'modal') {
                                $newList = [];
                                foreach ($item['data']['list'] as $dItem) {
                                    $newList[] = $dItem;
                                }
                                $item['data']['list'] = [];
                                if (count($newList) > 0) {
                                    $item['data']['list'][] = $newList;
                                }
                            }
                            // 签到
                            if ($item['id'] == 'check-in') {
                                if (isset($checkIn)) {
                                    $item['data']['award'] = $checkIn;
                                }
                            }
                            // 预售
                            if ($item['id'] == 'advance') {
                                $item['data'] = $diyAdvanceForm->getNewGoods($item['data'], $advance);
                            }
                            // 超级会员卡
                            if ($item['id'] == 'vip_card') {
                                if (isset($vipCard)) {
                                    $item['data']['vip_card'] = $vipCard;
                                }
                            }
                            //N元任选
                            if ($item['id'] == 'pick') {
                                $item['data'] = $diyPickForm->getNewGoods($item['data'], $pick);
                            }
                            //限时购买
                            if ($item['id'] == 'flash-sale') {
                                $item['data'] = $diyFlashSaleForm->getNewGoods($item['data'], $flashSale);
                            }

                            if ($item['id'] == 'map') {
                                $arr = explode(',', $item['data']['location']);
                                $item['data']['latitude'] = isset($arr[0]) ? $arr[0] : 0;
                                $item['data']['longitude'] = isset($arr[1]) ? $arr[1] : 0;
                            }

                            if ($item['id'] == 'user-order') {
                                $res = (new CommonOrder())->getOrderInfoCount();
                                foreach ($item['data']['navs'] as $key => &$value) {
                                    $value['url'] = '/pages/order/index/index?status=' . ($key + 1);
                                    $value['num'] = $res[$key];
                                    $value['open_type'] = $value['openType'];
                                    $value['link_url'] = $value['url'];
                                    $value['name'] = $value['text'];
                                    $value['icon_url'] = $value['picUrl'];
                                }
                                unset($value);
                            }
                            if ($item['id'] == 'video') {
                                $item['data']['url'] = Video::getUrl($item['data']['url']);
                            }
                            if ($item['id'] == 'copyright') {
                                if (isset($item['data']['link']['data']['params'])) {
                                    $newParams = [];
                                    foreach ($item['data']['link']['data']['params'] as $param) {
                                        $newParams[] = [
                                            'key' => $param['key'],
                                            'value' => $param['value']
                                        ];
                                    }
                                    $item['data']['params'] = $newParams;
                                }
                            }
                            // 直播
                            if ($item['id'] == 'live') {
                                $item['data'] = $diyLiveForm->getNewList($item['data'], $liveList);
                            }
                        }
                        unset($item);
                    } else {
                        $nav['template']['data'] = [];
                    }

                    // 是设置首页的diy页面才添加裂变红包广告
                    if ($page['is_home_page'] == 1 && $index == 0) {
                        $modal = (new DiyModalForm())->getModal();
                        if (count($modal) > 0) {
                            $nav['template']['data'][] = [
                                'id' => 'modal',
                                'data' => [
                                    'opened' => true,
                                    'times' => 1,
                                    'list' => [$modal]
                                ]
                            ];
                        }
                    }
                }
                unset($nav);
                // 是设置首页的diy页面才添加裂变红包广告
//                if ($page['is_home_page'] == 1) {
//                    $modal = (new DiyModalForm())->getModal();
//                    $page['navs'][0]['template']['data'][] = [
//                        'id' => 'modal',
//                        'data' => [
//                            'opened' => true,
//                            'times' => 1,
//                            'list' => [$modal]
//                        ]
//                    ];
//                }
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return $page;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]|DiyPage[]
     * 获取所有页面
     */
    public function getPageList()
    {
        $pageList = DiyPage::find()->alias('p')->select('p.*')->where([
            'p.mall_id' => \Yii::$app->getMallId() ?: \Yii::$app->mall->id,
            'p.is_delete' => 0,
            'p.is_disable' => 0,
        ])->innerJoinwith(['templateOne t' => function ($query) {
            $query->where(['t.type' =>  DiyTemplate::TYPE_PAGE]);
        }])->orderBy(['p.is_home_page' => SORT_DESC, 'p.created_at' => SORT_DESC])->all();
        return $pageList;
    }

    // 第一次简单处理数据--2020-04-20新首页数据接口
    public function getNewPage($pageId, $isIndex)
    {
        $page = $this->getData($pageId, $isIndex);
        if (!empty($page['navs'])) {
            // 获取需要查询的组件信息
            foreach ($page['navs'] as $index => $nav) {
                if (!empty($nav['template']['data'])) {
                    foreach ($nav['template']['data'] as $dIndex => $item) {
                        switch ($item['id']) {
                            case 'topic':
                                $diyTopicsForm = DiyTopicsForm::getInstance();
                                if (isset($item['data']['style']) && $item['data']['style'] == 'list') {
                                    if (isset($item['data']['cat_show']) && !$item['data']['cat_show']) {
                                        foreach ($item['data']['topic_list'] as $topic) {
                                            $diyTopicsForm->setIdList($topic['id']);
                                        }
                                    } else {
                                        foreach ($item['data']['list'] as $type) {
                                            if ($type['custom']) {
                                                foreach ($type['children'] as $topic) {
                                                    $diyTopicsForm->setIdList($topic['id']);
                                                }
                                            } else {
                                                $diyTopicsForm->setTypeList($type['cat_id']);
                                            }
                                        }
                                    }
                                }
                                break;
                            default:
                        }
                    }
                }
            }
            //小程序管理入口权限
            $checkPermission = CheckPermission::getInstance();
            foreach ($page['navs'] as $index => &$nav) {
                if (!empty($nav['template']['data'])) {
                    foreach ($nav['template']['data'] as $dIndex => &$item) {
                        if (isset($item['permission_key']) && !$checkPermission->check($item['permission_key'])) {
                            unset($nav['template']['data'][$dIndex]);
                            continue;
                        }
                        switch ($item['id']) {
                            case 'nav':
                                foreach ($item['data']['navs'] as $i => &$v) {
                                    // 判断权限显示
                                    if (isset($v['key']) && !$checkPermission->check($v['key'])) {
                                        unset($item['data']['navs'][$i]);
                                        continue;
                                    }
                                    if ($v['openType'] == 'app_admin' && !$checkPermission->check('app_admin')) {
                                        unset($item['data']['navs'][$i]);
                                        continue;
                                    }
                                    if ($v['openType'] == 'contact' && \Yii::$app->appPlatform === APP_PLATFORM_TTAPP) {
                                        unset($item['data']['navs'][$i]);
                                        continue;
                                    }
                                    $v['icon_url'] = $v['icon'];
                                    $v['link_url'] = $v['url'];
                                    $v['open_type'] = $v['openType'];
                                }
                                unset($v);
                                $item['data']['navs'] = array_values($item['data']['navs']);
                                $item['data']['navs'] = Transform::getInstance()->transformHomeNav($item['data']['navs']);
                                break;
                            case 'rubik':
                                // 判断权限显示
                                foreach ($item['data']['list'] as $rIndex => $rItem) {
                                    if (isset($rItem['link']['key']) && !$checkPermission->check($rItem['link']['key'])) {
                                        $item['data']['list'][$rIndex]['link']['value'] = '';
                                        $item['data']['list'][$rIndex]['link']['new_link_url'] = '';
                                        continue;
                                    }
                                }
                                foreach ($item['data']['hotspot'] as $hIndex => $hItem) {
                                    if (isset($hItem['link']['key']) && !$checkPermission->check($rItem['link']['key'])) {
                                        $item['data']['hotspot'][$hIndex]['link']['value'] = '';
                                        $item['data']['hotspot'][$hIndex]['link']['new_link_url'] = '';
                                        continue;
                                    }
                                }
                                break;
                            case 'banner':
                                $bannerForm = new DiyBannerForm();
                                $item['data'] = $bannerForm->getNewBanner($item['data']);
                                break;
                            case 'quick-nav':
                                if (isset($item['data']['navSwitch']) && $item['data']['navSwitch']) {
                                    if ($item['data']['useMallConfig']) {
                                        $diyQuickNavForm = new DiyQuickNavForm();
                                        $item['data'] = $diyQuickNavForm->getQuickNav();
                                    } else {
                                        if (isset($item['data']['web']['url'])) {
                                            $item['data']['web']['url'] = urlencode($item['data']['web']['url']);
                                        }
                                        $arr = explode(',', $item['data']['mapNav']['location']);
                                        $item['data']['mapNav']['latitude'] = isset($arr[0]) ? $arr[0] : 0;
                                        $item['data']['mapNav']['longitude'] = isset($arr[1]) ? $arr[1] : 0;
                                    }
                                    // 权限判断
                                    if (isset($item['data']['customize']['key']) && !$checkPermission->check($item['data']['customize']['key'])) {
                                        $item['data']['customize']['opened'] = false;
                                    }
                                }
                                break;
                            case 'topic':
                                $diyTopicsForm = DiyTopicsForm::getInstance();
                                $item['data'] = $diyTopicsForm->getNewTopics($item['data']);
                                break;
                            case 'video':
                                $item['data']['url'] = Video::getUrl($item['data']['url']);
                                break;
                            case 'copyright':
                                if (isset($item['data']['link']['data']['params'])) {
                                    $newParams = [];
                                    foreach ($item['data']['link']['data']['params'] as $param) {
                                        $newParams[] = [
                                            'key' => $param['key'],
                                            'value' => $param['value']
                                        ];
                                    }
                                    $item['data']['params'] = $newParams;
                                }
                                if (isset($item['data']['link']['data']['key']) && !$checkPermission->check($item['data']['link']['data']['key'])) {
                                    $item['data']['link'] = [];
                                }
                                break;
                            case 'map':
                                $arr = explode(',', $item['data']['location']);
                                $item['data']['latitude'] = isset($arr[0]) ? $arr[0] : 0;
                                $item['data']['longitude'] = isset($arr[1]) ? $arr[1] : 0;
                                break;
                            case 'live':
                                $diyLiveForm = new DiyLiveForm();
                                $item['data'] = $diyLiveForm->getNewList($item['data'], $diyLiveForm->getLiveList());
                                break;
                            case 'modal':
                                $newList = [];
                                foreach ($item['data']['list'] as $dItem) {
                                    $newList[] = $dItem;
                                }
                                $item['data']['list'] = [];
                                if (count($newList) > 0) {
                                    $item['data']['list'][] = $newList;
                                }
                                break;
                            default:
                        }
                    }
                    unset($item);
                } else {
                    $nav['template']['data'] = [];
                }
                // 是设置首页的diy页面才添加裂变红包广告
                if ($page['is_home_page'] == 1 && $index == 0) {
                    $modal = (new DiyModalForm())->getModal();
                    if (count($modal) > 0) {
                        $nav['template']['data'][] = [
                            'id' => 'modal',
                            'data' => [
                                'opened' => true,
                                'times' => 1,
                                'list' => [$modal]
                            ]
                        ];
                    }
                }
            }
            unset($nav);
        }
        return $page;
    }

    public function getData($pageId, $isIndex)
    {
        if (!$pageId) {
            if (!$isIndex) {
                throw new \Exception('页面不存在');
            }
            $exists = DiyPage::find()->where([
                'is_home_page' => 1,
                'mall_id' => $this->mall->id,
                'is_disable' => 0,
                'is_delete' => 0
            ])->exists();
            if (!$exists) {
                throw new \Exception('页面不存在');
            }
        }
        $query = DiyPage::find()->select('id,title,show_navs,is_home_page')
            ->where([
                'mall_id' => $this->mall->id,
                'is_disable' => 0,
                'is_delete' => 0,
            ])->with(['navs' => function ($query) {
                $query->select('id,name,page_id,template_id')->with(['template' => function ($query) {
                    $query->select('id,name,data')->where(['is_delete' => 0]);
                }]);
            }]);
        if ($pageId) {
            $query->andWhere(['id' => $pageId]);
        } else {
            if ($isIndex) {
                $query->andWhere(['is_home_page' => 1]);
            } else {
                throw new \Exception('页面不存在');
            }
        }
        $page = $query->asArray()->one();
        if (!$page) {
            throw new \Exception('页面不存在');
        }
        foreach ($page['navs'] as &$nav) {
            if (!empty($nav['template']['data'])) {
                $nav['template']['data'] = Json::decode($nav['template']['data'], true);
            }
        }
        unset($nav);
        return $page;
    }

    public function getIndexExtra($array)
    {
        if (!isset($array['homePages']) || !$array['homePages'] || empty($array['homePages'])) {
            throw new \Exception('参数值错误');
        }
        $homePages = $array['homePages'];
        if (!isset($homePages['navs'][$array['nav_index']])) {
            throw new \Exception('参数值错误，请刷新重试');
        }
        $nav = $homePages['navs'][$array['nav_index']];
        if (!isset($nav['template']['data'][$array['index']]) || $nav['template']['data'][$array['index']]['id'] != $array['key']) {
            throw new \Exception('参数值错误，请刷新重试');
        }

        $data = null;
        $homePage = $nav['template']['data'][$array['index']];
        switch ($array['key']) {
            case 'goods':
                $diyGoodsForm = new DiyGoodsForm();
                $goodsIds = $diyGoodsForm->getGoodsIds($homePage['data']);
                $goodsCats = $diyGoodsForm->getCats($homePage['data']);
                $diyGoods = !empty($goodsIds) ? $diyGoodsForm->getGoodsById($goodsIds) : [];
                $diyCatsGoods = !empty($goodsCats) ? $diyGoodsForm->getGoodsByCat($goodsCats) : [];
                $data = $diyGoodsForm->getNewGoods($homePage['data'], $diyGoods, $diyCatsGoods);
                break;
            case 'store':
                $diyStoreForm = new DiyStoreForm();
                $storeIds = $diyStoreForm->getStoreIds($homePage['data']);
                $diyStores = $diyStoreForm->getStoreById($storeIds);
                $data = $diyStoreForm->getNewStore(
                    $homePage['data'],
                    $diyStores,
                    $array['longitude'],
                    $array['latitude']
                );
                break;
            case '':
                break;
        }
        return $data;
    }
}
