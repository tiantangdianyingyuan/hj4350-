<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/15
 * Time: 16:33
 */

namespace app\forms\common\share;


use app\events\ShareEvent;
use app\events\ShareMemberEvent;
use app\handlers\HandlerRegister;
use app\models\GoodsCatRelation;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\models\Share;
use app\models\ShareSetting;
use app\models\User;
use app\models\UserIdentity;
use app\models\UserInfo;
use yii\db\Exception;

/**
 * @property Mall $mall
 * @property User $user
 */
class CommonShare extends Model
{
    public $mall;
    public $user;
    public $user_id;

    public static function getCommon()
    {
        $common = new self();
        return $common;
    }

    /**
     * @param $parentId integer
     * @param $condition integer
     * @param $isManual boolean 后台手动设置
     * @return boolean
     * @throws Exception
     */
    public function bindParent($parentId, $condition, $isManual = false)
    {
        if ($parentId == $this->user->id) {
            throw new Exception('自身分享的页面');
        }

        if ($this->user->identity->is_distributor == 1 && !$isManual) {
            throw new Exception('用户自身是分销商');
        }

        if ($this->user->userInfo->parent_id != 0 && !$isManual) {
            throw new Exception('用户存在上级');
        }

        $setting = ShareSetting::getList($this->mall->id);
        if (!$setting || $setting['level'] == 0) {
            throw new Exception('未开启分销');
        }

        if ($setting['condition'] != $condition && !$isManual) {
            throw new Exception('未满足成为下线条件');
        }

        $share = Share::findOne([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'status' => 1, 'user_id' => $parentId
        ]);
        if (!$share) {
            throw new Exception('绑定的上级用户不是分销商');
        }

        if (!$this->checkBind($this->user->id, $parentId, 1)) {
            throw new Exception('用户处于三层分销体系之内');
        }

        $beforeParentId = $this->user->userInfo->parent_id;
        $this->user->userInfo->parent_id = $parentId;
        $this->user->userInfo->junior_at = date('Y-m-d H:i:s', time());

        if ($this->user->userInfo->save()) {
            \Yii::$app->trigger(HandlerRegister::CHANGE_SHARE_MEMBER, new ShareMemberEvent([
                'mall' => \Yii::$app->mall,
                'beforeParentId' => $beforeParentId,
                'parentId' => $parentId,
                'userId' => $this->user->id
            ]));
            return true;
        } else {
            throw new Exception($this->getErrorMsg($this->user->userInfo));
        }
    }

