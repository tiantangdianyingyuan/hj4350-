<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/21
 * Time: 10:35
 */

namespace app\forms\api;


use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsDetail;
use app\models\CityDeliverySetting;
use app\models\Goods;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall;
 * @property User $user;
 */
class GoodsForm extends Model
{
    public $id;
    public $mall;
    public $user;


    public function rules()
    {
        return [
            [['id'], 'integer'],
        ];
    }

    /**
     * @param $goods
     * @return array|null
     */
    public function shareQuick($goods, $sales)
    {
        $plugin = 'quick_share';
        if (\Yii::$app->plugin->getInstalledPlugin($plugin)) {
            return \app\plugins\quick_share\forms\common\CommonQuickShare::getExtraInfo($goods, $sales);
        } else {
            return null;
        }
    }

    private function setLog(Goods $goods)
    {
        $goods->detail_count += 1;
        $goods->save();
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $form = new CommonGoodsDetail();
            $form->user = \Yii::$app->user->identity;
            $form->mall = \Yii::$app->mall;
            $goods = $form->getGoods($this->id);
            if (!$goods) {
                throw new Exception('商品不存在');
            }
            if ($goods->status != 1) {
                throw new Exception('商品未上架');
            }

            $form->goods = $goods;
            $mallGoods = CommonGoods::getCommon()->getMallGoods($goods->id);
            $form->setMember($mallGoods->is_negotiable == 0);
            $form->setShare($mallGoods->is_negotiable == 0);
            $cats = array_column(ArrayHelper::toArray($goods->goodsWarehouse->cats), 'id');
            $cats = array_map(function ($v) {
                return (string)$v;
            }, $cats);
            $res = $form->getAll();
            $res = array_merge($res, [
                'extra_quick_share' => $this->shareQuick($goods, $res['sales']),
                'is_quick_shop' => $mallGoods->is_quick_shop,
                'is_sell_well' => $mallGoods->is_sell_well,
                'is_negotiable' => $mallGoods->is_negotiable,
                //商品分类
                'cats' => $cats
            ]);
            //图片替换
            $temp = [];
            foreach ($res['attr'] as $v) {
                foreach ($v['attr_list'] as $w) {
                    if (!isset($temp[$w['attr_id']])) {
                        $temp[$w['attr_id']] = $v['pic_url'];
                    }
                }
            }

            foreach ($res['attr_groups'] as $k => $v) {
                foreach ($v['attr_list'] as $l => $w) {
                    $res['attr_groups'][$k]['attr_list'][$l]['pic_url'] = $temp[$w['attr_id']] ?: "";
                }
            }

            $model = CityDeliverySetting::findOne(['key' => 'address', 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);
            $this->setLog($goods);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success',
                'data' => [
                    'goods' => $res,
                    'delivery' => !empty($model) ? $model->value : ''
                ]
            ];
        } catch (\Exception $e) {
            \Yii::error($e);
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'errors' => $e->getLine()
            ];
        }
    }
}
