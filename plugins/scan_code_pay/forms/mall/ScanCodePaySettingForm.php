<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\helpers\PluginHelper;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\scan_code_pay\forms\common\CommonScanCodePaySetting;
use app\plugins\scan_code_pay\forms\common\GoodsEditForm;
use app\plugins\scan_code_pay\Plugin;
use yii\helpers\ArrayHelper;

class ScanCodePaySettingForm extends Model
{
    public function search()
    {
        $common = new CommonScanCodePaySetting();
        $setting = $common->getSetting();

        $form = new GoodsEditForm();
        /** @var Goods $goods */
        $goods = $form->save();
        $newGoods['attr_setting_type'] = $goods->attr_setting_type;
        $newGoods['share_type'] = $goods->share_type;
        $newGoods['use_attr'] = 0;
        $newGoods['shareLevelList'] = $goods->shareLevel ? ArrayHelper::toArray($goods->shareLevel) : [];

        $common = CommonGoods::getCommon();
        $goods = $common->transformAttr($goods);
        $newGoods['attr'] = $goods['attr'];
        try {
            $newGoods['attr_groups'] = \Yii::$app->serializer->decode($goods->attr_groups);
        } catch (\Exception $exception) {
            $newGoods['attr_groups'] = [];
        }

        $permissions = \Yii::$app->role->getPermission();
        if (\Yii::$app->getRole()->getName() == 'operator') {
            foreach ($permissions as $permission) {
                if (strstr($permission, 'mall/share')) {
                    $permissions = [];
                    $permissions[] = 'share';
                    break;
                }
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $setting,
                'permissions' => $permissions,
                'goods' => $newGoods,
                'platform_list' => $this->getPlatformList(),
            ]
        ];
    }

    private function getPlatformList()
    {
        $list = \Yii::$app->plugin->getAllPlatformPlugins();
        $newList = [];
        foreach ($list as $key => $value) {
            if ($value->getName() != APP_PLATFORM_BDAPP) {
                $newList[] = [
                'label' => $value->getDisplayName(),
                'value' => $value->getName()
            ];
            }
        }

        return $newList;
    }
}