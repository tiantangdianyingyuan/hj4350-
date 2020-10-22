<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/21
 * Time: 10:35
 */

namespace app\plugins\gift\forms\api;


use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\goods\CommonGoodsList;
use app\models\CityDeliverySetting;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\plugins\gift\forms\common\CommonGift;
use app\plugins\gift\Plugin;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall;
 * @property User $user;
 */
class GoodsForm extends Model
{
    public $id;
    public $sort;
    public $sort_type;
    public $keyword;
    public $page;

    public function rules()
    {
        return [
            [['page'], 'default', 'value' => 1],
            [['id'], 'integer'],
        ];
    }

    public function search()
    {
        try {
            $form = new CommonGoodsList();
            $form->sort = $this->sort;
            $form->status = 1;
            $form->sort_type = $this->sort_type;
            $form->keyword = $this->keyword;
            $form->page = $this->page;
            $form->mch_id = 0;
            $form->is_array = true;
            $form->sign = 'gift';
            $form->isSignCondition = true;
            $form->is_sales = (new Mall())->getMallSettingOne('is_sales');
            $form->relations = ['goodsWarehouse', 'mallGoods'];
            $list = $form->getList();
            $setting = CommonGift::getSetting();
            array_walk($list, function (&$item) use($setting) {
                $item['page_url'] = (new Plugin())->getGoodsUrl($item);
                $item['is_level'] = $setting['is_member_price'] ? $item['is_level'] : 0;
            });
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $form->pagination,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
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
            $setting = CommonGift::getSetting();
            $form->setShare($setting['is_share']);
            $form->goods = $goods;
//            $mallGoods = CommonGoods::getCommon()->getMallGoods($goods->id);
//            $form->setMember($mallGoods->is_negotiable == 0);
//            $form->setShare($mallGoods->is_negotiable == 0);
            $cats = array_column(ArrayHelper::toArray($goods->goodsWarehouse->cats), 'id');
            $cats = array_map(function ($v) {
                return (string)$v;
            }, $cats);
            $res = $form->getAll();
            $res = array_merge($res, [
                'extra_quick_share' => (new \app\forms\api\GoodsForm())->shareQuick($goods, null),
//                'is_quick_shop' => $mallGoods->is_quick_shop,
//                'is_sell_well' => $mallGoods->is_sell_well,
//                'is_negotiable' => $mallGoods->is_negotiable,
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
            $res['level_show'] = $setting['is_member_price'] ? $res['level_show'] : 0;

            $model = CityDeliverySetting::findOne(['key' => 'address', 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);
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
