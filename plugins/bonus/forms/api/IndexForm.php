<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/9
 * Time: 10:47
 */

namespace app\plugins\bonus\forms\api;

use app\core\response\ApiCode;
use app\forms\api\share\ShareForm;
use app\models\Model;
use app\models\UserIdentity;
use app\plugins\bonus\forms\common\CommonForm;
use app\plugins\bonus\models\BonusSetting;

class IndexForm extends Model
{
    const APPLY = 0;
    const TOTAL_BONUS = 1;
    const CASHED_BONUS = 2;
    const ALL_MEMBERS = 3;
    const ALL_SHARES = 4;

    protected $becomeType;
    protected $condition;

    public function search()
    {
        try {
            $identity = UserIdentity::findOne([
                'is_delete' => 0,
                'is_distributor' => 1,
                'user_id' => \Yii::$app->user->id,
            ]);
            if (!$identity) {
                throw new \Exception('你不是分销商');
            }

            $this->becomeType = BonusSetting::get(\Yii::$app->mall->id, 'become_type', 0);
            $this->condition = BonusSetting::get(\Yii::$app->mall->id, 'condition', 0);
            if (!isset($this->becomeType) || !isset($this->condition)) {
                throw new \Exception('团队分红未配置');
            }

            $info = $this->becomeType();
            $info['condition'] = $this->condition;
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $info
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    private function becomeType()
    {
        $form = new ShareForm();
        $price = $form->getPrice();

        switch ($this->becomeType) {
            case self::APPLY:
                $info['to_apply'] = true;
                $info['pass'] = true;
                return $info;

            case self::TOTAL_BONUS:
                $info['total_money'] = $price['total_money'];
                $info['pass'] = $info['total_money'] >= price_format($this->condition) ? true : false;
                return $info;

            case self::CASHED_BONUS:
                $info['cash_money'] = $price['cash_money'];
                $info['pass'] = $info['cash_money'] >= price_format($this->condition) ? true : false;
                return $info;

            case self::ALL_MEMBERS:
                $count = CommonForm::allMembers(\Yii::$app->user->id);
                $info['all_members'] = $count ? $count : 0;
                $info['pass'] = $info['all_members'] >= $this->condition ? true : false;
                return $info;

            case self::ALL_SHARES:
                $count = CommonForm::allShares(\Yii::$app->user->id);
                $info['all_shares'] = $count ? $count: 0;
                $info['pass'] = $info['all_shares'] >= $this->condition ? true : false;
                return $info;

            default:
                throw new \Exception('未知的条件');
        }

    }
}