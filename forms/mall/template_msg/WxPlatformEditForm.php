<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\template_msg;

use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class WxPlatformEditForm extends Model
{
    public $app_id;
    public $app_secret;
    public $admin_open_list;
    public $template_list;

    public function rules()
    {
        return [
            [['app_id', 'app_secret',], 'required'],
            [['admin_open_list'], 'safe'],
            [['template_list'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'app_id' => '公众号AppId',
            'app_secret' => '公众号AppSecret',
        ];
    }

    public function save()
    {
        try {
            $option = CommonOption::set(
                Option::NAME_WX_PLATFORM,
                $this->attributes,
                \Yii::$app->mall->id,
                Option::GROUP_APP
            );

            if (!$option) {
                throw new \Exception('保存失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
