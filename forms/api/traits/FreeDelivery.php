<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/7/6
 * Time: 18:22
 */

namespace app\forms\api\traits;

use app\models\FreeDeliveryRules;

trait FreeDelivery
{
    private $xFreeDeliveryRules;

    /**
     * 获取指定的包邮规则，若不存在则取默认包邮规则
     * @param $mall_id
     * @param $mch_id
     * @param $shipping_id
     * @return mixed
     */
    private function getFreeDeliveryRules($mall_id, $mch_id, $shipping_id)
    {
        if (!$this->xFreeDeliveryRules) {
            $this->xFreeDeliveryRules = [];
        }
        if (!empty($this->xFreeDeliveryRules[$shipping_id])) {
            return $this->xFreeDeliveryRules[$shipping_id];
        }
        $rules = FreeDeliveryRules::find()
            ->where([
                        'id' => $shipping_id,
                        'mall_id' => $mall_id,
                        'is_delete' => 0,
                        'mch_id' => $mch_id,
                    ])->limit(1)->one();
        if (empty($rules)) {
            $rules = FreeDeliveryRules::find()
                ->where([
                            'mall_id' => $mall_id,
                            'is_delete' => 0,
                            'mch_id' => $mch_id,
                            'status' => 1
                        ])->limit(1)->one();
            $this->xFreeDeliveryRules[0] = $rules;
        }
        $this->xFreeDeliveryRules[$shipping_id] = $rules;
        return $this->xFreeDeliveryRules[$shipping_id];
    }

    /**
     * 获取包邮规则对应地区的包邮金额或件数
     * @param FreeDeliveryRules $deliveryRules
     * @param $address
     * @return int|mixed
     */
    private function getCondition(FreeDeliveryRules $deliveryRules, $address)
    {
        $districts = $deliveryRules->decodeDetail();
        $inDistrict = false;
        $condition = -1;
        foreach ($districts as $district) {
            foreach ($district['list'] as $item) {
                if ($item['id'] == $address->province_id) {
                    $inDistrict = true;
                    $condition = $district['condition'];
                    break;
                } elseif ($item['id'] == $address->city_id) {
                    $inDistrict = true;
                    $condition = $district['condition'];
                    break;
                } elseif ($item['id'] == $address->district_id) {
                    $inDistrict = true;
                    $condition = $district['condition'];
                    break;
                }
            }
            if ($inDistrict) {
                break;
            }
        }
        return $condition;
    }

    /**
     * 获取包邮文字信息
     * @param $mall_id
     * @param $mch_id
     * @param $shipping_id
     * @return string
     * @throws \Exception
     */
    private function getShippingText($mall_id, $mch_id, $shipping_id)
    {
        /**@var FreeDeliveryRules $freeDelivery**/
        $freeDelivery = $this->getFreeDeliveryRules(
            $mall_id,
            $mch_id,
            $shipping_id
        );
        if ($freeDelivery) {
            $shipping = '';
            $districts = $freeDelivery->decodeDetail();
            switch ($freeDelivery->type) {
                case 1:
                    foreach ($districts as $i) {
                        $shipping .= sprintf('订单满%s元包邮', $i['condition']);
                        $shipping .= '(';
                        $shipping .= implode('、', array_column((array)$i['list'], 'name'));
                        $shipping .= ')，';
                    }
                    break;
                case 2:
                    foreach ($districts as $i) {
                        $shipping .= sprintf('订单满%s件包邮', $i['condition']);
                        $shipping .= '(';
                        $shipping .= implode('、', array_column((array)$i['list'], 'name'));
                        $shipping .= ')，';
                    }
                    break;
                case 3:
                    foreach ($districts as $i) {
                        $shipping .= sprintf('单品满%s元包邮', $i['condition']);
                        $shipping .= '(';
                        $shipping .= implode('、', array_column((array)$i['list'], 'name'));
                        $shipping .= ')，';
                        if ($i['condition'] < $this->getPriceMin()) {
                            $this->isExpress = true;
                        }
                    }
                    break;
                case 4:
                    foreach ($districts as $i) {
                        $shipping .= sprintf('单品满%s件包邮', $i['condition']);
                        $shipping .= '(';
                        $shipping .= implode('、', array_column((array)$i['list'], 'name'));
                        $shipping .= ')，';
                        if ($i['condition'] == 1) {
                            $this->isExpress = true;
                        }
                    }
                    break;
                default:
                    throw new \Exception('未知的包邮类型');
            }
            return trim($shipping, '，');
        }

        return '';
    }
}
