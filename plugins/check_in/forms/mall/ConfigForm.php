<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/26
 * Time: 18:43
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\forms\mall;


use app\core\response\ApiCode;
use app\plugins\check_in\forms\common\Common;
use app\plugins\check_in\forms\Model;
use function PHPSTORM_META\type;
use yii\helpers\ArrayHelper;

class ConfigForm extends Model
{
    public function search()
    {
        $common = Common::getCommon($this->mall);
        $config = $common->getConfig();
        if (!$config->isNewRecord) {
            $awardConfig = $common->getAwardConfigAll();
            $config = ArrayHelper::toArray($config);
            $config['continue'] = [];
            $config['total'] = [];
            foreach ($awardConfig as $award) {
                $number = price_format($award->number, 'float', 2);
                $item = [
                    'number' => $number,
                    'day' => $award->day,
                    'type' => $award->type
                ];
                if ($award->status == 1) {
                    $config['normal'] = $number;
                    $config['normal_type'] = $award->type;
                }
                if ($award->status == 2) {
                    $config['continue'][] = $item;
                }
                if ($award->status == 3) {
                    $config['total'][] = $item;
                }
            }
        } else {
            $config = null;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'config' => $config
            ]
        ];
    }
}
