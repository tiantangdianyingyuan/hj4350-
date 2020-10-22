<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\exchange\forms\common\CommonModel;
use app\plugins\exchange\forms\mall\export\CardOrderExport;
use app\plugins\exchange\models\ExchangeOrder;

class CardOrderForm extends Model
{
    public $goods_id;
    public $goods_name;
    public $keyword_1;
    public $keyword;
    public $platform;
    public $flag;

    public function rules()
    {
        return [
            [['goods_id'], 'integer'],
            [['goods_name', 'keyword_1', 'keyword', 'platform', 'flag'], 'string'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            /** @var  $query */
            $query = ExchangeOrder::find()->alias('e')->where([
                'e.mall_id' => \Yii::$app->mall->id,
                'e.is_delete' => 0,
            ])
                ->joinWith(['order o'])
                ->joinWith(['goods g'])
                ->joinWith(['goods.goodsWarehouse gw'])
                ->joinWith(['library l'])
                ->joinWith(['user.userInfo ui'])
                ->joinWith(['user u'])
                ->orderBy(['e.id' => SORT_DESC]);

            $query->keyword($this->goods_id, ['e.goods_id' => $this->goods_id]);
            if ($this->keyword) {
                $query->keyword($this->keyword_1 == 1, ['like', 'o.order_no', $this->keyword]);
                $query->keyword($this->keyword_1 == 4, ['like', 'o.user_id', $this->keyword]);
                $query->keyword($this->keyword_1 == 2, ['like', 'u.nickname', $this->keyword]);
                $query->keyword($this->keyword_1 == 8, ['like', 'l.name', $this->keyword]);
            }
            $query->keyword($this->platform, ['ui.platform' => $this->platform]);
            $query->keyword($this->goods_name, ['OR', ['like', 'g.id', $this->goods_name], ['like', 'gw.name', $this->goods_name]]);

            if ($this->flag === "EXPORT") {
                $exp = new CardOrderExport();
                $exp->goods_id = $this->goods_id;
                $exp->page = \Yii::$app->request->post('page');
                return $exp->export($query);
            }


            $list = $query
                ->page($pagination)
                ->all();
            $list = array_map(function ($item) {
                $status = CommonModel::getStatus($item->library, $item->code, $msg);
                return [
                    'order_id' => $item->order->id,
                    'order_no' => $item->order->order_no,
                    'avatar' => $item->user->userInfo->avatar,
                    'user_id' => $item->user->userInfo->user_id,
                    'nickname' => $item->user->nickname,
                    'platform' => $item->user->userInfo->platform,
                    'goods_name' => $item->goods->name,
                    'library_name' => $item->library->name,
                    'code' => $item->code->code,
                    'created_at' => $item->created_at,
                    'status' => $status,
                    'msg' => $msg,
                ];
            }, $list);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '获取成功',
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
