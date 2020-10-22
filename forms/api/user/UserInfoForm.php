<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/29
 * Time: 11:14
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\user;


use app\core\response\ApiCode;
use app\forms\api\app_platform\Transform;
use app\forms\common\CommonAppConfig;
use app\forms\common\CommonUser;
use app\forms\common\config\UserCenterConfig;
use app\forms\common\order\CommonOrder;
use app\forms\common\template\TemplateList;
use app\models\ClerkUser;
use app\models\Favorite;
use app\models\FootprintGoodsLog;
use app\models\Goods;
use app\models\MallMembers;
use app\models\Model;
use app\models\User;
use app\models\UserCard;
use app\models\UserCoupon;
use app\models\UserInfo;

class UserInfoForm extends Model
{
    public $clerk_id;

    public function rules()
    {
        return [
            [['clerk_id'], 'integer']
        ];
    }

    private function userInfo()
    {
        if (\Yii::$app->user->isGuest) {
            return null;
        }
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        /* @var UserInfo $userInfo */
        $userInfo = CommonUser::getUserInfo();
        unset($userInfo->platform_user_id);

        $parentName = '总店';
        if ($userInfo->parent_id != 0) {
            $parent = User::findOne([
                'id' => $userInfo->parent_id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ]);
            if ($parent) {
                $parentName = $parent->nickname;
            }
        }

        $userCenterConfig = UserCenterConfig::getInstance()->getApiUserCenter();

        $levelName = $userCenterConfig['general_user_text'];
        $memberPicUrl = '';
        if ($user->identity->member_level != 0) {
            $level = MallMembers::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'level' => $user->identity->member_level,
                'status' => 1, 'is_delete' => 0
            ]);
            if ($level) {
                $levelName = $level->name;
                $memberPicUrl = $level->pic_url;
            }
        }

        $couponCount = UserCoupon::find()->andWhere(['user_id' => $user->id, 'is_delete' => 0, 'is_use' => 0])
            ->andWhere(['>', 'end_time', mysql_timestamp()])->count();
        $cardCount = UserCard::find()->andWhere(['user_id' => $user->id, 'is_delete' => 0, 'is_use' => 0])
            ->andWhere(['>', 'end_time', mysql_timestamp()])->count();

        $favoriteCount = Favorite::find()->alias('f')->where(['f.user_id' => $user->id, 'f.is_delete' => 0])
            ->leftJoin(['g' => Goods::tableName()], 'g.id = f.goods_id')
            ->count();

        $result = [
            'nickname' => $user->nickname,
            'mobile' => $user->mobile,
            'avatar' => $userInfo->avatar,
            'integral' => $userInfo->integral,
            'balance' => $userInfo->balance,
            'options' => $userInfo,
            'favorite' => $favoriteCount ?? '0',
            'footprint' => FootprintGoodsLog::find()->where(['user_id' => $user->id, 'is_delete' => 0])->count() ?? '0',
            'identity' => [
                'parent_name' => $parentName,
                'level_name' => $levelName,
                'member_level' => $user->identity->member_level,
                'member_pic_url' => $memberPicUrl,
                'is_admin' => $user->identity->is_admin,
            ],
            'coupon' => $couponCount,
            'card' => $cardCount,
            'is_vip_card_user' => 0,
        ];
        $result = array_merge($result, \Yii::$app->plugin->getUserInfo($user));
        return $result;
    }

    public function getInfo()
    {
        $result = $this->userInfo();
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        $cacheKey = 'user_register_' . $user->id . '_' . $user->mall_id;
        $couponList = \Yii::$app->cache->get($cacheKey);
        if ($couponList && count($couponList) > 0) {
            $result['register'] = ['coupon_list' => $couponList];
            \Yii::$app->cache->delete($cacheKey);
        }

        return [
            'code' => 0,
            'data' => $result,
        ];
    }

    public function config()
    {
        $mall = \Yii::$app->mall->getMallSetting();
        $userCenter = \app\forms\common\config\UserCenterConfig::getInstance()->getApiUserCenter();

        $res = [
            'code' => 0,
            'data' => [
                'mall' => $mall,
                'config' => [
                    'title_bar' => [
                        'background' => '#ff4544',
                        'color' => '#ffffff',
                    ],
                    'user_center' => $userCenter,
                    'copyright' => CommonAppConfig::getCoryRight(),
                ],
                'user_info' => $this->userInfo(),
            ],
        ];

        return $res;
    }

    public function isClerkUser()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if (!$this->clerk_id) {
                throw new \Exception('请传入核销员ID');
            }

            $clerkInfo = ClerkUser::find()->where(['user_id' => $this->clerk_id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->with('store')->one();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'is_clerk_user' => $clerkInfo ? 1 : 0
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }
}
