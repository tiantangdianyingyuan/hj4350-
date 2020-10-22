<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/30
 * Time: 17:11
 */

namespace app\plugins\bonus\handlers;

use app\forms\api\share\ShareForm;
use app\handlers\HandlerBase;
use app\plugins\bonus\events\MemberEvent;
use app\plugins\bonus\forms\common\CommonForm;
use app\plugins\bonus\models\BonusCaptain;
use app\plugins\bonus\models\BonusMembers;

class MemberLevelHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(BonusMembers::UPDATE_LEVEL, function ($event) {
            /**
             * @var MemberEvent $event
             */
            if (!$event->captain instanceof BonusCaptain) {
                throw new \Exception('错误的参数,$event->captain必须是\app\plugins\bonus\models\BonusCaptain的对象或对象ID');
            }

            $nextLevel = $this->nextLevel($event);
            if (empty($nextLevel)) {
                return false;
            }

            $event->captain->level = $nextLevel->id;
            try {
                $event->captain->save();
            } catch (\Exception $e) {
                \Yii::error('队长升级失败');
                \Yii::error($e);
                return [
                    'code' => 1,
                    'msg' => $e->getMessage()
                ];
            }
        });
    }

    private function nextLevel($event)
    {
        $level = $event->captain->level;
        $nowLevelModel = BonusMembers::findOne($level);
        if (empty($nowLevelModel)) {
            $nowLevel = 0;
        } else {
            $nowLevel = $nowLevelModel['level'];
        }
        $nextLevel = BonusMembers::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'auto_update' => 1, 'status' => 1])
            ->andWhere(['>', 'level', $nowLevel])
            ->orderBy(['level' => SORT_DESC])
            ->all();

        $allMembers = CommonForm::allMembers($event->captain->user_id);
        $allShares = CommonForm::allShares($event->captain->user_id);
        $allCaptain = CommonForm::allCaptains($event->captain->user_id);
        $form = new ShareForm();
        $price = $form->getPrice($event->captain->user_id);

        foreach ($nextLevel as $item) {
            switch ($item->update_type) {
                case BonusMembers::TOTAL_MONEY:
                    if ($price['total_money'] >= $item->update_condition) {
                        return $item;
                    }
                    break;

                case BonusMembers::CASHED_MONEY:
                    if ($price['cash_money'] >= $item->update_condition) {
                        return $item;
                    }
                    break;

                case BonusMembers::ALL_MEMBERS:
                    if ($allMembers >= $item->update_condition) {
                        return $item;
                    }
                    break;

                case BonusMembers::ALL_SHARES:
                    if ($allShares >= $item->update_condition) {
                        return $item;
                    }
                    break;

                case BonusMembers::ALL_CAPTAIN:
                    if ($allCaptain >= $item->update_condition) {
                        return $item;
                    }
                    break;

                default:

                    break;
            }
        }
    }
}