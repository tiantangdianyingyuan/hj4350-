<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonAppConfig;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class MallOverrunForm extends Model
{
    public $form;

    public function rules()
    {
        return [
            [['form'], 'safe']
        ];
    }

    public function save()
    {
        try {
            $this->checkData();
            $option = CommonOption::set(Option::NAME_OVERRUN, $this->form, 0, 'admin');
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function setting()
    {
        $option = $this->getSetting();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $option
            ]
        ];
    }

    public function getSetting()
    {
        $option = CommonOption::get(Option::NAME_OVERRUN, 0, 'admin', $this->getDefault());

        $option = CommonAppConfig::check($option, $this->getDefault());

        $option['is_img_overrun'] = $option['is_img_overrun'] == 'true' ? true : false;
        $option['is_diy_module_overrun'] = $option['is_diy_module_overrun'] == 'true' ? true : false;
        $option['is_video_overrun'] = $option['is_video_overrun'] == 'true' ? true : false;
        return $option;
    }

    public function getDefault()
    {
        return [
            'img_overrun' => 1,
            'is_img_overrun' => false,
            'diy_module_overrun' => 20,
            'is_diy_module_overrun' => false,
            'video_overrun' => 50,
            'is_video_overrun' => false,
        ];
    }

    private function checkData()
    {
        if ($this->form['img_overrun'] == '') {
            throw new \Exception('请输入上传图片限制');
        }

        if ($this->form['diy_module_overrun'] == '') {
            throw new \Exception('请输入diy组件限制');
        }
        if ($this->form['video_overrun'] == '') {
            throw new \Exception('请输入diy组件限制');
        }

        if ($this->form['video_overrun'] > 50) {
            throw new \Exception('视频大小限制最高为50');
        }
    }
}