    /**
     * @param $userId
     * @param $parentId
     * @param int $root
     * @return bool
     * @@throws \Exception
     * 判断user_id 是否在parent_id所在的三层分销体系中
     */
    public function checkBind($userId, $parentId, $root = 3)
    {
        if ($root > 3) {
            return true;
        }

        if ($parentId == 0) {
            return true;
        }

        /* @var Share $parent */
        $parent = Share::find()->with('userInfo')
            ->where(['user_id' => $parentId, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
            ->one();
        if (!$parent) {
            throw new \Exception('错误的上级id');
        }

        if ($parent->userInfo->parent_id == $userId) {
            return false;
        }

        if ($parent->userInfo->parent_id == 0) {
            return true;
        }

        $root++;
        return $this->checkBind($userId, $parent->userInfo->parent_id, $root);
    }

    /**
     * @param Order $order
     * @return bool
     * @throws \Exception
     */
    public function becomeShareByPayed($order)
    {
        $shareCondition = ShareSetting::get($this->mall->id, ShareSetting::SHARE_CONDITION, 1);
        $becomeCondition = ShareSetting::get($this->mall->id, ShareSetting::BECOME_CONDITION, 3);
        if ($shareCondition != 3) {
            return true;
        } else {
            if ($becomeCondition == 1) {
                return $this->becomeShareByConsume($order);
            } elseif ($becomeCondition == 2) {
                return $this->becomeShareByBuyGoods($order);
            } else {
                return true;
            }
        }
    }

    /**
     * @param Order $order
     * @return bool
     * @throws \Exception
     * 购买商品成为分销商
     */
    public function becomeShareByBuyGoods($order)
    {
        $shareGoodsStatus = ShareSetting::get($this->mall->id, ShareSetting::SHARE_GOODS_STATUS, 0);
        if (!$shareGoodsStatus) {
            return false;
        }
        $ok = false;
        if ($shareGoodsStatus == 1) {
            $ok = true;
        } elseif ($shareGoodsStatus == 2) {
            $shareGoodsId = ShareSetting::get($this->mall->id, ShareSetting::SHARE_GOODS_WAREHOUSE_ID);
            if (!$shareGoodsId) {
                return false;
            }
            foreach ($order->detail as $detail) {
                $goodsInfo = $detail->decodeGoodsInfo();
                if (isset($goodsInfo['goods_attr'])
                    && $goodsInfo['goods_attr']['goods_warehouse_id']
                    && in_array($goodsInfo['goods_attr']['goods_warehouse_id'], (array)$shareGoodsId)) {
                    $ok = true;
                    break;
                }
            }
        } elseif ($shareGoodsStatus == 3) {
            $catIdList = ShareSetting::get($this->mall->id, ShareSetting::CAT_LIST);
            if (!$catIdList) {
                return false;
            }
            $shareGoodsId = GoodsCatRelation::find()->where(['cat_id' => $catIdList, 'is_delete' => 0])
                ->select('goods_warehouse_id')->column();
            foreach ($order->detail as $detail) {
                $goodsInfo = $detail->decodeGoodsInfo();
                if (isset($goodsInfo['goods_attr'])
                    && $goodsInfo['goods_attr']['goods_warehouse_id']
                    && in_array($goodsInfo['goods_attr']['goods_warehouse_id'], $shareGoodsId)) {
                    $ok = true;
                    break;
                }
            }
        } else {
            return false;
        }

        if (!$ok) {
            return false;
        }
        $attributes = [
            'status' => 1,
            'reason' => "购买商品自动成为分销商",
            'apply_at' => mysql_timestamp(),
        ];
        \Yii::error('购买商品成功为分销商');
        return $this->becomeShare($order->user, $attributes);
    }

    /**
     * @param Order $order
     * @return bool
     * @throws \Exception
     * 消费满额自动成为分销商
     */
    public function becomeShareByConsume($order)
    {
        $shareAutoVal = ShareSetting::get($this->mall->id, ShareSetting::AUTO_SHARE_VAL, 0);
        if (!$shareAutoVal) {
            return false;
        }

        $orderCount = $order->total_pay_price;
        $orderCount = floatval($orderCount);
        if ($orderCount < $shareAutoVal) {
            return false;
        }
        $attributes = [
            'status' => 1,
            'reason' => "消费{$shareAutoVal}元自动成为分销商",
            'apply_at' => mysql_timestamp(),
        ];
        \Yii::error('消费' . $shareAutoVal . '元自动成为分销商');
        return $this->becomeShare($order->user, $attributes);
    }

    /**
     * @param User $user
     * @param $attributes
     * @return bool
     * @throws \Exception
     * 分销商关于申请处理
     */
    public function becomeShare($user, $attributes)
    {
        if (!$user instanceof User) {
            throw new Exception('$user参数必须是\app\models\User的是实例');
        }

        $share = $user->share;
        if ($share) {
            if ($share->is_delete == 0 && $share->status == 1) {
                throw new Exception('用户已经是分销商，请勿重复提交');
            }
        } else {
            $share = new Share();
            $share->mall_id = $user->mall_id;
            $share->user_id = $user->id;
            $share->created_at = mysql_timestamp();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            $share->attributes = $attributes;
            $share->level = 0;
            $share->is_delete = 0;
            $share->become_at = $share->status == 1 ? mysql_timestamp() : '';
            if ($share->save()) {
                if ($share->status == 1) {
                    $userIdentity = UserIdentity::findOne(['user_id' => $share->user_id]);
                    $userIdentity->is_distributor = 1;
                    if (!$userIdentity->save()) {
                        throw new \Exception((new Model())->getErrorMsg($userIdentity));
                    }
                }
                $user->share = $share;
                $t->commit();
                \Yii::$app->trigger(HandlerRegister::BECOME_SHARE, new ShareEvent([
                    'share' => $share
                ]));
                return true;
            } else {
                throw new \Exception($this->getErrorMsg($share));
            }
        } catch (\Exception $exception) {
            $t->rollBack();
            throw $exception;
        }
    }

    /**
     * @param Share $share
     * @throws \Exception
     * 删除分销商（包括删除分销商的直属下级和上级）
     */
    public function deleteShare($share)
    {
        $t = \Yii::$app->db->beginTransaction();
        $share->is_delete = 1;
        $share->delete_first_show = 0;
        $share->deleted_at = mysql_timestamp();
        $share->first_children = 0;
        $share->all_children = 0;
        if ($share->save()) {
            $userIdentity = UserIdentity::findOne(['user_id' => $share->user_id]);
            $userIdentity->is_distributor = 0;
            if (!$userIdentity->save()) {
                $t->rollBack();
                throw new \Exception($this->getErrorMsg($userIdentity));
            }
            UserInfo::updateAll(
                ['parent_id' => 0],
                [
                    'or',
                    ['parent_id' => $share->user_id],
                    ['user_id' => $share->user_id]
                ]
            );
            $this->changeShare($share->userInfo->parent_id, 1);
            $t->commit();
        } else {
            $t->rollBack();
            throw new \Exception($this->getErrorMsg($share));
        }
    }

    /**
     * @param $parentId integer 上级用户id
     * @param int $count 计数
     * @return bool
     * @throws \Exception
     */
    public function changeShare($parentId, $count = 4)
    {
        if ($count >= 3) {
            return true;
        }

        if (!$parentId) {
            return true;
        }

        /* @var Share $share */
        $share = Share::find()->with('userInfo')
            ->where(['user_id' => $parentId, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
            ->one();
        if (!$share) {
            return true;
        }
        if ($count == 1) {
            $share->first_children -= min($share->first_children, 1);
        }
        $share->all_children -= min($share->all_children, 1);
        if (!$share->save()) {
            throw new \Exception($this->getErrorMsg($share));
        }
        $count++;
        return $this->changeShare($share->userInfo->parent_id, $count);
    }
}
