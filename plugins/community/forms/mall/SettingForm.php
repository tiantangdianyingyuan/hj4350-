<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/2
 * Time: 10:22
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\mall;


use app\forms\common\CommonOption;
use app\plugins\community\forms\common\CommonSetting;
use app\plugins\community\forms\Model;

class SettingForm extends Model
{
    public $is_apply;
    public $is_apply_money;
    public $is_allow_change;
    public $apply_money;
    public $apply_money_name;
    public $middleman;
    public $sell_out_sort;
    public $app_share_title;
    public $app_share_pic;
    public $banner;
    public $recruit_title;
    public $recruit_content;
    public $poster_style;
    public $image_style;
    public $activity_poster_style;
    public $image_bg;
    public $goods_sign_pic;
    public $is_share;
    public $pay_type;
    public $min_money;
    public $cash_service_charge;
    public $free_cash_min;

    public function rules()
    {
        return [
            [['is_apply', 'is_apply_money', 'sell_out_sort', 'is_allow_change', 'is_share'], 'integer'],
            [['apply_money', 'min_money', 'cash_service_charge'], 'number', 'min' => 0, 'max' => 999999],
            [['apply_money_name', 'middleman', 'app_share_title', 'app_share_pic', 'banner', 'recruit_title',
                'image_bg', 'goods_sign_pic'], 'trim'],
            [['apply_money_name', 'middleman', 'app_share_title', 'app_share_pic', 'banner', 'recruit_title',
                'image_bg', 'goods_sign_pic'], 'string'],
            ['apply_money_name', 'default', 'value' => '产品使用费'],
            ['middleman', 'default', 'value' => '团长'],
            [['recruit_content', 'poster_style', 'image_style', 'activity_poster_style', 'pay_type'], 'safe'],
            ['recruit_title', 'string', 'max' => 13],
            ['apply_money_name', 'string', 'max' => 10],
            ['free_cash_min', 'number', 'max' => 999999]
        ];
    }

    public function attributeLabels()
    {
        return [
            'is_apply' => '是否开启团长审核',
            'is_apply_money' => '是否开启申请门槛',
            'is_allow_change' => '是否允许更换团长',
            'apply_money' => '门槛金额',
            'apply_money_name' => '门槛名称', //
            'middleman' => '社区团购团长名称', //
            'app_share_title' => '分享标题',
            'app_share_pic' => '分享图片',
            'banner' => '背景图',
            'recruit_title' => '招募令标题',
            'recruit_content' => '招募令内容',
            'pay_type' => '提现方式',
            'min_money' => '提现门槛',
            'cash_service_charge' => '提现手续费',
            'free_cash_min' => '免提现手续费门槛',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if (!$this->image_style) {
                throw new \Exception('自定义商品海报样式不能为空');
            }
            if (!$this->poster_style) {
                throw new \Exception('自定义商品海报样式不能为空');
            }
            if (!$this->activity_poster_style) {
                throw new \Exception('自定义活动海报样式不能为空');
            }
            if (!$this->check($this->image_style, CommonSetting::IMAGE_STYLE)) {
                throw new \Exception('自定义商品海报image_style参数错误');
            }
            if (!$this->check($this->poster_style, CommonSetting::POSTER_STYLE)) {
                throw new \Exception('自定义商品海报poster_style参数错误');
            }
            if (!$this->check($this->activity_poster_style, CommonSetting::ACTIVITY_POSTER_STYLE)) {
                throw new \Exception('自定义活动海报activity_poster_style参数错误');
            }
            if ($this->free_cash_min !== '') {
                if ($this->free_cash_min < 0) {
                    throw new \Exception('免提现手续费门槛必须大于0');
                }
                $this->free_cash_min = price_format($this->free_cash_min);
            }
            $this->apply_money = price_format($this->apply_money);
            $this->min_money = price_format($this->min_money);
            $this->cash_service_charge = price_format($this->cash_service_charge);
            return $this->setting();
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }

    // 判断第一个数组是否是第二个数组的子集
    public function check($arr1, $arr2)
    {
        if ($arr1 == array_intersect($arr1, $arr2)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @throws \Exception
     * @return boolean
     */
    public function setting()
    {
        $common = CommonSetting::getCommon();
        $setting = $common->getSetting();
        $list = $common->checkDefault($setting, $this->attributes);
        $boolean = CommonOption::set('community_setting', $list, \Yii::$app->mall->id, 'plugin');
        return $boolean ? $this->success(['msg' => '保存成功']) : $this->fail(['msg' => '保存失败']);
    }

    public function recruit()
    {
        try {
            if (!$this->validate()) {
                return $this->getErrorResponse();
            }
            return $this->setting();
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }
}
