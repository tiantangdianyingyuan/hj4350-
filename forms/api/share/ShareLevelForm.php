<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/1
 * Time: 15:58
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\share;


use app\core\response\ApiCode;
use app\forms\common\share\CommonShareLevel;
use app\models\Model;
use app\models\ShareLevel;
use app\models\User;
use yii\db\Query;

class ShareLevelForm extends Model
{
    /**
     * 获取升级条件
     */
    public function getLevelCondition()
    {
        try {
            /* @var User $user */
            $user = \Yii::$app->user->identity;
            if ($user->identity->is_distributor != 1) {
                throw new \Exception('用户不是分销商');
            }
            $list = [];
            $condition = ['is_delete' => 0, 'status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_auto_level' => 1];
            $query1 = ShareLevel::find()->where([
                'condition_type' => 1
            ])->andWhere(['>', 'level', $user->share->level])->andWhere($condition)
                ->orderBy(['condition_type' => SORT_ASC, 'condition' => SORT_ASC, 'level' => SORT_DESC])
                ->select('rule,condition_type,condition')->one();
            $query2 = ShareLevel::find()->where([
                'condition_type' => 2
            ])->andWhere(['>', 'level', $user->share->level])->andWhere($condition)
                ->orderBy(['condition_type' => SORT_ASC, 'condition' => SORT_ASC, 'level' => SORT_DESC])
                ->select('rule,condition_type,condition')->one();
            $query3 = ShareLevel::find()->where([
                'condition_type' => 3
            ])->andWhere(['>', 'level', $user->share->level])->andWhere($condition)
                ->orderBy(['condition_type' => SORT_ASC, 'condition' => SORT_ASC, 'level' => SORT_DESC])
                ->select('rule,condition_type,condition')->one();
            if ($query1) {
                array_push($list, $query1);
            }
            if ($query2) {
                array_push($list, $query2);
            }
            if ($query3) {
                array_push($list, $query3);
            }
            array_walk($list, function (&$item) {
                $item['condition'] = round($item['condition'], 2);
            });
            unset($item);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $list
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function levelUp()
    {
        try {
            $commonShareLevel = CommonShareLevel::getInstance();
            $commonShareLevel->user = \Yii::$app->user->identity;
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $commonShareLevel->levelUp()
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
