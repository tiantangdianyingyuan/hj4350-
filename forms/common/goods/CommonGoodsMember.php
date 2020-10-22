<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/10/12
 * Time: 9:32
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\goods;


use app\forms\common\CommonMallMember;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsMemberPrice;
use app\models\MallMembers;
use app\models\Model;
use app\models\User;

class CommonGoodsMember extends Model
{
    private static $instance;

    public static function getCommon()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public $priceList;
    public $is_level;
    public $is_level_alone;
    public $level;

    /**
     * @param Goods $goods
     * @return string
     * 获取商城商品会员价
     */
    public function getGoodsMemberPrice($goods)
    {
        $this->setLevel();
        $this->priceList = $this->getPriceList($goods);
        $this->is_level = $goods->is_level;
        $this->is_level_alone = $goods->is_level_alone;
        return $this->getGoodsMember();
    }

    public function setLevel()
    {
        if (!\Yii::$app->user->isGuest) {
            /* @var User $user */
            $user = \Yii::$app->user->identity;
            $level = $user->identity->member_level;
            if ($level <= 0) {
                $level = $this->getMinMember();
            } else {
                $level = \Yii::$app->mall->getMallSettingOne('is_member_user_member_price') == 1 ? $level : 0;
            }
        } else {
            $level = $this->getMinMember();
        }
        $this->level = $level;
    }

    public static $memberLevel = null;
    private function getMinMember()
    {
        if (\Yii::$app->mall->getMallSettingOne('is_common_user_member_price') == 0) {
            return 0;
        }
        if (self::$memberLevel !== null) {
            return self::$memberLevel;
        }
        $member = MallMembers::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'status' => 1,
        ])->orderBy(['level' => SORT_ASC])->one();
        if (!$member) {
            self::$memberLevel = 0;
            return 0;
        }
        self::$memberLevel = $member->level;
        return $member->level;
    }

    private $list;

    /**
     * @param Goods $goods
     * @return array
     */
    public function getPriceList($goods)
    {
        if (isset($this->list[$goods->id])) {
            return $this->list[$goods->id];
        }
        $list = [];
        if ($this->level > 0) {
            if ($goods->is_level_alone == 0) {
                $list = array_column($goods->attr, 'price');
            } else {
                $list = GoodsMemberPrice::find()
                    ->where(['is_delete' => 0, 'goods_id' => $goods->id, 'level' => $this->level])
                    ->select('price')->column();
            }
        }
        $this->list[$goods->id] = $list;
        return $list;
    }

    /**
     * @return string
     * 从价格列表中选取最小会员价
     */
    public function getGoodsMember()
    {
        $levelPrice = null;

        if ($this->level > 0 && $this->is_level == 1) {
            foreach ($this->priceList as $item) {
                if (!$levelPrice) {
                    $levelPrice = $item;
                } else {
                    $levelPrice = min($levelPrice, $item);
                }
            }
            if ($this->is_level_alone == 0) {
                $member = CommonMallMember::getMemberOne($this->level);
                $levelPrice *= $member->discount / 10;
            }
            $levelPrice = round($levelPrice, 2);
        } else {
            $levelPrice = -1;
        }

        return $levelPrice;
    }
}
