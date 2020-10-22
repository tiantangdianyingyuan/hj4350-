<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/14
 * Time: 10:52
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api;


use app\forms\common\goods\CommonGoodsDetail;
use app\models\Mall;
use app\models\User;
use app\plugins\community\forms\common\CommonActivity;
use app\plugins\community\forms\common\CommonMiddleman;
use app\plugins\community\forms\common\CommonSetting;
use app\plugins\community\forms\Model;
use app\plugins\community\models\CommunityAddress;
use app\plugins\community\models\CommunityGoods;
use app\plugins\community\models\CommunityMiddleman;
use app\plugins\community\models\CommunityRelations;
use yii\helpers\ArrayHelper;

/**
 * Class GoodsForm
 * @package app\plugins\community\forms\api
 * @property Mall $mall
 * @property User $user
 */
class GoodsForm extends Model
{
    public $goods_id;
    public $middleman_id;
    public $longitude;
    public $latitude;
    public $user;
    public $mall;

    public function rules()
    {
        return [
            [['goods_id', 'middleman_id'], 'integer'],
            [['longitude', 'latitude'], 'string']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $communityGoods = CommunityGoods::findOne(['goods_id' => $this->goods_id]);
            $activity = CommonActivity::getActivity($communityGoods->activity_id);
            if (!$activity || $activity->status != 1) {
                throw new \Exception('所选活动不存在');
            }
            $commonMiddleman = CommonMiddleman::getCommon();
            $middlemanUserId = CommunityRelations::find()->where(['user_id' => $this->user->id, 'is_delete' => 0])
                ->select('middleman_id');
            $middleman = CommunityMiddleman::findOne(['user_id' => $middlemanUserId, 'status' => 1]);
            if (!$middleman) {
                $middleman = $commonMiddleman->getConfig($this->middleman_id);
                if (!$middleman || $middleman->status != 1) {
                    $middleman = $commonMiddleman->getMiddlemanByDistance($this->longitude, $this->latitude);
                }
            }
            $notJoinGoods = $commonMiddleman->getNotJoin($middleman, $communityGoods->activity_id);
            if ($notJoinGoods && !empty($notJoinGoods) && in_array($this->goods_id, $notJoinGoods)) {
                throw new \Exception('所选商品不在活动中');
            }
            $form = new CommonGoodsDetail();
            $form->user = \Yii::$app->user->identity;
            $form->mall = \Yii::$app->mall;
            $goods = $form->getGoods($this->goods_id);
            if (!$goods || $goods->status != 1) {
                throw new \Exception('商品不存在');
            }

            $form->goods = $goods;
            $form->setMember(false);
            $cats = array_column(ArrayHelper::toArray($goods->goodsWarehouse->cats), 'id');
            $cats = array_map(function ($v) {
                return (string)$v;
            }, $cats);
            $res = $form->getAll();
            $res = array_merge($res, [
                //商品分类
                'cats' => $cats
            ]);
            $res['price'] = $res['price_min'];

            //图片替换
            $temp = [];
            foreach ($res['attr'] as $v) {
                foreach ($v['attr_list'] as $w) {
                    if (!isset($temp[$w['attr_id']])) {
                        $temp[$w['attr_id']] = $v['pic_url'];
                    }
                }
            }

            foreach ($res['attr_groups'] as $k => $v) {
                foreach ($v['attr_list'] as $l => $w) {
                    $res['attr_groups'][$k]['attr_list'][$l]['pic_url'] = $temp[$w['attr_id']] ?: "";
                }
            }
            $nowTime = time();
            $startTime = strtotime($activity->start_at);
            $endTime = strtotime($activity->end_at);
            if ($startTime >= $nowTime) {
                $status = 0;
                $time = intval($startTime - $nowTime);
            } elseif ($endTime <= $nowTime) {
                $status = 2;
                $time = intval($nowTime - $endTime);
            } else {
                $status = 1;
                $time = intval($endTime - $nowTime);
            }

            $setting = CommonSetting::getCommon()->getSetting();
            // 判断插件分销是否开启
            if (!$setting['is_share']) {
                $res['share'] = 0;
            }
            return $this->success([
                'goods' => $res,
                'middleman' => [
                    'id' => $middleman->id,
                    'user_id' => $middleman->user_id,
                    'name' => $middleman->name,
                    'mobile' => $middleman->mobile,
                    'avatar' => $middleman->user->userInfo->avatar,
                    'location' => $middleman->address->location,
                    'province' => $middleman->address->province,
                    'city' => $middleman->address->city,
                    'district' => $middleman->address->district,
                    'detail' => $middleman->address->detail,
                    'nickname' => $middleman->user->nickname,
                ],
                'activity' => [
                    'start_at' => $activity->start_at,
                    'end_at' => $activity->end_at,
                    'title' => $activity->title,
                    'status' => $status,
                    'time' => $time,
                    'id' => $activity->id,
                ]
            ]);
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }
}
