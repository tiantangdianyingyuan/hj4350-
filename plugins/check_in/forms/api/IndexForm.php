<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/27
 * Time: 9:57
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\forms\api;


use app\core\response\ApiCode;
use app\forms\common\template\TemplateList;
use app\plugins\check_in\forms\common\Common;
use app\plugins\check_in\models\CheckInSign;

class IndexForm extends ApiModel
{
    public $month;
    public $year;

    public function rules()
    {
        return [
            [['month', 'year'], 'integer']
        ];
    }

    public function search()
    {
        try {
            $common = Common::getCommon($this->mall);
            $config = $common->getConfig();
            if (!$config) {
                throw new \Exception('未开启签到');
            }

            $checkInDay = $common->getDay(date('m', time()), $this->user);
            $checkInUser = $common->getCheckInUser($this->user);
            $newList = [];
            foreach ($config as $key => $item) {
                $ignore = ['id', 'mall_id', 'is_delete', 'created_at', 'deleted_at', 'updated_at', 'continue_type'];
                if (in_array($key, $ignore)) {
                    continue;
                }
                $newList[$key] = $item;
            }

            // 获取普通签到奖励信息
            $awardConfigNormal = $common->getAwardConfigNormal();
            $awardConfig[] = $awardConfigNormal;

            $awardConfig = [];
            // 获取连续签到奖励信息（用户未领取）
            $awardConfigContinue = $common->getAwardConfigContinue($checkInUser);
            $awardConfig = array_merge($awardConfig, $awardConfigContinue);

            // 获取累计签到奖励信息（用户未领取）
            $awardConfigTotal = $common->getAwardConfigTotal($this->user);
            $awardConfig = array_merge($awardConfig, $awardConfigTotal);

            $newList['continue'] = [];
            $newList['total'] = [];
            $newList['normal'] = 0;
            $newList['normal_type'] = 'integral';
            foreach ($awardConfig as $award) {
                $number = price_format($award['number'], 'float', 2);
                $item = [
                    'number' => $number,
                    'day' => $award['day'],
                    'type' => $award['type'],
                    'check' => isset($award['check']) ? true : false
                ];
                if ($award['status'] == 1) {
                    $newList['normal'] = $number;
                    $newList['normal_type'] = $award['type'];
                }
                if ($award['status'] == 2) {
                    $newList['continue'][] = $item;
                }
                if ($award['status'] == 3) {
                    $newList['total'][] = $item;
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'config' => $newList,
                    'check_in_user' => [
                        'continue_day' => $checkInUser ? $checkInUser->continue : 0,
                        'total_day' => $checkInUser ? $checkInUser->total : 0,
                        'is_remind' => $checkInUser ? $checkInUser->is_remind : 0,
                        'today_check_in' => $common->getSignInByToday($this->user)
                    ],
                    'check_in_day' => $checkInDay,
                    'template_message' => TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, ['check_in_tpl'])
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function getDay()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if ($this->user) {
                $common = Common::getCommon($this->mall);
                $checkInDay = $common->getDay($this->month, $this->user, $this->year);
            } else {
                $checkInDay = [];
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'check_in_day' => $checkInDay
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function getCustomize()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $config = Common::getCommon(\Yii::$app->mall)->getCustomize();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $config
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
