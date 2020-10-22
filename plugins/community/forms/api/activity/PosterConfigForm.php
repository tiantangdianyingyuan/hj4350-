<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/23
 * Time: 14:31
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api\activity;


use app\forms\api\poster\parts\PosterBg;
use app\forms\common\CommonQrCode;
use app\models\User;
use app\plugins\community\forms\common\CommonActivity;
use app\plugins\community\forms\common\CommonMiddleman;
use app\plugins\community\forms\common\CommonSetting;
use app\plugins\community\forms\Model;

class PosterConfigForm extends Model
{
    public $activity_id;
    public $middleman_id;

    public function rules()
    {
        return [
            [['activity_id', 'middleman_id'], 'required'],
            [['activity_id', 'middleman_id'], 'integer'],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $common = CommonSetting::getCommon();
            $setting = $common->getSetting();
            /* @var User $user */
            $user = \Yii::$app->user->identity;
            $middleman = $this->getMiddleman($user);
            $activity = CommonActivity::getActivity($this->activity_id);
            if (!$activity || $activity->status != 1) {
                throw new \Exception('活动不存在，无法分享');
            }
            $commonMiddleman = CommonMiddleman::getCommon();
            $notJoin = $commonMiddleman->getNotJoin($middleman, $activity->id);
            $goodsList = [];
            foreach ($activity->communityGoods as $goods) {
                if (in_array($goods->goods_id, $notJoin)
                    || $goods->goods->is_delete == 1
                    || $goods->goods->status == 0) {
                    continue;
                }
                if (count($goodsList) >= 6) {
                    break;
                }
                $goodsList[] = [
                    'cover_pic' => $goods->goods->coverPic,
                    'name' => $goods->goods->name,
                    'original_price' => $goods->goods->originalPrice,
                    'price' => $goods->goods->price,
                ];
            }
            return $this->success([
                'config' => [
                    'activity_poster_style' => $setting['activity_poster_style'],
                    'image_bg' => $setting['image_bg'],
                    'color' => PosterBg::COLOR_LIST
                ],
                'info' => [
                    'nickname' => $user->nickname,
                    'avatar' => $user->userInfo->avatar,
                ],
                'activity' => [
                    'title' => $activity->title,
                    'goods_list' => $goodsList,
                    'end_at' => $activity->end_at,
                    'start_at' => $activity->start_at,
                    'qrcode' => $this->getQrcode($activity),
                ],
                'middleman' => [
                    'name' => $middleman->name,
                    'mobile' => $middleman->mobile,
                    'avatar' => $middleman->userInfo->avatar,
                    'province' => $middleman->address->province,
                    'city' => $middleman->address->city,
                    'district' => $middleman->address->district,
                    'detail' => $middleman->address->detail,
                    'location' => $middleman->address->location,
                ]
            ]);
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }

    /**
     * @param User $user
     * @return \app\plugins\community\models\CommunityMiddleman|array|\yii\db\ActiveRecord|null
     * @throws \Exception
     */
    protected function getMiddleman($user)
    {
        $commonMiddleman = CommonMiddleman::getCommon();
        if ($this->middleman_id == 0) {
            // 团长端请求
            $middleman = $commonMiddleman->getConfig($user->id);
        } else {
            // 用户端请求
            $parent = $commonMiddleman->getParent($user->id);
            if ($parent && $parent->middleman_id > 0) {
                $middleman = $commonMiddleman->getConfig($parent->middleman_id);
                if ($middleman && $middleman->status == 1) {
                    return $middleman;
                }
            }
            $middlemanId = $this->middleman_id;
            $middleman = $commonMiddleman->getConfig($middlemanId);
        }
        if (!$middleman || $middleman->status != 1) {
            throw new \Exception('用户选择的团长不存在');
        }
        return $middleman;
    }

    protected function getQrcode($activity)
    {
        $common = CommonMiddleman::getCommon();
        $middlemanId = $common->getQrcodeMiddlemanId(\Yii::$app->user->id);
        $common = new CommonQrCode();
        $common->appPlatform = \Yii::$app->appPlatform;
        $scene = ['user_id' => \Yii::$app->user->id, 'id' => $activity->id, 'middleman_id' => $middlemanId];
        $res = $common->getQrCode($scene, 430, 'plugins/community/activity/activity');
        return $res['file_path'];
    }
}
