<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\plugins\pond\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\pond\forms\common\CommonPond;
use app\plugins\pond\models\PondLog;

class PondLogForm extends Model
{
    public $type;
    public $nickname;

    public function rules()
    {
        return [
            [['type'], 'in', 'range' => [1, 2, 3, 4, 5]],
            [['nickname'], 'string'],
            [['nickname'], 'default', 'value' => ''],
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $list = PondLog::find()->alias('p')->where([
                'p.mall_id' => \Yii::$app->mall->id
            ])
            ->with("coupon")
            ->with("goods.goodsWarehouse")
            ->joinWith(['user u' => function ($query) {
                $query->with('userInfo')->where([
                    'AND',
                    ['u.is_delete' => 0],
                    ['like', 'u.nickname', $this->nickname],
                    ['u.mall_id'  => \Yii::$app->mall->id],
                ]);
            }])
            ->keyword($this->type, ['p.type' => $this->type])
            ->page($pagination)
            ->orderBy('p.id DESC')
            ->asArray()
            ->all();

        array_walk($list, function (&$item) {
            $item['nickname'] = $item['user']['nickname'];
            $item['avatar'] = $item['user']['userInfo']['avatar'];
            $item['name'] = '';
            $item['name'] = CommonPond::getNewName($item, 'end');
        });
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}
