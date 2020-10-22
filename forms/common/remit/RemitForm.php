<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/24
 * Time: 16:58
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\remit;


use app\models\Model;
use app\models\User;

/**
 * @property User $user
 */
class RemitForm extends Model
{
    public $orderNo;
    public $amount;
    public $user;
    public $title;
    public $type;
    public $desc;
    public $price;
    public $service_charge;

    public function rules()
    {
        return [
            [['orderNo', 'amount', 'user', 'title', 'type'], 'required'],
            ['orderNo', 'string', 'max' => 32],
            [['title', 'desc'], 'string', 'max' => 128],
            [['amount'], function ($attribute, $params) {
                if (!is_float($this->amount) && !is_int($this->amount) && !is_double($this->amount)) {
                    $this->addError($attribute, '`amount`必须是数字类型。');
                }
            }],
            [['amount', 'price'], 'number', 'min' => 0.01, 'max' => 100000000],
            [['type'], 'in', 'range' => ['wechat', 'alipay', 'bank', 'auto', 'balance']],
            ['user', function ($attribute, $param) {
                if (!$this->user instanceof User) {
                    $this->addError($attribute, 'user必须是app\\models\User的对象');
                }
            }],
            ['service_charge', 'number']
        ];
    }

    /**
     * PaymentTransfer constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        if (!$this->validate()) {
            dd($this->errors);
        }
    }
}
