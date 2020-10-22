<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\forms\common;

use app\forms\api\app_platform\Transform;
use app\forms\common\order\CommonOrder;
use app\forms\common\video\Video;
use app\models\Model;
use app\plugins\diy\models\DiyPage;
use app\plugins\diy\models\DiyTemplate;

class CommonPageTwo extends Model
{
    public $mall;
    public $longitude;
    public $latitude;
    private $isLog = false;
    private $format = [
        'nav' => 'sNav',
        'module' => 'DELETE',
        'goods' => 'sGoods',
        'rubik' => 'sRubik',
        'coupon' => 'sCoupon',
        'mch' => 'sMch',
        'topic' => 'sTopic',
        'store' => 'sStore',
        'pintuan' => 'sPintuan',
        'booking' => 'sBooking',
        'miaosha' => 'sMiaosha',
        'bargain' => 'sBargain',
        'integral-mall' => 'sIntegralMall',
        'lottery' => 'sLottery',
        'quick-nav' => 'sQuickNav',
        'check-in' => 'sCheckIn',
        'advance' => 'sAdVance',
        'vipCard' => 'sVipCard',
        'pick' => 'sPick',
        'live' => 'sLive',
        'video' => 'sVideo',
        'copyright' => 'sCopyright',
        'user-order' => 'sUserOrder',
        'map' => 'sMap',
        'modal' => 'sModal',
        'banner' => 'sBanner',
        'gift' => 'sGift',
        'composition' => 'sComposition',
        'flash-sale' => 'sFlashSale',
        'exchange' => 'sExchange',
    ];
    //商品
    private $diyGoods = [];
    private $diyCatsGoods = [];
    //优惠券
    private $coupons = [];
    private $couponCenter = [];
    //N元任选
    private $pick;
    // 预售
    private $advance;
    //幸运抽奖
    private $diyLotteryGoods;
    //积分商城
    private $diyIntegralMallGoods;
    //
    private $diyBargainGoods;
    private $diyPintuanGoods;
    private $diyBookingGoods;
    private $diyMiaoshaGoods;
    //门店
    private $diyStores;
    //好店推荐
    private $diyMchGoods;
    private $diyMch;
    //礼物
    private $diyGiftGoods;
    //套餐组和
    private $compositionGoodsIds;
    //限时抢购
    private $flashSale;
    //礼品卡
    private $exchangeGoods;

    /** @var DiyTemplate $model */
    private $model;
    //循环中的template_id
    private $couponTemplateIdSign = 0;
    public static function getCommon($mall, $longitude = null, $latitude = null)
    {
        $common = new self();
        $common->mall = $mall;
        $common->longitude = $longitude;
        $common->latitude = $latitude;
        return $common;
    }

    /**
     * 商品数
     * @param null $templateId
     * @param bool $isIndex
     * @return int
     */
    public function getGoodsCount($templateId = null, $isIndex = false)
    {
        $format = [
            'module' => 'DELETE',
            'goods' => 'sGoods',
            'mch' => 'sMch',
            'pintuan' => 'sPintuan',
            'booking' => 'sBooking',
            'miaosha' => 'sMiaosha',
            'bargain' => 'sBargain',
            'integral-mall' => 'sIntegralMall',
            'lottery' => 'sLottery',
            'quick-nav' => 'sQuickNav',
            'advance' => 'sAdVance',
            'pick' => 'sPick',
            'gift' => 'sGift',
            'composition' => 'sComposition',
        ];
        $this->setFormatter($format);
        $data = $this->getPage($templateId, $isIndex);
        $goodsNum = 0;
        foreach ($data as $item) {
            if (in_array($item['id'], array_keys($format)) && isset($item['data']['list'])) {
                if ($item['id'] === 'mch') {
                    foreach ($item['data']['list'] as $item) {
                        $goodsNum += count($item['goodsList']);
                    }
                } else {
                    $goodsList = $item['data']['list'];
                    $goodsNum += count($goodsList);
                }
            }
        }
        return $goodsNum;
    }

    public function setFormatter(array $format)
    {
        $this->format = $format;
    }

