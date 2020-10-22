<?php

namespace app\forms\common\order;

use app\core\Pagination;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;

/**
 * @property Order $order
 * @property BaseActiveQuery $query
 * @property Pagination $pagination
 */
class CommonOrderDetail extends Model
{
    public $query;

    /* @var Order $model */
    public $model = 'app\\models\\Order';

    public $mall_id;
    public $mch_id;
    public $is_array;
    public $id;
    /**
     * 关联关系
     * @var
     */
    public $is_detail;
    public $is_goods;
    public $is_user;
    public $is_refund;
    public $is_store;
    public $relations = [];
    public $is_vip_card;

    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'is_detail', 'is_goods', 'is_user', 'is_refund', 'id', 'is_array', 'is_store', 'is_vip_card'], 'integer'],
        ];
    }

    /**
     * @param $key
     * @return mixed|null
     * 获取字段对应的设置sql方法
     */
    private function getMethod($key)
    {
        $array = [
            'mch_id' => 'setMchId',
            'is_detail' => 'setWithDetail',
            'is_goods' => 'setWithGoods',
            'is_user' => 'setWithUser',
            'is_refund' => 'setWithRefund',
            'is_store' => 'setWithStore',
            'relations' => 'setRelations',
            'is_vip_card' => 'setWithVipCard',
        ];
        return isset($array[$key]) ? $array[$key] : null;
    }

    //持续改进
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->query = $query = $this->model::find()->alias('o')->where([
                'o.mall_id' => \Yii::$app->mall->id,
                'o.is_delete' => 0,
                'o.id' => $this->id
            ]);
            foreach ($this->attributes as $key => $value) {
                $method = $this->getMethod($key);
                if ($method && method_exists($this, $method) && $value !== null) {
                    $this->$method();
                }
            }
            $order = $this->query->asArray($this->is_array)->one();

            return $order;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function setMchId()
    {
        $this->query->andWhere(['o.mch_id' => $this->mch_id]);
    }

    private function setWithStore()
    {
        $this->query->with('store');
    }

    private function setWithDetail()
    {
        $this->query->with('detail.order');
    }

    private function setWithUser()
    {
        $this->query->with('user');
    }

    private function setWithGoods()
    {
        $this->query->with('detail.goods.goodsWarehouse');
    }

    private function setWithRefund()
    {
        $this->query->with('detail.refund');
    }

    private function setRelations()
    {
        $this->query->with($this->relations);
    }

    private function setWithVipCard()
    {
        try {
            $plugin = \Yii::$app->plugin->getPlugin('vip_card');
            $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
            if (in_array('vip_card', $permission) && $plugin) {
                $this->query->with('vipCardDiscount');
            }
        } catch (\Exception $e) {
            //throw $e;
        }
    }
}
