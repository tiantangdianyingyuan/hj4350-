<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020-02-18
 * Time: 10:35
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\composition\forms\common\combination\FactoryCombination;
use app\plugins\composition\models\Composition;

class ListForm extends Model
{
    public $page;
    public $keyword;
    public $status;
    public $sort_prop;
    public $sort_type; // 0--倒序 1--升序
    public $date_start;
    public $date_end;
    public $type;

    public function rules()
    {
        return [
            [['keyword', 'sort_prop'], 'trim'],
            [['keyword', 'sort_prop'], 'string'],
            [['page', 'sort_type', 'status', 'type'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['status'], 'default', 'value' => -1],
            [['type'], 'default', 'value' => 0],
            [['date_start', 'date_end'], 'safe'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        switch ($this->status) {
            case 0:
                $statusCondition = ['status' => 0];
                break;
            case 1:
                $statusCondition = ['status' => 1];
                break;
            default:
                $statusCondition = [];
        }
        $list = Composition::find()
            ->where([
                'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0
            ])->keyword($this->keyword !== '', ['like', 'name', $this->keyword])
            ->keyword($this->date_start, ['>=', 'created_at', $this->date_start])
            ->keyword($this->date_end, ['<=', 'created_at', $this->date_end])
            ->keyword($this->type, ['type' => $this->type])
            ->keyword($this->status != -1, $statusCondition)
            ->with(['compositionGoods.goods.goodsWarehouse', 'compositionGoods.goods.attr'])
            ->page($pagination, 20, $this->page)
            ->orderBy(['sort' => $this->sort_type == 0 ? SORT_DESC : SORT_ASC, 'created_at' => SORT_DESC])
            ->all();
        $newList = [];
        /* @var Composition[] $list */
        foreach ($list as $composition) {
            $compositionClass = FactoryCombination::getCommon()->getCombination($composition->type);
            $compositionClass->composition = $composition;
            $goodsList = $compositionClass->getGoodsList($composition);
            $goodsList['min_price'] = price_format(floatval($goodsList['min_price']) + floatval($goodsList['host_price']));
            $goodsList['max_price'] = price_format(floatval($goodsList['max_price']) + floatval($goodsList['host_price']));
            $status = $composition->status;
            $status = $goodsList['flag'] ? 3 : $status; // 是否异常
            $newItem = [
                'id' => $composition->id,
                'name' => $composition->name,
                'sort' => $composition->sort,
                'status' => $status,
                'type' => $composition->type,
                'type_text' => $composition->typeText,
                'goods_count' => count($goodsList['goods_list']) + count($goodsList['host_list']),
                'price' => $composition->price,
                'created_at' => $composition->created_at,
            ];
            $newItem = array_merge($newItem, $goodsList);
            $newList[] = $newItem;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination
            ]
        ];
    }
}