    /**
     * 用户访问次数
     * @param bool $isLog
     */
    public function hasLog(bool $isLog)
    {
        $this->isLog = $isLog;
    }
    public function getLog()
    {
        $key = 'diy-access-page-count:' . $this->mall->id;
        return \Yii::$app->cache->get($key) ?: [];
    }
    private function setLog(int $template_id)
    {
        if (!$this->isLog) {
            return false;
        }
        $user_id = \Yii::$app->user->id;
        $values = $this->getLog();
        if (isset($values[$template_id])) {
            $users = $values[$template_id]['userIds'];
            $accessCount = $values[$template_id]['accessCount'];
        } else {
            $users = [];
            $accessCount = 0;
        }
        $accessCount++;
        if ($user_id && !in_array($user_id, $users)) {
            array_push($users, $user_id);
        }
        $value = [
            'userIds' => $users,
            'accessCount' => $accessCount,
        ];
        $values[$template_id] = $value;
        $key = 'diy-access-page-count:' . $this->mall->id;
        return \Yii::$app->cache->set($key, $values, 0);
    }
    //权限判断
    private function getPermission(string $key): bool
    {
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        return array_search($key, $permission) !== false;
    }
    //模板查询
    private function getModuleModel($id)
    {
        $query = DiyTemplate::find()->where([
            'mall_id' => $this->mall->id,
            'type' => DiyTemplate::TYPE_MODULE,
            'is_delete' => 0,
            'id' => $id,
        ]);
        $list = $query->asArray()->one();
        \Yii::warning('template id');
        \Yii::warning($list);
        if ($list) {
            return \yii\helpers\BaseJson::decode($list['data']);
        }
    }
    //页面查询
    private function getAppPageModel($pageId, $isIndex)
    {
        $query = DiyPage::find()->select('id,title,show_navs,is_home_page')->where([
            'mall_id' => $this->mall->id,
            'is_disable' => 0,
            'is_delete' => 0,
        ]);
        if ($pageId) {
            $query->andWhere(['id' => $pageId]);
        } elseif ($isIndex) {
            $query->andWhere(['is_home_page' => 1]);
        } else {
            throw new \Exception('页面不存在');
        }
        $model = $query->with('templateOne')->one();
        if (empty($model)) {
            throw new \Exception('页面不存在');
        }
        $this->model = $model;
        return $model;
    }

    //接口格式化兼容
    public function getPageFormat($pageId = null, $isIndex = false)
    {
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
        } elseif ($isIndex) {
            $query->andWhere(['is_home_page' => 1]);
        } else {
            throw new \Exception('页面不存在');
        }

