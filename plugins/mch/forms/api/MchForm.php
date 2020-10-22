<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\api;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsStatistic;
use app\forms\common\mch\MchSettingForm;
use app\forms\common\mch\SettingForm;
use app\forms\common\order\CommonOrderStatistic;
use app\forms\common\template\TemplateList;
use app\models\Model;
use app\models\Store;
use app\plugins\mch\forms\common\CommonCat;
use app\plugins\mch\forms\common\CommonMchForm;
use app\plugins\mch\models\Mch;
use app\plugins\mch\Plugin;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class MchForm extends Model
{
    public $keyword;
    public $page;
    public $id;
    public $mch_common_cat_id;
    public $longitude;
    public $latitude;
    public $is_review_status;
    public $mch_id;

    public function rules()
    {
        return [
            [['keyword', 'longitude', 'latitude'], 'string'],
            [['id', 'mch_common_cat_id', 'is_review_status', 'mch_id'], 'integer'],
            [['page',], 'default', 'value' => 1],
            [['longitude', 'latitude',], 'default', 'value' => 0],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = Mch::find()->alias('m')->where([
            'm.mall_id' => \Yii::$app->mall->id,
            'm.is_delete' => 0,
            'm.review_status' => 1,
            'm.is_recommend' => 1,
        ])->select('m.*');

        if ($this->mch_common_cat_id) {
            $query->andWhere(['m.mch_common_cat_id' => $this->mch_common_cat_id]);
        }


        $storeQuery = Store::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->andWhere(['>', 'mch_id', 0]);
        if ($this->keyword) {
            $mchIds = (new Query())->from(['s' => $storeQuery])
                ->where(['like', 's.name', $this->keyword])->addSelect('mch_id');
            $query->andWhere(['m.id' => $mchIds]);
        }

        $form = new MchSettingForm();
        $setting = $form->search();

        if ($setting['is_distance'] == 1 && $this->longitude && $this->latitude) {
            $mchIds = (new Query())->from(['s' => $storeQuery])->addSelect([
                'jl' => 'ROUND(
        6378.138 * 2 * ASIN(
            SQRT(
                POW(
                    SIN(
                        (
                            ' . $this->latitude . ' * PI() / 180 - latitude * PI() / 180
                        ) / 2
                    ),
                    2
                ) + COS(' . $this->latitude . ' * PI() / 180) * COS(latitude * PI() / 180) * POW(
                    SIN(
                        (
                            ' . $this->longitude . ' * PI() / 180 - longitude * PI() / 180
                        ) / 2
                    ),2
                )
            )
          )
        )'
            ])->where('s.mch_id=m.id');
            $query->addSelect(['juli' => $mchIds])->orderBy(['juli' => SORT_ASC]);
        } else {
            $query->orderBy(['m.sort' => SORT_ASC]);
        }

        $list = $query->with('user.userInfo', 'store', 'category')
            ->page($pagination, 10)->asArray()->all();

        foreach ($list as &$item) {
            $form = new CommonGoodsStatistic();
            $form->mch_id = $item['id'];
            $form->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
            $res = $form->getAll(['goods_count']);
            $item['goods_count'] = $res['goods_count'];
            $pagination_2 = null;
            $item['goods'] = $form->query
                ->with('goodsWarehouse')
                ->joinWith(['mchGoods as mg' => function ($query) {
                    $query->andWhere(['mg.status' => 2]);
                }])
                ->andWhere(['g.status' => 1])
                ->orderBy(['mg.sort' => SORT_ASC])
                ->page($pagination_2, 10, 1)
                ->asArray()
                ->all();


            $form = new CommonOrderStatistic();
            $form->mch_id = $item['id'];
            $form->sign = (new Plugin())->getName();
            $form->is_user = 1;
            $res = $form->getAll(['order_goods_count']);
            $item['order_goods_count'] = $res['order_goods_count'];

            $item['distance'] = '';
            $longitude = isset($item['store']) ? $item['store']['longitude'] : '';
            $latitude = isset($item['store']) ? $item['store']['latitude'] : '';
            if (isset($item['juli']) && $longitude && $latitude) {
                $item['distance'] = $item['juli'] . 'km';
            }
        }
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $form = new CommonMchForm();
            $form->id = $this->id;
            $form->is_review_status = $this->is_review_status;
            $detail = $form->getDetail();
            $newDetail = ArrayHelper::toArray($detail);
            $newDetail['user'] = $detail->user ? ArrayHelper::toArray($detail->user->userInfo) : [];
            $newDetail['mchUser'] = ArrayHelper::toArray($detail->mchUser);
            $newDetail['store'] = ArrayHelper::toArray($detail->store);
            $newDetail['category'] = ArrayHelper::toArray($detail->category);

            // 商户商品统计
            $form = new CommonGoodsStatistic();
            $form->mch_id = $this->id;
            $form->sign = (new Plugin())->getName();
            $res = $form->getAll(['goods_count']);
            $newDetail['goods_count'] = $res['goods_count'];

            // 商户订单统计
            $form = new CommonOrderStatistic();
            $form->mch_id = $this->id;
            $form->sign = (new Plugin())->getName();
            $form->is_user = 1;
            $res = $form->getAll(['order_goods_count']);
            $newDetail['order_goods_count'] = $res['order_goods_count'];

            // 多商户设置
            $form = new \app\forms\common\mch\SettingForm();
            $form->mch_id = $this->id;
            $setting = $form->search();
            $newDetail['mch_setting'] = $setting;

            $form = new SettingForm();
            $form->mch_id = $this->id;
            $setting = $form->search();
            $setting['web_service_url'] = urlencode($setting['web_service_url']);


            $latitude1 = $this->latitude ?: 0;
            $longitude1 = $this->longitude ?: 0;
            $latitude2 = $detail->store && $detail->store->latitude ? $detail->store->latitude : 0;
            $longitude2 = $detail->store && $detail->store->longitude ? $detail->store->longitude : 0;
            if ($latitude1 && $longitude1 && $latitude2 && $longitude2) {
                $distance = $this->getDistance($latitude1, $longitude1, $latitude2, $longitude2);
            }else {
                $distance = '';
            }
            $newDetail['distance'] = $distance ? round($distance, 2) . 'km' : '';


            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $newDetail,
                    'mchSetting' => $setting,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function getCategory()
    {

        $form = new CommonCat();
        $list = $form->getAllList();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
            ]
        ];
    }

    public function setting()
    {
        try {
            $form = new MchSettingForm();
            $form->isDefaultCashType = true;
            $res = $form->search();

            // TODO 此代码不应写在这里，此接口在入驻申请和商户提现时都有用到，mch_id不传会有bug
            if ($this->mch_id) {
                /** @var Mch $mch */
                $mch = Mch::find()->where(['id' => $this->mch_id])->with('user')->one();
                if (!$mch) {
                    throw new \Exception('商户不存在');
                }
                // 商户未绑定小程序用户情况下，没有自动打款
                if (!$mch->user) {
                    foreach ($res['cash_type'] as $key => $item) {
                        if ($item == 'auto') {
                            unset($res['cash_type'][$key]);
                        }
                    }
                    $res['cash_type'] = array_values($res['cash_type']);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'setting' => $res,
                    'template_message_list' => $this->getWithdrawTemplateMessage(),
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    private function getWithdrawTemplateMessage()
    {
        $arr = ['withdraw_success_tpl', 'withdraw_error_tpl'];
        $list = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $arr);
        return $list;
    }

    public function mchStatus()
    {
        $query = Mch::find()->where([
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        // 微信登录、获取商户审核状态都用到此接口，
        // 微信登录需判断商户已通过审核的
        if ($this->is_review_status) {
            $query->andWhere(['review_status' => 1]);
        }
        $mch = $query->one();

        $token = '';
        if ($mch) {
            $token = $this->setMchToken($mch->id);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => "请求成功",
            'data' => [
                'mch' => $mch,
                'token' => $token,
                'template_message_list' => $this->getTemplateMessage(),
            ]
        ];
    }

    private function getTemplateMessage()
    {
        $arr = ['audit_result_tpl'];
        $list = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $arr);
        return $list;
    }

    public function setMchToken($mchId)
    {
        $token = \Yii::$app->security->generateRandomString();
        \Yii::$app->cache->delete('MCHTOKENID' . $mchId);
        $res = \Yii::$app->cache->set('MCHTOKENID' . $mchId, $token, 15 * 24 * 60 * 60);
        return $token;
    }

    public function getMchSetting()
    {
        $form = new SettingForm();
        $form->mch_id = $this->mch_id;
        $setting = $form->search();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $setting
            ]
        ];
    }

    private function getDistance($lat1, $lng1, $lat2, $lng2)
    {

        // 将角度转为狐度
        $radLat1 = deg2rad($lat1);// deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);

        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;

        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137;

        return $s;

    }
}
