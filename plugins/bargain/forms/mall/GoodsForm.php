<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/13
 * Time: 17:17
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\models\Goods;
use app\models\Mall;
use app\models\Model;
use app\plugins\bargain\forms\common\goods\CommonBargainGoods;
use app\plugins\bargain\models\BargainGoods;

/**
 * @property Mall $mall
 */
class GoodsForm extends Model
{
    public $mall;
    public $id;
    public $status;
    public $sort;

    public function rules()
    {
        return [
            [['id', 'status', 'sort'], 'integer']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if (!$this->mall) {
            $this->mall = \Yii::$app->mall;
        }

        /* @var BargainGoods $bargain */
        $bargain = CommonBargainGoods::getCommonGoods($this->mall)->getGoods($this->id);
        if (!$bargain) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '错误的商品id'
            ];
        }
        $commonGoods = CommonGoods::getCommon();
        $detail = $commonGoods->getGoodsDetail($this->id);

        $statusData = \Yii::$app->serializer->decode($bargain->status_data);
        $list = [
            'goods_id' => $bargain->goods_id,
            'type' => $bargain->type,
            'bargain_time' => $bargain->time,
            'min_price' => floatval($bargain->min_price),
            'begin_time' => $bargain->begin_time,
            'end_time' => $bargain->end_time,
            'bargain_people' => $statusData->people,
            'bargain_human' => $statusData->human,
            'bargain_first_min_price' => $statusData->first_min_price,
            'bargain_first_max_price' => $statusData->first_max_price,
            'bargain_second_min_price' => $statusData->second_min_price,
            'bargain_second_max_price' => $statusData->second_max_price,
            'stock' => $bargain->stock,
            'stock_type' => $bargain->stock_type,
            'disabledBegin' => $detail['status'] == 1 && $bargain->begin_time <= mysql_timestamp(),
            'disabledEnd' => $detail['status'] == 1 && $bargain->end_time <= mysql_timestamp(),
        ];
        $detail['plugin'] = $list;
        $detail['select_attr_groups'] = $commonGoods->changeAttr((array)$detail['attr_groups']);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'detail' => $detail
            ]
        ];
    }

    public function switchStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $commonBargainGoods = CommonBargainGoods::getCommonGoods($this->mall);
            $commonBargainGoods->setSwitchStatus($this->id, $this->status);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function setSort()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $commonBargainGoods = CommonBargainGoods::getCommonGoods($this->mall);
            $commonBargainGoods->setSort($this->id, $this->sort);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
