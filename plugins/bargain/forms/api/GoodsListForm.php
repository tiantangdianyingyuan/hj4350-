<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/14
 * Time: 9:15
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\forms\api;


use app\core\response\ApiCode;
use app\forms\common\ecard\CommonEcard;
use app\models\Mall;
use app\models\Model;
use app\plugins\bargain\forms\common\goods\CommonBargainGoods;
use app\plugins\bargain\models\BargainGoods;
use app\plugins\bargain\models\BargainOrder;

/**
 * @property Mall $mall
 */
class GoodsListForm extends ApiModel
{
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['page', 'limit'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['limit'], 'default', 'value' => 10],
        ];
    }
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        /* @var BargainGoods[] $bargainGoodsList */
        $bargainGoodsList = CommonBargainGoods::getCommonGoods($this->mall)->getApiGoodsList($this->page);

        // TODO
        $newList = [];
        foreach ($bargainGoodsList as $bargainGoods) {
            /* @var BargainOrder[] $bargainOrderList */
            $bargainOrderList = $bargainGoods->getOrderList()->where(['is_delete' => 0])
                ->groupBy('user_id')->limit(3)->all();
            $userList = [];
            foreach ($bargainOrderList as $bargainOrder) {
                $userList[] = [
                    'avatar' => $bargainOrder->user->userInfo->avatar,
                ];
            }
            $sales = ($bargainGoods->userOrderList ? count($bargainGoods->userOrderList) : 0) + $bargainGoods->goods->virtual_sales; // 正在进行砍价人数
            $stock = CommonEcard::getCommon()->getEcardStock($bargainGoods->stock, $bargainGoods->goods);
            $newItem = [
                'name' => $bargainGoods->goods->name,
                'cover_pic' => $bargainGoods->goods->coverPic,
                'price' => price_format($bargainGoods->goods->price, 'float', 2),
                'min_price' => price_format($bargainGoods->min_price, 'float', 2),
                'sales' => $sales,
                'goods_id' => $bargainGoods->goods_id,
                'user_list' => $userList,
                'status' => $bargainGoods->goods->status,
                'stock' => $stock,
                'video_url' => $bargainGoods->goods->videoUrl,
            ];
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $newList
            ]
        ];
    }
}