        $model = $query->asArray()->one();
        $this->model = $model;
        if (!$model) {
            throw new \Exception('页面不存在');
        }
        foreach ($model['navs'] as &$nav) {
            if (isset($nav['template']['data'])) {
                $this->couponTemplateIdSign = $nav['template']['id'];
                $nav['template']['data'] = $this->getPage($pageId, $isIndex, $nav['template']['data']);
            }
        }
        unset($nav);
        return $model;
    }

    public function getPage($pageId = null, $isIndex = false, $oldData = null)
    {
        $format = $this->format;
        /** @var DiyPage $model */
        if (is_null($oldData)) {
            $this->getAppPageModel($pageId, $isIndex);
            $databaseData = $this->model->templateOne->data;
        } else {
            $databaseData = $oldData;
        }
        $databaseData = \yii\helpers\BaseJson::decode($databaseData);

        $funcZ = function (array $data) {
            return array_filter($data, function ($item) {
                return !(isset($item['permission_key']) && $item['permission_key'] && !$this->getPermission($item['permission_key']));
            });
        };
        //过滤权限
        $formatModel = new CommonFormat();
        $databaseData = $funcZ($databaseData);
        foreach ($databaseData as $key => $item) {
            $databaseData[$key] = $formatModel->handleOne($item);
            if ($item['id'] === 'module') {
                if (is_array($item['data']['list'])) {
                    foreach ($item['data']['list'] as $key2 => $item2) {
                        $list = $this->getModuleModel($item2['id']) ?: [];
                        $databaseData[$key]['data']['list'][$key2]['data'] = $formatModel->handleAll($funcZ($list));
                    }
                }
            }
        }
        //查询start
        $data = [];
        $func = function ($databaseData) use (&$func, &$data) {
            foreach ($databaseData as $item) {
                if ($item['id'] === 'module') {
                    if (isset($item['data']['list']) && is_array($item['data']['list'])) {
                        array_walk($item['data']['list'], function ($item) use ($func) {
                            $func($item['data']);
                        });
                    }
                } else {
                    $data[] = $item;
                }
            }
        };
        $func($databaseData);
        //数组整理
        $newData = [];
        foreach ($data as $v) {
            $newData[$v['id']][] = $v;
        }

        //is_callable(array($this, $format[$key])
        foreach ($newData as $key => $value) {
            if (isset($format[$key]) && method_exists($this, $format[$key])) {
                $method = $format[$key];
                $this->$method(false, $value);
            }
        }

        //后续拼接
        $funcA = function ($realData) use (&$funcA, $format) {
            foreach ($realData as $key => $item) {
                if (isset($format[$item['id']]) && method_exists($this, $format[$item['id']])) {
                    $method = $format[$item['id']];
                    $realData[$key] = $this->$method(true, $item);
                }
                if ($item['id'] === 'module') {
                    if (isset($item['data']['list']) && is_array($item['data']['list'])) {
                        foreach ($item['data']['list'] as $key1 => $module) {
                            $realData[$key]['data']['list'][$key1]['data'] = $funcA($module['data']);
                        }
                    }
                }
            }
            return $realData;
        };
        $data = $funcA($databaseData);
        //区分
        $data = $this->HFxhb($this->model['is_home_page'] == 1, $data);
        // 将背景组件放到首位
        $temp = [];
        foreach ($data as $key => $datum) {
            if ($datum['id'] == 'background') {
                $temp = $datum;
                unset($data[$key]);
                break;
            }
        }
        if (!empty($temp)) {
            array_unshift($data, $temp);
            $data = array_values($data);
        }
        //日志
        $this->isLog && $this->setLog($this->model['id']);
        return $data;
    }
    //首页裂变拆红包
    protected function HFxhb(bool $is_show, array $arr)
    {
        if ($is_show) {
            $modal = (new DiyModalForm())->getModal();
            if (count($modal) > 0) {
                array_push($arr, [
                    'id' => 'modal',
                    'data' => [
                        'opened' => true,
                        'times' => 1,
                        'list' => [$modal]
                    ]
                ]);
            }
        }
        return $arr;
    }
    //优惠券
    protected function sCoupon(bool $is_show, array $arr)
    {
        if ($is_show) {
            if (isset($arr['data']['addType']) && $arr['data']['addType'] === 'manual') {
                $couponList = (new DiyCouponForm())->selectIds($arr['data']['coupons'], $this->coupons, $arr['data']);
            } else {
                $couponList = (new DiyCouponForm())->selectIds($this->couponCenter, $this->couponCenter, $arr['data']);
            }
            //自动
            $arr['data']['coupon_list'] = $couponList;
            unset($arr['data']['coupons']);
            return $arr;
        } else {
            $couponIds = [];
            $sentinel = true;
            foreach ($arr as $coupon) {
                if (isset($coupon['data']['addType']) && $coupon['data']['addType'] === 'manual' && is_array($coupon['data']['coupons'])) {
                    $coupon = $coupon['data']['coupons'];
                    $couponIds = array_merge(array_column($coupon, 'id'), $couponIds);
                } else {
                    if ($sentinel) {
                        $diyCouponForm = new DiyCouponForm();
                        $this->couponCenter = $diyCouponForm->getCoupons();
                        $sentinel = false;
                    }
                }
            }

            if (!empty($couponIds)) {
                $diyCouponForm = new DiyCouponForm();
                $this->coupons = $diyCouponForm->getCouponIds($couponIds, $this->couponTemplateIdSign);
            }
        }
    }
    //导航
    protected function sNav(bool $is_show, array $arr)
    {
        if ($is_show) {
            $app_admin = true;
            if (
                empty(\Yii::$app->plugin->getInstalledPlugin('app_admin'))
                || !$this->getPermission('app_admin')
                || empty(\Yii::$app->user->identity->identity->is_admin)
                || \Yii::$app->user->identity->identity->is_admin != 1
            ) {
                $app_admin = false;
            }

            foreach ($arr['data']['navs'] as $i => $v) {
                // 判断权限显示
                if (isset($v['key']) && $v['key'] && !$this->getPermission($v['key'])) {
                    unset($arr['data']['navs'][$i]);
                    continue;
                }
                if ($v['openType'] == 'app_admin' && !$app_admin) {
                    unset($arr['data']['navs'][$i]);
                    continue;
                }
                if ($v['openType'] == 'contact' && \Yii::$app->appPlatform === APP_PLATFORM_TTAPP) {
                    unset($arr['data']['navs'][$i]);
                    continue;
                }
                $arr['data']['navs'] = array_values($arr['data']['navs']);
            }

            foreach ($arr['data']['navs'] as $key => $value) {
                $arr['data']['navs'][$key]['icon_url'] = $value['icon'];
                $arr['data']['navs'][$key]['link_url'] = $value['url'];
                $arr['data']['navs'][$key]['open_type'] = $value['openType'];
            }

            $arr['data']['navs'] = Transform::getInstance()->transformHomeNav($arr['data']['navs']);
            return $arr;
        }
    }
    //商品
    protected function sGoods(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyGoodsForm = new DiyGoodsForm();
            $arr['data'] = $diyGoodsForm->getNewGoods($arr['data'], $this->diyGoods, $this->diyCatsGoods);
            return $arr;
        } else {
            $diyGoodsForm = new DiyGoodsForm();
            $goodsIds = $goodsCats = [];
            foreach ($arr as $goods) {
                $goodsIds = array_merge($diyGoodsForm->getGoodsIds($goods['data']), $goodsIds);
                $goodsCats = $diyGoodsForm->getCats($goods['data'], $goodsCats);
            }
            $this->diyGoods = $diyGoodsForm->getGoodsById($goodsIds);
            $this->diyCatsGoods = $diyGoodsForm->getGoodsByCat($goodsCats);
        }
    }
    // 图片广告
    protected function sRubik(bool $is_show, array $arr)
    {
        if ($is_show) {
            foreach ($arr['data']['list'] as $rIndex => $rItem) {
                if (isset($rItem['link']['key']) && $rItem['link']['key'] && !$this->getPermission($rItem['link']['key'])) {
                    $arr['data']['list'][$rIndex]['link']['value'] = '';
                    $arr['data']['list'][$rIndex]['link']['new_link_url'] = '';
                    continue;
                }
            }
            foreach ($arr['data']['hotspot'] as $hIndex => $hItem) {
                if (isset($hItem['link']['key']) && $hItem['link']['key'] && !$this->getPermission($hItem['link']['key'])) {
                    $arr['data']['hotspot'][$hIndex]['link']['value'] = '';
                    $arr['data']['hotspot'][$hIndex]['link']['new_link_url'] = '';
                    continue;
                }
            }
            return $arr;
        }
    }
    //多商户
    protected function sMch(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyMchForm = new DiyMchForm();
            $arr['data'] = $diyMchForm->getNewMch($arr['data'], $this->diyMchGoods, $this->diyMch);
            return $arr;
        } else {
            $diyMchForm = new DiyMchForm();
            $mchIds = $mchGoodsIds = [];
            foreach ($arr as $goods) {
                $res = $diyMchForm->getMchData($goods['data']);
                $mchIds = array_merge($res['mchIds'], $mchIds);
                $mchGoodsIds = array_merge($res['mchGoodsIds'], $mchGoodsIds);
            }
            $this->diyMchGoods = $diyMchForm->getMchGoodsById($mchGoodsIds);
            $this->diyMch = $diyMchForm->getMch($mchIds);
        }
    }
    //专题
    protected function sTopic(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyTopicsForm = DiyTopicsForm::getInstance();
            $arr['data'] = $diyTopicsForm->getNewTopics($arr['data']);
            return $arr;
        } else {
            $diyTopicsForm = DiyTopicsForm::getInstance();
            foreach ($arr as $item) {
                if (isset($item['data']['style']) && $item['data']['style'] === 'list') {
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
        }
    }
    // 门店
    protected function sStore(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyStoreForm = new DiyStoreForm();
            $arr['data'] = $diyStoreForm->getNewStore($arr['data'], $this->diyStores, $this->longitude, $this->latitude);
            return $arr;
        } else {
            $diyStoreForm = new DiyStoreForm();
            $storeIds = [];
            foreach ($arr as $goods) {
                $storeIds = array_merge($diyStoreForm->getStoreIds($goods['data']), $storeIds);
            }
            $this->diyStores = $diyStoreForm->getStoreById($storeIds);
        }
    }
    //拼团
    protected function sPintuan(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyPintuanForm = new DiyPintuanForm();
            $arr['data'] = $diyPintuanForm->getNewGoods($arr['data'], $this->diyPintuanGoods);
            return $arr;
        } else {
            $diyPintuanForm = new DiyPintuanForm();
            $pintuanGoodsIds = [];
            foreach ($arr as $goods) {
                $pintuanGoodsIds = array_merge($diyPintuanForm->getGoodsIds($goods['data']), $pintuanGoodsIds);
            }
            $this->diyPintuanGoods = $diyPintuanForm->getGoodsById($pintuanGoodsIds);
        }
    }
    //预约
    protected function sBooking(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyBookingForm = new DiyBookingForm();
            $arr['data'] = $diyBookingForm->getNewGoods($arr['data'], $this->diyBookingGoods);
            return $arr;
        } else {
            $diyBookingForm = new DiyBookingForm();
            $bookingGoodsIds = [];
            foreach ($arr as $goods) {
                $bookingGoodsIds = array_merge($diyBookingForm->getGoodsIds($goods['data']), $bookingGoodsIds);
            }
            $this->diyBookingGoods = $diyBookingForm->getGoodsById($bookingGoodsIds);
        }
    }
    //秒杀
    protected function sMiaosha(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyMiaoshaForm = new DiyMiaoshaForm();
            $arr['data'] = $diyMiaoshaForm->getNewGoods($arr['data'], $this->diyMiaoshaGoods);
            return $arr;
        } else {
            $diyMiaoshaForm = new DiyMiaoshaForm();
            $miaoshaGoodsIds = [];
            foreach ($arr as $goods) {
                $miaoshaGoodsIds = array_merge($diyMiaoshaForm->getGoodsIds($goods['data']), $miaoshaGoodsIds);
            }
            $this->diyMiaoshaGoods = $diyMiaoshaForm->getGoodsById($miaoshaGoodsIds);
        }
    }
    //砍价
    protected function sBargain(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyBargainForm = new DiyBargainForm();
            $arr['data'] = $diyBargainForm->getNewGoods($arr['data'], $this->diyBargainGoods);
            return $arr;
        } else {
            $diyBargainForm = new DiyBargainForm();
            $bargainGoodsIds = [];
            foreach ($arr as $goods) {
                $bargainGoodsIds = array_merge($diyBargainForm->getGoodsIds($goods['data']), $bargainGoodsIds);
            }
            $this->diyBargainGoods = $diyBargainForm->getGoodsById($bargainGoodsIds);
        }
    }
    //积分商城
    protected function sIntegralMall(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyIntegralMallForm = new DiyIntegralMallForm();
            $arr['data'] = $diyIntegralMallForm->getNewGoods($arr['data'], $this->diyIntegralMallGoods);
            return $arr;
        } else {
            $diyIntegralMallForm = new DiyIntegralMallForm();
            $integralMallGoodsIds = [];
            foreach ($arr as $goods) {
                $integralMallGoodsIds = array_merge($diyIntegralMallForm->getGoodsIds($goods['data']), $integralMallGoodsIds);
            }
            $this->diyIntegralMallGoods = $diyIntegralMallForm->getGoodsById($integralMallGoodsIds);
        }
    }
    //幸运抽奖
    protected function sLottery(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyLotteryForm = new DiyLotteryForm();
            $arr['data'] = $diyLotteryForm->getNewGoods($arr['data'], $this->diyLotteryGoods);
            return $arr;
        } else {
            $diyLotteryForm = new DiyLotteryForm();
            $lotteryGoodsIds = [];
            foreach ($arr as $goods) {
                $lotteryGoodsIds = array_merge($diyLotteryForm->getGoodsIds($goods['data']), $lotteryGoodsIds);
            }
            $this->diyLotteryGoods = $diyLotteryForm->getGoodsById($lotteryGoodsIds);
        }
    }
    //快捷导航
    protected function sQuickNav(bool $is_show, array $arr)
    {
        if ($is_show) {
            if (
                isset($arr['data']['customize']['key'])
                && $arr['data']['customize']['key']
                && $this->getPermission($arr['data']['customize']['key'])
            ) {
                $arr['data']['customize']['opened'] = false;
            }
            if (isset($arr['data']['navSwitch']) && $arr['data']['navSwitch'] && $arr['data']['useMallConfig']) {
                $diyQuickNavForm = new DiyQuickNavForm();
                $quickNav = array_merge($diyQuickNavForm->getQuickNav());
                $arr['data'] = $quickNav;
            } else {
                if (isset($arr['data']['web']['url'])) {
                    $arr['data']['web']['url'] = urlencode($arr['data']['web']['url']);
                }
                $location = explode(',', $arr['data']['mapNav']['location']);
                $arr['data']['mapNav']['latitude'] = isset($location[0]) ? $location[0] : 0;
                $arr['data']['mapNav']['longitude'] = isset($location[1]) ? $location[1] : 0;
            }
            return $arr;
        }
    }
    //签到
    protected function sCheckIn(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyCheckInForm = new DiyCheckInForm();
            $checkIn = $diyCheckInForm->getCheckIn();
            $arr['data']['award'] = $checkIn;
            return $arr;
        }
    }
    //预售
    protected function sAdvance(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyAdvanceForm = new DiyAdvanceForm();
            $arr['data'] = $diyAdvanceForm->getNewGoods($arr['data'], $this->advance);
            return $arr;
        } else {
            $diyAdvanceForm = new DiyAdvanceForm();
            $advanceGoodsIds = [];
            foreach ($arr as $goods) {
                $advanceGoodsIds = array_merge($diyAdvanceForm->getGoodsIds($goods['data']), $advanceGoodsIds);
            }
            $this->advance = $diyAdvanceForm->getGoodsById($advanceGoodsIds);
        }
    }
    //超级会员卡
    protected function sVipCard(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyVipCardForm = new DiyVipCardForm();
            $vipCard = $diyVipCardForm->getVipCard();
            $arr['data']['vip_card'] = $vipCard;
            return $arr;
        }
    }
    //N元任选
    protected function sPick(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyPickForm = new DiyPickForm();
            $arr['data'] = $diyPickForm->getNewGoods($arr['data'], $this->pick);
            return $arr;
        } else {
            $diyPickForm = new DiyPickForm();
            $pickGoodsIds = [];
            foreach ($arr as $goods) {
                $pickGoodsIds = array_merge($diyPickForm->getGoodsIds($goods['data']), $pickGoodsIds);
            }
            $this->pick = $diyPickForm->getGoodsById($pickGoodsIds);
        }
    }
    //直播
    protected function sLive(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyLiveForm = new DiyLiveForm();
            $liveList = $diyLiveForm->getLiveList();
            $arr['data'] = $diyLiveForm->getNewList($arr['data'], $liveList);
            return $arr;
        }
    }
    //视频
    protected function sVideo(bool $is_show, array $arr)
    {
        if ($is_show) {
            $arr['data']['url'] = Video::getUrl($arr['data']['url']);
            return $arr;
        }
    }
    //版权
    protected function sCopyright(bool $is_show, array $arr)
    {
        if ($is_show) {
            if (isset($arr['data']['link']['data']['params'])) {
                $newParams = [];
                foreach ($arr['data']['link']['data']['params'] as $param) {
                    $newParams[] = [
                        'key' => $param['key'],
                        'value' => $param['value']
                    ];
                }
                $arr['data']['params'] = $newParams;
            }
            return $arr;
        }
    }
    //用户订单
    protected function sUserOrder(bool $is_show, array $arr)
    {
        if ($is_show) {
            $res = (new CommonOrder())->getOrderInfoCount();
            foreach ($arr['data']['navs'] as $key => $value) {
                $arr['data']['navs'][$key]['url'] = '/pages/order/index/index?status=' . ($key + 1);
                $arr['data']['navs'][$key]['num'] = $res[$key];
                $arr['data']['navs'][$key]['open_type'] = $value['openType'];
                $arr['data']['navs'][$key]['link_url'] = $arr['data']['navs'][$key]['url'];
                $arr['data']['navs'][$key]['name'] = $value['text'];
                $arr['data']['navs'][$key]['icon_url'] = $value['picUrl'];
            }
            return $arr;
        }
    }
    //地图组件
    protected function sMap(bool $is_show, array $arr)
    {
        if ($is_show) {
            $location = explode(',', $arr['data']['location']);
            $arr['data']['latitude'] = isset($location[0]) ? $location[0] : 0;
            $arr['data']['longitude'] = isset($location[1]) ? $location[1] : 0;
            return $arr;
        }
    }
    //弹窗广告
    protected function sModal(bool $is_show, array $arr)
    {
        if ($is_show) {
            $newList = [];
            foreach ($arr['data']['list'] as $dItem) {
                $newList[] = $dItem;
            }
            $arr['data']['list'] = [];
            if (count($newList) > 0) {
                $arr['data']['list'][] = $newList;
            }
            return $arr;
        }
    }
    // 轮播图
    protected function sBanner(bool $is_show, array $arr)
    {
        if ($is_show) {
            $bannerForm = new DiyBannerForm();
            $arr['data'] = $bannerForm->getNewBanner($arr['data']);
            return $arr;
        }
    }
    //社交送礼
    protected function sGift(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyGiftForm = new DiyGiftForm();
            $arr['data'] = $diyGiftForm->getNewGoods($arr['data'], $this->diyGiftGoods);
            return $arr;
        } else {
            $diyGiftForm = new DiyGiftForm();
            $giftGoodsIds = [];
            foreach ($arr as $goods) {
                $giftGoodsIds = array_merge($diyGiftForm->getGoodsIds($goods['data']), $giftGoodsIds);
            }

            $this->diyGiftGoods = $diyGiftForm->getGoodsById($giftGoodsIds);
        }
    }
    //套餐组合
    protected function sComposition(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyCompositionForm = new DiyCompositionForm();
            $arr['data'] = $diyCompositionForm->getNewGoods($arr['data'], $this->compositionGoodsIds);
            return $arr;
        } else {
            $diyCompositionForm = new DiyCompositionForm();
            $compositionGoodsIds = [];
            foreach ($arr as $goods) {
                $compositionGoodsIds = array_merge($diyCompositionForm->getGoodsIds($goods['data']), $compositionGoodsIds);
            }
            $this->compositionGoodsIds = $diyCompositionForm->getGoodsById($compositionGoodsIds);
        }
    }
    //限时抢购
    protected function sFlashSale(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyFlashSaleForm = new DiyFlashSaleForm();
            $arr['data'] = $diyFlashSaleForm->getNewGoods($arr['data'], $this->flashSale);
            return $arr;
        } else {
            $diyFlashSaleForm = new DiyFlashSaleForm();
            $flashSaleGoodsIds = [];
            foreach ($arr as $goods) {
                $flashSaleGoodsIds = array_merge(
                    $diyFlashSaleForm->getGoodsIds($goods['data']),
                    $flashSaleGoodsIds
                );
            }
            $this->flashSale = $diyFlashSaleForm->getGoodsById($flashSaleGoodsIds);
        }
    }

    //兑换
    protected function sExchange(bool $is_show, array $arr)
    {
        if ($is_show) {
            $diyExchangeForm = new DiyExchangeForm();
            $arr['data'] = $diyExchangeForm->getNewGoods($arr['data'], $this->exchangeGoods);
            return $arr;
        } else {
            $diyExchangeForm = new DiyExchangeForm();
            $ExchangeGoodsIds = [];
            foreach ($arr as $goods) {
                $ExchangeGoodsIds = array_merge(
                    $diyExchangeForm->getGoodsIds($goods['data']),
                    $ExchangeGoodsIds
                );
            }
            $this->exchangeGoods = $diyExchangeForm->getGoodsById($ExchangeGoodsIds);
        }
    }
}
