<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/11
 * Time: 13:48
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\goods;


use app\core\response\ApiCode;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;

class GoodsListForm extends Model
{
    public $goodsId;
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['goodsId', 'page', 'limit'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['limit'], 'default', 'value' => 4],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $goods = Goods::findOne([
                'id' => $this->goodsId, 'status' => 1, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id
            ]);
            if (!$goods) {
                throw new \Exception('错误的商品');
            }
            // 部分插件的商品无特殊要求忽略判断
            $ignore = ['', 'booking', 'gift', 'integral_mall', 'mch', 'pond', 'scratch', 'step'];
            try {
                if (in_array($goods->sign, $ignore)) {
                    $list = $this->hasVideoGoodsList($goods);
                } else {
                    $plugin = \Yii::$app->plugin->getPlugin($goods->sign);
                    // 判断插件是否有hasVideoGoodsList这个方法，没有的则使用商城的
                    if (method_exists($plugin, 'hasVideoGoodsList')) {
                        $list = $plugin->hasVideoGoodsList($goods, $this->page, $this->limit);
                    } else {
                        $list = $this->hasVideoGoodsList($goods);
                    }
                }
            } catch (\Exception $exception) {
                \Yii::error($exception);
                $list = $this->hasVideoGoodsList($goods);
            }
            if ($this->page == 1) {
                array_unshift($list, $goods);
            }
            $newList = [];
            /* @var Goods[] $list */
            foreach ($list as $item) {
                $apiGoods = ApiGoods::getCommon();
                $apiGoods->hasMember = true;
                $apiGoods->goods = $item;
                $apiGoods->isSales = 0;
                $detail = $apiGoods->getDetail();
                $detail['app_share_title'] = $item->app_share_title;
                $detail['app_share_pic'] = $item->app_share_pic;
                $detail['activity_id'] = 0;
                try {
                    if ($item->sign != '') {
                        $plugin = \Yii::$app->plugin->getPlugin($item->sign);
                        if (method_exists($plugin, 'videoGoods')) {
                            $detail = array_merge($detail, $plugin->videoGoods($item, $detail));
                        }
                    }
                } catch (\Exception $exception) {
                }

                $newList[] = $detail;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $newList
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * @param Goods $goods
     * @return array|\yii\db\ActiveRecord[]|Goods[]
     */
    protected function hasVideoGoodsList($goods)
    {
        $list = Goods::find()->alias('g')->with(['goodsWarehouse', 'attr'])->where([
            'g.sign' => $goods->sign, 'g.is_delete' => 0, 'g.status' => 1, 'g.mall_id' => \Yii::$app->mall->id,
            'g.mch_id' => $goods->mch_id
        ])->andWhere(['!=', 'g.id', $goods->id])
            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id=g.goods_warehouse_id')
            ->andWhere(['!=', 'gw.video_url', ''])
            ->orderBy(['g.sort' => SORT_ASC, 'g.id' => SORT_DESC])
            ->groupBy('g.goods_warehouse_id')
            ->apiPage($this->limit, $this->page)
            ->all();
        return $list;
    }
}
