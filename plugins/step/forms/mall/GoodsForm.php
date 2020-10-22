<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/13
 * Time: 17:17
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\step\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsList;
use app\models\Mall;
use app\models\Model;
use app\plugins\step\forms\common\CommonStepGoods;

/**
 * @property Mall $mall
 */
class GoodsForm extends Model
{
    public $id;
    public $page;
    public $limit;
    public $search;
    public $sort;

    public function rules()
    {
        return [
            [['page', 'limit', 'id', 'sort'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['limit'], 'default', 'value' => 20],
            [['search'], 'trim']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $search = \Yii::$app->serializer->decode($this->search) ?? null;

        $form = new CommonGoodsList();
        $form->model = 'app\plugins\step\models\Goods';
        $form->is_array = 1;
        $form->page = $this->page;
        $form->keyword = $search['keyword'];
        $form->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
        $form->relations = ['goodsWarehouse.cats', 'stepGoods', 'attr.stepGoods'];
        $form->sort = 2;
        if (array_key_exists('status', $search) && $search['status'] != -1) {
            if ($search['status'] == 0 || $search['status'] == 1) {
                $form->status = $search['status'];
            } else if ($search['status'] == 2) {
                $form->is_sold_out = 1;
            }
        }

        $list = $form->search();
        //格式化
        foreach($list as $key => $item) {
            $num_count = 0;
            foreach($item['attr'] as $key2 => $item2) {
                $list[$key]['attr'][$key2]['step_currency'] = $item2['stepGoods']['currency'];
                $num_count += $item2['stock'];
            }
            $list[$key]['num_count'] = $num_count;
            $list[$key]['status'] = (int)$item['status'];
            $list[$key]['cats'] = $item['goodsWarehouse']['cats'];
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

    public function editSort()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {

            $form = CommonStepGoods::getGoods($this->id);
            if(!$form) {
                throw new \Exception('数据为空');
            }
            $form->goods->sort = $this->sort;
            $form->goods->save();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        }catch(\Exception $e) {
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

        $form = CommonStepGoods::getGoods($this->id);
        if(!$form) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据为空'
            ];
        }

        $commonGoods = CommonGoods::getCommon();
        $detail = $commonGoods->getGoodsDetail($this->id);
        $detail = CommonStepGoods::getDetail($detail);

        $detail['step_currency'] = $form['currency'];
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'detail' => $detail,
            ]
        ];
    }
}
