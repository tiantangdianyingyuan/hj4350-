<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/26
 * Time: 15:08
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\share;


use app\forms\common\CommonOption;
use app\forms\common\share\CommonShareOrder;
use app\models\Model;
use app\models\Option;
use app\models\OrderRefund;
use app\models\User;

/**
 * @property User $user
 */
class ShareOrderForm extends Model
{
    public $status;
    public $page;
    public $limit;

    public $user;

    public function rules()
    {
        return [
            [['page', 'limit', 'status'], 'integer'],
            ['status', 'in', 'range' => [0, 1, 2, 3]],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $this->user = \Yii::$app->user->identity;
        $form = new CommonShareOrder();
        $form->mall = \Yii::$app->mall;
        $form->shareUser = $this->user;
        $form->page = $this->page;
        $form->limit = $this->limit;
        $orderList = $form->getList($this->status);
        $list = $form->search($orderList);

        $data = CommonOption::get(Option::NAME_SHARE_CUSTOMIZE, \Yii::$app->mall->id, 'api');

        $orderRefund = OrderRefund::find()->where([
            'type' => 1, 'status' => 1, 'is_confirm' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0
        ])->select('order_detail_id')->column();
        foreach ($list as &$item) {
            // 佣金及佣金等级
            switch ($this->user->id) {
                case $item['first_parent_id']:
                    $item['share_status'] = $data ? $data['words']['one_share']['name'] : '一级';
                    $item['share_money'] = round($item['first_price'], 2);
                    if ($item['first_parent_id'] == $item['user_id']) {
                        $item['share_status'] = '自购返利';
                    }
                    break;
                case $item['second_parent_id']:
                    $item['share_status'] = $data ? $data['words']['second_share']['name'] : '二级';
                    $item['share_money'] = round($item['second_price'], 2);
                    if ($item['first_parent_id'] == $item['user_id']) {
                        $item['share_status'] = $data ? $data['words']['one_share']['name'] : '一级';
                    }
                    break;
                case $item['third_parent_id']:
                    $item['share_status'] = $data ? $data['words']['three_share']['name'] : '三级';
                    $item['share_money'] = round($item['third_price'], 2);
                    if ($item['first_parent_id'] == $item['user_id']) {
                        $item['share_status'] = $data ? $data['words']['second_share']['name'] : '二级';
                    }
                    break;
                default:
                    $item['share_money'] = 0;
            }

            $goodsList = [];
            foreach ($item['detail'] as $value) {
                if (!empty($orderRefund) && in_array($value['order_detail_id'], $orderRefund)) {
                } else {
                    $value['cover_pic'] = $value['goods_info']['goods_attr']['cover_pic'];
                    $value['name'] = $value['goods_info']['goods_attr']['name'];
                    $goodsList[] = $value;
                }
            }
            $item['detail'] = $goodsList;
        }
        unset($item);
        return [
            'code' => 0,
            'data' => [
                'list' => $list
            ]
        ];
    }
}
