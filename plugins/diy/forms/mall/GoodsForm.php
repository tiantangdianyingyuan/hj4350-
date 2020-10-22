<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/10
 * Time: 17:24
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsList;
use app\models\Goods;
use app\models\Model;

class GoodsForm extends Model
{
    public $page;
    public $keyword;
    public $cat_id;
    public $mch_id;

    public function rules()
    {
        return [
            [['page', 'mch_id', 'cat_id'], 'integer'],
            [['keyword'], 'string'],
            [['keyword'], 'trim'],
            [['cat_id', 'mch_id'], 'default', 'value' => 0]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if (!($this->sign == '' || $this->sign == 'mch')) {
            try {
                $plugin = \Yii::$app->plugin->getPlugin($this->sign);
                if (!method_exists($plugin, 'getGoodsData')) {
                    throw new \Exception('没有这个getGoods这个函数');
                }
                $res = $plugin->getGoodsData($this->attributes);
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '',
                    'data' => $res
                ];
            } catch (\Exception $exception) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $exception->getMessage()
                ];
            }
        } else {
            $common = new CommonGoodsList();
            $common->attributes = $this->attributes;
            $common->relations = ['goodsWarehouse', 'mallGoods'];
            $common->status = 1;
            $common->sign = $this->sign ?: ['mch', ''];
            $common->mch_id = $this->mch_id;
            /* @var Goods[] $goodsList */
            $goodsList = $common->search();
            $newList = [];
            foreach ($goodsList as $goods) {
                $newItem = $common->getDiyBack($goods);
                if ($goods->mallGoods) {
                    $newItem = array_merge($newItem, [
                        'is_negotiable' => $goods->mallGoods->is_negotiable
                    ]);
                }
                $newList[] = $newItem;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $newList,
                    'pagination' => $common->pagination
                ]
            ];
        }
    }
}
