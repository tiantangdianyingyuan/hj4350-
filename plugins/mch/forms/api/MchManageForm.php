<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\api;


use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\forms\common\order\CommonOrderStatistic;
use app\forms\common\template\TemplateList;
use app\models\Formid;
use app\models\Model;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchVisitLog;
use app\plugins\mch\Plugin;

class MchManageForm extends Model
{
    public $mch_id;
    public $year;
    public $monthly;

    public function rules()
    {
        return [
            [['mch_id'], 'required'],
            [['mch_id', 'monthly', 'year'], 'integer']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $mch = Mch::find()->where(['id' => $this->mch_id, 'is_delete' => 0])->asArray()->one();
            if (!$mch) {
                throw new \Exception('商户不存在');
            }

            $visitCount = MchVisitLog::find()->where(['mch_id' => $this->mch_id])->count();
            $mch['visit_count'] = $visitCount;
            $mch['page_url'] = \Yii::$app->urlManager->createAbsoluteUrl([
                'admin/passport/mch-login','mall_id' => base64_encode($mch['mall_id'])
            ]);

            $form = new CommonOrderStatistic();
            $form->mch_id = $this->mch_id;
            $form->sign = (new Plugin())->getName();
            $res = $form->getAll();
            $mch = array_merge($mch, $res);

            $formIdCount = Formid::find()->alias('f')
                ->andWhere(['f.user_id' => $mch['user_id']])
                ->andWhere(['>', 'f.remains', 0])
                ->andWhere(['>', 'f.expired_at', date('Y-m-d H:i:s')])
                ->count();
            $mch['form_id_count'] = $formIdCount;
            $mch['is_add_formid'] = \Yii::$app->user->id == $mch['user_id'] ? true : false;
            $mch['template_message_list']= $this->getTemplateMessage();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $mch
                ]
            ];
        } catch (\Exception $e) {
            \Yii::error('多商户管理首页错误：' . $e->getLine() . '-' . $e->getMessage());
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    private function getTemplateMessage()
    {
        $arr = ['mch_order_tpl'];
        $list = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $arr);
        return $list;
    }

    public function getQrCode()
    {
        try {
            $mch = Mch::find()->where([
                'id' => $this->mch_id,
                'is_delete' => 0,
                'review_status' => 1,
            ])->with('store', 'user')->asArray()->one();

            if (!$mch) {
                throw new \Exception('店铺不存在');
            }
            $mch['store']['pic_url'] = \Yii::$app->serializer->decode($mch['store']['pic_url']);

            $form = new CommonQrCode();
            $res = $form->getQrCode(['mch_id' => $mch['id'],'user_id' => \Yii::$app->user->id], 150, 'plugins/mch/shop/shop');



            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'mch' => $mch,
                    'qr_code' => $res,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function getStatistic()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $form = new  CommonOrderStatistic();
        $form->mch_id = $this->mch_id;
        $form->sign = (new Plugin())->getName();
        $form->monthly = $this->monthly;
        $form->year = $this->year;
        $res = $form->getAll(['monthly_order_pay_price_count']);

        $res['monthly_order_pay_price_average'] =
            round($res['monthly_order_pay_price_count'] / $form->monthlyDay, 2);

        $trendArr = [];
        for ($i = 1; $i <= $form->monthlyDay; $i++) {
            $form->day = $i;
            $trendArr[] = $form->getDayOrderPayPriceCount();
        }
        $res['trend_arr'] = $trendArr;
        // 如果是当前是一月份则和去年12月份想比较
        if ($this->monthly == 1) {
            $form->monthly = 12;
            $form->year = $form->year - 1;
        } else {
            $form->monthly = $this->monthly - 1;
        }
        // 与上月金额比较
        $prevMonthlyPayPriceCount = $form->getMonthlyOrderPayPriceCount();
        $contrastPrice = round($res['monthly_order_pay_price_count'] - $prevMonthlyPayPriceCount, 2);
        $res['contrast_prev_monthly'] = $contrastPrice;

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => $res
        ];
    }

    public function getYearList()
    {
        $monthNameList = [
            1 => [
                'name_en' => 'JAN',
                'name_cn' => '1月',
            ],
            2 => [
                'name_en' => 'FEB',
                'name_cn' => '2月',
            ],
            3 => [
                'name_en' => 'MAR',
                'name_cn' => '3月',
            ],
            4 => [
                'name_en' => 'APR',
                'name_cn' => '4月',
            ],
            5 => [
                'name_en' => 'MAY',
                'name_cn' => '5月',
            ],
            6 => [
                'name_en' => 'JUNE',
                'name_cn' => '6月',
            ],
            7 => [
                'name_en' => 'JULY',
                'name_cn' => '7月',
            ],
            8 => [
                'name_en' => 'AUG',
                'name_cn' => '8月',
            ],
            9 => [
                'name_en' => 'SEPT',
                'name_cn' => '9月',
            ],
            10 => [
                'name_en' => 'OCT',
                'name_cn' => '10月',
            ],
            11 => [
                'name_en' => 'NOV',
                'name_cn' => '11月',
            ],
            12 => [
                'name_en' => 'DEC',
                'name_cn' => '12月',
            ],
        ];
        $startYear = 2018;
        $start_month = 1;
        $endYear = intval(date('Y'));
        $endMonth = intval(date('m'));
        $list = [];
        for ($i = 0; ($startYear + $i) <= $endYear; $i++) {
            $monthList = [];
            if ($startYear + $i == $endYear) {
                for ($j = 1; $j <= $endMonth; $j++) {
                    $monthList[] = [
                        'month' => $j,
                        'name_en' => $monthNameList[$j]['name_en'],
                        'name_cn' => $monthNameList[$j]['name_cn'],
                        'active' => false,
                    ];
                }
            } else {
                for ($j = 1; $j <= 12; $j++) {
                    $monthList[] = [
                        'month' => $j,
                        'name_en' => $monthNameList[$j]['name_en'],
                        'name_cn' => $monthNameList[$j]['name_cn'],
                        'active' => false,
                    ];
                }
            }
            $list[] = [
                'year' => $startYear + $i,
                'month_list' => $monthList,
                'active' => false,
            ];
        }
        $list[count($list) - 1]['active'] = true;
        $list[count($list) - 1]['month_list'][count($list[count($list) - 1]['month_list']) - 1]['active'] = true;
        return [
            'code' => 0,
            'data' => [
                'year_list' => $list,
                'current_year' => $endYear,
                'current_month' => $endMonth,
            ],
        ];
    }
}
