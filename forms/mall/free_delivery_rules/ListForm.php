<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 11:38
 */

namespace app\forms\mall\free_delivery_rules;


use app\core\response\ApiCode;
use app\models\FreeDeliveryRules;
use app\models\Model;
use yii\helpers\ArrayHelper;

class ListForm extends Model
{
    public $limit;
    public $page;
    public $keyword;
    public $mch_id;

    private $rule = [
        1 => '订单满额包邮',
        2 => '订单满件包邮',
        3 => '单商品满额包邮',
        4 => '单商品满件包邮',
    ];

    public function rules()
    {
        return [
            [['limit', 'page', 'mch_id'], 'integer'],
            ['limit', 'default', 'value' => 20],
            ['page', 'default', 'value' => 1],
            [['keyword'], 'string'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        /* @var $pagination \app\core\Pagination */
        $pagination = null;
        $query = FreeDeliveryRules::find()->where([
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
        ]);
        if ($this->keyword || $this->keyword == 0) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }
        $list = $query
            ->select(['id', 'name', 'type', 'status'])
            ->page($pagination, $this->limit, $this->page)
            ->all();

        $newList = [];
        foreach ($list as $item) {
            /**@var FreeDeliveryRules $item**/
            $newItem = ArrayHelper::toArray($item);
            $newItem['type_text'] = $this->rule[$item->type];
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => 'success',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
            ]
        ];
    }

    public function allList()
    {
        $allList = FreeDeliveryRules::find()->where([
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $this->mch_id ?: \Yii::$app->user->identity->mch_id,
        ])->select(['id', 'name', 'status', 'type'])->all();

        $default = '';
        $newList = [];
        foreach ($allList as $item) {
            /**@var FreeDeliveryRules $item**/
            if ($item['status'] == 1) {
                $default = $item->getTypeText();
            }
            $newItem = ArrayHelper::toArray($item);
            $newItem['name'] = $item->name;
            $newItem['text'] = $item->getTypeText();
            $newList[] = $newItem;
        }

        array_unshift($newList, [
            'id' => 0,
            'name' => '默认包邮规则',
            'status' => 0,
            'text' => $default
        ]);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList
            ]
        ];
    }
}
