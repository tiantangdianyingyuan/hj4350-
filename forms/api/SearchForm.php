<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/6/15
 * Time: 21:12
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api;


use app\core\response\ApiCode;
use app\models\AdminInfo;
use app\models\Model;

class SearchForm extends Model
{
    public function getSearch()
    {
        // 查找商城所属的用户
        $adminInfo = AdminInfo::findOne([
            'user_id' => \Yii::$app->mall->user_id,
            'is_delete' => 0
        ]);
        $list = [
            [
                'key' => '',
                'name' => '商城'
            ],
        ];
        if ($adminInfo->permissions) {
            $permission = \Yii::$app->serializer->decode($adminInfo->permissions);
            $allow = ['miaosha', 'pintuan', 'booking'];
            if ($adminInfo->user_id == 1) {
                $permission = ['miaosha', 'pintuan', 'booking'];
            }
            foreach ($permission as $item) {
                try {
                    $plugin = \Yii::$app->plugin->getPlugin($item);
                    if (in_array($item, $allow)) {
                        $list[] = [
                            'key' => $plugin->getName(),
                            'name' => $plugin->getDisplayName()
                        ];
                    }
                } catch (\Exception $exception) {
                }
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $list
        ];
    }
}
