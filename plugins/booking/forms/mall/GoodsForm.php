<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/13
 * Time: 17:17
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\booking\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsList;
use app\models\Model;
use app\models\Store;
use app\plugins\booking\forms\common\CommonBookingGoods;
use app\plugins\booking\models\BookingStore;

/**
 * @property
 */
class GoodsForm extends Model
{
    public $id;
    public $page; 
    public $limit;
    public $keyword;
    public $search;
    public $sort;

    public function rules()
    {
        return [
            [['id', 'sort'], 'integer'],
            [['page', 'limit'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['limit'], 'default', 'value' => 20],
            [['keyword'], 'string'],
            [['keyword'], 'default', 'value' => ''],
            [['search'], 'safe'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $search = \Yii::$app->serializer->decode($this->search) ?? null;
        $form = new CommonGoodsList();
        $form->model = 'app\plugins\booking\models\Goods';
        $form->is_array = 1;
        $form->page = $this->page;
        $form->keyword = $search['keyword'];
        $form->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
        $form->relations = ['goodsWarehouse.cats', 'bookingGoods', 'attr'];

        if (array_key_exists('sort_prop', $search) && $search['sort_prop']) {
            $form->sort = 6;
            $form->sort_prop = $search['sort_prop'];
            $form->sort_type = $search['sort_type'];
        } else {
            $form->sort = 2;
        }

        if (array_key_exists('status', $search) && $search['status'] != -1) {
            if ($search['status'] == 0 || $search['status'] == 1) {
                $form->status = $search['status'];
            } else if ($search['status'] == 2) {
                $form->is_sold_out = 1;
            }
        }
        $list = $form->search();
        foreach($list as $k => $v) {
            $num_count = 0;
            foreach($v['attr'] as $v1) {
                $num_count += $v1['stock'];
            }
            $list[$k]['num_count'] = $num_count;
            $list[$k]['status'] = (int)$v['status'];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $form->pagination,
            ]
        ];
    }
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $booking = CommonBookingGoods::getGoods($this->id);
        if (!$booking) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '错误的商品id'
            ];
        }

        $commonGoods = CommonGoods::getCommon();
        $detail = $commonGoods->getGoodsDetail($this->id);

        $storeList = BookingStore::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'goods_id' => $this->id,
            'is_delete' => 0
        ])->with('store')->asArray()->all();
        $store = array_map(function ($item) {
            return $item['store'];
        }, $storeList);

        $list = [
            'goods_id' => $booking['goods_id'],
            'is_order_form' => $booking['is_order_form'],
            'order_form_type' => $booking['order_form_type'],
            'form_data' => \Yii::$app->serializer->decode($booking['form_data']),
            'store' => $store,
        ];

        $detail['plugin'] = $list;
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'detail' => $detail,
            ]
        ];
    }

    // 商品编辑门店列表
    public function storeSearch()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $storeList = Store::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'mch_id' => 0])
            ->andWhere(['like', 'name', $this->keyword])
            ->page($pagination)
            ->all();
        $newList = [];
        /** @var Store $item */
        foreach ($storeList as $item) {
            $newItem = [];
            $newItem['id'] = $item->id;
            $newItem['name'] = $item->name;
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList,
                'pagination' => $pagination
            ],
        ];
    }
}
