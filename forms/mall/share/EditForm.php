<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/3
 * Time: 14:33
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\share;


use app\core\response\ApiCode;
use app\forms\common\share\CommonShare;
use app\forms\common\share\CommonShareLevel;
use app\models\Model;
use app\models\Share;
use app\models\ShareLevel;
use app\models\User;
use app\models\UserIdentity;
use app\models\UserInfo;

class EditForm extends Model
{
    public $keyword;
    public $id;
    public $level;
    public $batch_ids;

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['keyword'], 'string'],
            [['id', 'level'], 'integer'],
            [['batch_ids'], 'safe']
        ];
    }

    public function getUser()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $list = User::find()->alias('u')->with('userInfo')->where([
            'u.is_delete' => 0, 'u.mall_id' => \Yii::$app->mall->id,
        ])->InnerJoin([
            'i' => UserInfo::tableName()
        ], 'u.id = i.user_id')->leftJoin(['ud' => UserIdentity::tableName()], 'ud.user_id=u.id')
            ->andWhere(['ud.is_distributor' => 0])
            ->keyword($this->keyword !== '', ['like', 'u.nickname', $this->keyword])
            ->apiPage(20)->select('u.id,u.nickname')->all();
        array_walk($list, function (&$item) {
            $platform = $item->userInfo ? $item->userInfo->platform : '';
            $item->nickname = UserInfo::getPlatformText($platform) . '（' . $item->nickname . '）';
        });
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list
            ]
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if (!$this->id || $this->id < 0) {
                throw new \Exception('错误的用户');
            }
            /* @var User $user */
            $user = User::find()->where(['id' => $this->id])->with('share')->one();
            if (!$user) {
                throw new \Exception('用户不存在');
            }
            $userIdentity = UserIdentity::findOne(['user_id' => $this->id]);
            if (!$userIdentity) {
                throw new \Exception('用户不存在');
            }
            if ($userIdentity->is_distributor == 1) {
                throw new \Exception('所选用户已经是分销商，无需重复添加');
            }
            if ($user->share && $user->share->status == 0) {
                throw new \Exception('用户已经提交分销商申请，请前往审核');
            }
            $commonShare = CommonShare::getCommon();
            $commonShare->becomeShare($user, ['status' => 1, 'reason' => '后台添加成为分销商',
                'apply_at' => mysql_timestamp(),]);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '添加成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function changeLevel()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $common = CommonShareLevel::getInstance();
            $common->userId = $this->id;
            if ($this->level) {
                $shareLevel = $common->getShareLevelByLevel($this->level);
                if (!$shareLevel) {
                    throw new \Exception('无效的分销商等级');
                }
            } else {
                $this->level = 0;
            }
            $common->changeLevel($this->level);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '修改成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function getLevel()
    {
        $level = ShareLevel::find()
            ->where(['is_delete' => 0, 'status' => 1, 'mall_id' => \Yii::$app->mall->id])
            ->orderBy(['level' => SORT_ASC])
            ->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $level
            ]
        ];
    }

    public function batchLevel()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if ($this->level) {
                $common = CommonShareLevel::getInstance();
                $shareLevel = $common->getShareLevelByLevel($this->level);
                if (!$shareLevel) {
                    throw new \Exception('无效的分销商等级');
                }
            } else {
                $this->level = 0;
            }
            Share::updateAll(
                ['level' => $this->level, 'level_at' => mysql_timestamp()],
                ['id' => $this->batch_ids]
            );
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '修改成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
