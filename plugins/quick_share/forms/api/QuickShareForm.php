<?php


namespace app\plugins\quick_share\forms\api;

use app\core\response\ApiCode;
use app\models\Goods;
use app\models\Model;
use app\plugins\quick_share\forms\common\CommonGoods;

class QuickShareForm extends Model
{
    public $type;
    public $sort;

    public function rules()
    {
        return [
            [['type'], 'string'],
            [['sort'], 'integer'],
            [['sort'], 'default', 'value' => '0'],
            [['type'], 'default', 'value' => 'goods'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if ($this->type !== 'goods') {
            $this->sort = 3;
        }
        $data = (new CommonGoods)->getGoodsList(
            array_merge($this->attributes, ['status' => 1, 'is_app' => 1, 'limit' => 10])
        );
        list($list) = $data;
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list
            ]
        ];
    }
}