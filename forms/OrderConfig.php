<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/1
 * Time: 10:36
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms;


use app\models\Model;

/**
 * @property integer $is_sms
 * @property integer $is_mail
 * @property integer $is_print
 * @property integer $is_share
 * @property integer $support_share
 * @property integer $is_member_price
 */
class OrderConfig extends Model
{
    public $is_sms;
    public $is_print;
    public $is_mail;
    public $is_share;
    public $support_share;
    public $is_member_price;

    public function rules()
    {
        return [
            [['is_sms', 'is_print', 'is_mail', 'is_share', 'support_share'], 'default', 'value' => 0],
            [['is_sms', 'is_print', 'is_mail', 'is_share', 'support_share'], 'integer'],
            [['is_sms', 'is_print', 'is_mail', 'is_share', 'support_share'], 'in', 'range' => [0, 1]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'is_sms' => '是否开启短信提醒',
            'is_print' => '是否开启小票打印',
            'is_mail' => '是否开启邮件通知',
            'is_share' => '是否开启分销',
            'support_share' => '是否支持分销',
        ];
    }

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        if (!$this->validate()) {
            \Yii::error('--order config --' . $this->getErrorMsg());
        }
    }

    public function setOrder()
    {
        $this->is_share = 1;
        $this->is_print = 1;
        $this->is_sms = 1;
        $this->is_mail = 1;
        $this->support_share = 1;
    }
}
