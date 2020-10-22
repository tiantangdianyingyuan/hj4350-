<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/4/30
 * Time: 15:29
 */

namespace app\plugins\flash_sale\forms\mall;

use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;
use Exception;
use Yii;

class SettingForm extends Model
{
    protected $mall;

    public $is_share;
    public $is_territorial_limitation;
    public $is_coupon;
    public $is_member_price;
    public $is_integral;
    public $svip_status;
    public $is_full_reduce;
    public $title;
    public $content;
    public $is_offer_price;

    public function rules()
    {
        return [
            [['title', 'content'], 'trim'],
            [['title', 'content'], 'string'],
            [
                ['is_share', 'is_territorial_limitation', 'is_coupon', 'is_member_price', 'is_integral', 'svip_status',
                    'is_full_reduce', 'is_offer_price'], 'integer'
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => '规则标题',
            'content' => '活动规则',
            'is_share' => '分销开关',
            'is_territorial_limitation' => '区域购买开关',
            'is_coupon' => '优惠券开关',
            'is_member_price' => '会员价开关',
            'is_integral' => '积分抵扣开关',
            'svip_status' => '超级会员卡开关',
            'is_offer_price' => '是否开启起送规则',
            'is_full_reduce' => '是否参加满减活动'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $array = [
                'title' => $this->title,
                'content' => $this->content,
                'is_share' => $this->is_share,
                'is_territorial_limitation' => $this->is_territorial_limitation,
                'is_coupon' => $this->is_coupon,
                'is_member_price' => $this->is_member_price,
                'is_integral' => $this->is_integral,
                'svip_status' => $this->svip_status,
                'is_offer_price' => $this->is_offer_price,
                'is_full_reduce' => $this->is_full_reduce
            ];

            $result = CommonOption::set('flash_sale_setting', $array, Yii::$app->mall->id, Option::GROUP_ADMIN);
            if (!$result) {
                throw new Exception('保存失败');
            }

            return [
                'code' => 0,
                'msg' => '保存成功'
            ];
        } catch (Exception $e) {
            return [
                'code' => 1,
                'msg' => $e->getMessage()
            ];
        }
    }
}
