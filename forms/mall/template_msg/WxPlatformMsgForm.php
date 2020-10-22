<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\template_msg;

use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\common\mptemplate\MpTplGet;
use app\forms\common\mptemplate\MpTplMsgCSend;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\models\Model;
use app\models\MpTemplateRecord;
use app\models\Option;

class WxPlatformMsgForm extends Model
{
    public $key;
    public $app_id;
    public $app_secret;
    public $template_id;
    public $admin_open_list;

    const NEWORDER = 'newOrderTpl';
    const SHAREAPPLY = 'shareApplyTpl';
    const SHAREWITHDRAW = 'shareWithdrawTpl';
    const MCHAPPLY = 'mchApplyTpl';
    const MCHGOODAPPLY = 'mchGoodApplyTpl';
    const CANCELORDER = 'cancelOrderTpl';
    const SALEORDER = 'saleOrderTpl';
    const APPLYSUBMIT = 'applySubmitTpl';

    public function rules()
    {
        return [
            [['app_id', 'app_secret', 'template_id'], 'string'],
            [['key'], 'safe'],
            [['admin_open_list'], 'trim']
        ];
    }

    public function attributeLabels()
    {
        return [
            'app_id' => '公众号AppId',
            'app_secret' => '公众号AppSecret',
            'template_id' => '模板信息',
        ];
    }

    public function formatOption($option)
    {
        if(!$option) {
            return $this->getDefault();
        }
        try {
            $default = $this->getDefault();
            foreach ($default as $k => $v) {
                if ((!is_array($v) || $k == 'admin_open_list') && array_key_exists($k, $option)) {
                    $default[$k] = $option[$k];
                    continue;
                }
                if ($k == 'template_list' && array_key_exists($k, $option)) {
                    foreach ($v as $k1 => $v1) {
                        foreach ($option[$k] as $k2 => $v2) {
                            if ($v1['key_name'] == $v2['key_name']) {
                                //todo 可改进
                                $pic_url = $default[$k][$k1]['pic_url'];
                                $default[$k][$k1] = $option[$k][$k1];
                                $default[$k][$k1]['pic_url'] = $pic_url;
                                break;
                            }
                        }
                    }
                }
            }
            return $default;
        } catch (\Exception $e) {
            return $this->getDefault();
        }
    }


