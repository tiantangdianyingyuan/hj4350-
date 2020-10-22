<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/11
 * Time: 17:20
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\core\mail;


use app\models\Form;
use app\models\MailSetting;
use app\models\Mall;
use app\models\Order;
use yii\base\Component;
use yii\swiftmailer\Mailer;

/**
 * @property Mall $mall
 * @property Order $order
 */
class SendMail extends Component
{
    public $mall;
    public $mch_id = 0;
    public $order;
    public $mailSetting;

    /**
     * @param $view
     * @param $params
     * @return bool
     * 邮件发送配置
     */
    protected function send($view, $params)
    {
        $mailSetting = $this->mailSetting;
        $receive = str_replace("，", ",", $mailSetting->receive_mail);
        $receiveMail = explode(",", $receive);
        $messages = [];
        /* @var Mailer $mailer */
        $mailer = \Yii::$app->mailer;
        $mailer->transport = [
            'class' => 'Swift_SmtpTransport',
            'host' => 'smtp.qq.com',
            'username' => $mailSetting->send_mail,
            'password' => $mailSetting->send_pwd,
            'port' => '465',
            'encryption' => 'ssl',//    tls | ssl
        ];
        foreach ($receiveMail as $mail) {
            $compose = $mailer->compose($view, $params);
            $compose->setFrom($mailSetting->send_mail); //要发送给那个人的邮箱
            $compose->setTo($mail); //要发送给那个人的邮箱
            $compose->setSubject($mailSetting->send_name); //邮件主题
            $messages[] = $compose;
        }
        $mailer->sendMultiple($messages);
        return true;
    }

    /**
     * @return bool
     * 订单支付提醒
     */
    public function orderPayMsg()
    {
        return $this->newOrderPayMsg();
        /*
        try {
            $this->mailSetting = $this->getMailSetting();
            $this->send('order', [
                'order' => $this->order,
                'mall' => $this->mall
            ]);
            return true;
        } catch (\Exception $exception) {
            \Yii::error('--发送邮件：--' . $exception->getMessage());
            return false;
        }
        */
    }

    public function newOrderPayMsg()
    {
        try {
            $this->mailSetting = $this->getMailSetting();

            $orderDetail = $this->order->detail;
            $form_ids = array_column($orderDetail, 'form_id');
            array_multisort($form_ids, SORT_DESC, $orderDetail);

            $show_type = \yii\helpers\BaseJson::decode($this->mailSetting->show_type) ?: ['attr' => 1, 'goods_no' => 0, 'form_data' => 0];

            /* 表单处理方式1 */
            $is_form_data = $show_type['form_data'];
            $ids = array_unique($form_ids);
            if (count($ids) <= 2 && in_array(0, $ids)) {
                $is_form_data = 0;
            }

            $data = [];
            foreach ($orderDetail as $detail) {
                $sentinel = true;
                foreach ($data as &$info) {
                    if ($info['form_id'] == $detail->form_id || $is_form_data == 0) {
                        $sentinel = false;
                        array_push($info['goods_list'], $detail);
                    }
                }
                unset($info);
                //预约表单位置未移动(兼容老数据)  form_data
                $sentinel && $data[] = [
                    'goods_list' => [$detail],
                    'form_data' => !\yii\helpers\BaseJson::decode($detail->form_data) ? \yii\helpers\BaseJson::decode($this->order->order_form) ?: [] : \yii\helpers\BaseJson::decode($detail->form_data),
                    'form_id' => $detail->form_id,
                    'form_name' => Form::findOne($detail->form_id)['name'] ?? ''
                ];
            }
            $this->send('order', [
                'order' => $this->order,
                'mall' => $this->mall,
                'new_goods_list' => array_reverse($data),
                'show_type' => $show_type,
            ]);
            return true;
        } catch (\Exception $exception) {
            \Yii::error('--发送邮件：--' . $exception->getMessage());
            return false;
        }
    }

    public function test()
    {
        $this->send('test', []);
        return true;
    }

    public function getMailSetting()
    {
        $mailSetting = MailSetting::findOne([
            'mall_id' => $this->mall->id,
            'is_delete' => 0,
            'status' => 1,
            'mch_id' => $this->order->mch_id,
        ]);
        if (!$mailSetting) {
            throw new \Exception('商城未设置邮件发送');
        }
        return $mailSetting;
    }

    public function refundMsg()
    {
        try {
            $this->mailSetting = $this->getMailSetting();
            $this->send('refund', [
                'mall' => $this->mall
            ]);
            return true;
        } catch (\Exception $exception) {
            \Yii::error('--发送邮件：--' . $exception->getMessage());
            return false;
        }
    }
}
