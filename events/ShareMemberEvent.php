<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/10
 * Time: 16:44
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\events;


use app\models\Mall;
use app\models\Share;
use app\models\User;
use yii\base\Event;

/**
 * Class ShareMemberEvent
 * @package app\events
 * @property Share $parent
 * @property Share $beforeParent
 * @property User $user
 * @property Mall $mall
 * @property integer $beforeParentId
 * @property integer $parentId
 * @property integer $userId
 * @property string $remark
 */
class ShareMemberEvent extends Event
{
    protected $user; // 触发事件的用户
    protected $userId; // 触发事件的用户ID
    protected $beforeParentId; // 变更前的上级ID（此为user表的id）
    protected $beforeParent; // 变更前的上级分销商（此为share表）
    protected $parent; // 变更后的上级分销商
    protected $parentId; // 变更后的上级Id
    protected $remark; //备注
    public $mall; // 商城

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        if ($this->user) {
            return $this->user;
        } elseif ($this->userId) {
            $this->user = User::findOne(['id' => $this->userId]);
            return $this->user;
        } else {
            return null;
        }
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getUserId()
    {
        if ($this->userId) {
            return $this->userId;
        } elseif ($this->user) {
            return $this->user->id;
        } else {
            return 0;
        }
    }

    public function setBeforeParentId($beforeParentId)
    {
        $this->beforeParentId = $beforeParentId;
    }

    public function getBeforeParentId()
    {
        if ($this->beforeParentId) {
            return $this->beforeParentId;
        } elseif ($this->beforeParent) {
            return $this->beforeParent->user_id;
        } else {
            return 0;
        }
    }

    public function setBeforeParent($beforeParent)
    {
        $this->beforeParent = $beforeParent;
    }

    public function getBeforeParent()
    {
        if ($this->beforeParent) {
            return $this->beforeParent;
        } elseif ($this->beforeParentId) {
            $this->beforeParent = Share::findOne(['user_id' => $this->beforeParentId]);
            return $this->beforeParent;
        } else {
            return null;
        }
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        if ($this->parent) {
            return $this->parent;
        } elseif ($this->parentId) {
            $this->parent = Share::findOne(['user_id' => $this->parentId]);
            return $this->parent;
        } else {
            return null;
        }
    }

    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    public function getParentId()
    {
        if ($this->parentId) {
            return $this->parentId;
        } elseif ($this->parent) {
            return $this->parent->user_id;
        } else {
            return 0;
        }
    }

    public function setRemark($remark)
    {
        $this->remark = $remark;
    }

    public function getRemark()
    {
        if ($this->remark) {
            return $this->remark;
        } else {
            return null;
        }
    }
}