    public function getDetail()
    {
        $option = CommonOption::get(Option::NAME_WX_PLATFORM, \Yii::$app->mall->id, Option::GROUP_APP);
        $option = $this->formatOption($option);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $option,
            ]
        ];
    }

    public function mpTemplate()
    {
        try {
            $mp = new MpTplGet();
            $list = $mp->getAccessToken($this->app_id, $this->app_secret)->getTemplateList() ?: [];
            $tpl = [
                self::NEWORDER => '',
                self::SHAREAPPLY => '',
                self::SHAREWITHDRAW => '',
                self::MCHAPPLY => '',
                self::MCHGOODAPPLY => '',
                self::CANCELORDER => '',
                self::SALEORDER => '',
                self::APPLYSUBMIT => '',
            ];

            foreach ($list as $v) {
                $info = md5($v['title'] . $v['primary_industry'] . $v['deputy_industry']);
                if ($info == md5('新订单通知IT科技互联网|电子商务')) {
                    $tpl[self::NEWORDER] = $v['template_id'];
                    continue;
                }
                if ($info == md5('入驻申请提醒IT科技互联网|电子商务')) {
                    $tpl[self::SHAREAPPLY] = $v['template_id'];
                    $tpl[self::MCHAPPLY] = $v['template_id'];
                    continue;
                }
                if ($info == md5('提现申请通知IT科技互联网|电子商务')) {
                    $tpl[self::SHAREWITHDRAW] = $v['template_id'];
                    continue;
                }
                if ($info == md5('服务申请通知IT科技互联网|电子商务')) {
                    $tpl[self::MCHGOODAPPLY] = $v['template_id'];
                    continue;
                }
                if ($info == md5('订单取消通知IT科技互联网|电子商务')) {
                    $tpl[self::CANCELORDER] = $v['template_id'];
                    continue;
                }
                if ($info == md5('订单售后通知IT科技互联网|电子商务')) {
                    $tpl[self::SALEORDER] = $v['template_id'];
                    continue;
                }
                if ($info == md5('申请提交成功通知IT科技互联网|电子商务')) {
                    $tpl[self::APPLYSUBMIT] = $v['template_id'];
                    continue;
                }
            }

            foreach ($tpl as $k => $v) {
                if ($v) continue;
                if ($k == self::NEWORDER) {
                    $key = 'TM00351';
                }
                if ($k == self::SHAREAPPLY) {
                    $key = 'OPENTM407435154';
                }
                if ($k == self::SHAREWITHDRAW) {
                    $key = 'OPENTM410103702';
                }
                if ($k == self::MCHAPPLY) {
                    $key = 'OPENTM407435154';
                }
                if ($k == self::MCHGOODAPPLY) {
                    $key = 'OPENTM412090267';
                }
                if ($k == self::CANCELORDER) {
                    $key = 'OPENTM406648164';
                }
                if ($k == self::SALEORDER) {
                    $key = 'OPENTM410195709';
                }
                if ($k == self::APPLYSUBMIT) {
                    $key = 'OPENTM411517700';
                }
                $tpl[$k] = $mp->addTemplate($key);
            };

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '获取成功',
                'data' => $tpl,
            ];
        } catch (\Exception $exception) {
            $msg = $exception->getMessage();
            $reg = '/[^\f\n\r\v]+?(\d*(\.((2(5[0-5]|[0-4]\d))|[0-1]?\d{1,2})){3})[^\f\n\r\v]+$/';
            if (preg_match($reg, $msg, $match)) {
                $msg = sprintf('公众号「开发」—「基本配置」—IP白名单未添加');
            }
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $msg,
            ];
        }
    }

    public function test()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $customize = new MpTplMsgCSend();
            $customize->admin_open_list = $this->admin_open_list;
            $customize->template_id = $this->template_id;
            $customize->app_id = $this->app_id;
            $customize->app_secret = $this->app_secret;

            $tplMsg = new MpTplMsgSend();
            if ($this->key == 'new_order_tpl') {
                $tplMsg->method = self::NEWORDER;
                $tplMsg->params = [
                    'sign' => '',
                    'goods' => '测试商品',
                    'time' => date('Y-m-d H:i:s'),
                    'user' => '张三',
                ];
            } elseif ($this->key == 'share_apply_tpl') {
                $tplMsg->method = self::SHAREAPPLY;
                $tplMsg->params = [
                    'time' => date('Y-m-d H:i:s'),
                    'content' => '申请已提交',
                ];
            } elseif ($this->key == 'share_withdraw_tpl') {
                $tplMsg->method = self::SHAREWITHDRAW;
                $tplMsg->params = [
                    'time' => mysql_timestamp(),
                    'money' => '13.60',
                    'user' => '张三',
                ];
            } elseif ($this->key == 'mch_apply_tpl') {
                $tplMsg->method = self::MCHAPPLY;
                $tplMsg->params = [
                    'time' => mysql_timestamp(),
                    'content' => '申请已提交',
                ];
            } elseif ($this->key == 'mch_good_apply_tpl') {
                $tplMsg->method = self::MCHGOODAPPLY;
                $tplMsg->params = [
                    'goods' => '测试商品',
                ];
            } elseif ($this->key == 'cancel_order_tpl') {
                $tplMsg->method = self::CANCELORDER;
                $tplMsg->params = [
                    'order_no' => '20190517165553459179',
                    'price' => '1',
                ];
            } elseif ($this->key == 'sale_order_tpl') {
                $tplMsg->method = self::SALEORDER;
                $tplMsg->params = [
                    'order_no' => '30190517165553459179',
                    'status' => '已发货',
                ];
            } elseif ($this->key == 'apply_submit_tpl') {
                $tplMsg->method = self::APPLYSUBMIT;
                $tplMsg->params = [
                    'time' => mysql_timestamp()
                ];
            } else {
                throw new \Exception('模板消息提交异常');
            }
            $info = $tplMsg->sendTemplate($customize);

            $status = \Yii::$app->queue->isDone($info['queueId']);
            $t1 = microtime(true);
            while (!$status) {
                sleep(0.25);
                $status = \Yii::$app->queue->isDone($info['queueId']);
                $t2 = microtime(true);
                if (round($t2 - $t1, 3) > 10) {
                    throw new \Exception('队列处理失败');
                    break;
                }
            }

            $list = MpTemplateRecord::findAll([
                'mall_id' => \Yii::$app->mall->id,
                'token' => $info['token'],
            ]);
            $list = \yii\helpers\ArrayHelper::toArray($list);
            $list = array_map(function ($item) {
                $msg = $item['error'];
                $reg = '/errCode 40164/';
                if (preg_match($reg, $msg, $match)) {
                    $msg = sprintf('公众号「开发」—「基本配置」—IP白名单未添加');
                }
                $item['error'] = $msg;
                return $item;
            }, $list);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '已发送',
                'data' => $list,
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    public function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/wechatplatform/';

        return [
            'app_id' => '',
            'app_secret' => '',
            'admin_open_list' => [['open_id' => '']],
            'template_list' => [
                [
                    'name' => '新订单通知',
                    'key_name' => 'new_order_tpl',
                    'new_order_tpl' => '',
                    'pic_url' => $iconUrlPrefix . 'new_order_tpl.png',
                ],
                [
                    'name' => '分销商入驻申请通知',
                    'key_name' => 'share_apply_tpl',
                    'share_apply_tpl' => '',
                    'pic_url' => $iconUrlPrefix . 'apply_tpl.png',
                ],
                [
                    'name' => '分销商提现通知',
                    'key_name' => 'share_withdraw_tpl',
                    'share_withdraw_tpl' => '',
                    'pic_url' => $iconUrlPrefix . 'withdraw_tpl.png',
                ],
                [
                    'name' => '多商户入驻申请通知',
                    'key_name' => 'mch_apply_tpl',
                    'mch_apply_tpl' => '',
                    'pic_url' => $iconUrlPrefix . 'apply_tpl.png',
                ],
                [
                    'name' => '入驻商商品上架申请通知',
                    'key_name' => 'mch_good_apply_tpl',
                    'mch_good_apply_tpl' => '',
                    'pic_url' => $iconUrlPrefix . 'service_apply.png',
                ],
                [
                    'name' => '订单申请取消通知',
                    'key_name' => 'cancel_order_tpl',
                    'cancel_order_tpl' => '',
                    'pic_url' => $iconUrlPrefix . 'order_cancel.png',
                ],

                [
                    'name' => '订单申请售后通知',
                    'key_name' => 'sale_order_tpl',
                    'sale_order_tpl' => '',
                    'pic_url' => $iconUrlPrefix . 'order_refund.png',
                ],
                [
                    'name' => '自定义表单通知',
                    'key_name' => 'apply_submit_tpl',
                    'apply_submit_tpl' => '',
                    'pic_url' => $iconUrlPrefix . 'apply_submit_tpl.png',
                ]
            ]
        ];
    }
}