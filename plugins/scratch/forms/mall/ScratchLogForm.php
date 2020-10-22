<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\plugins\scratch\forms\mall;

use app\core\response\ApiCode;
use app\models\Goods;
use app\models\Model;
use app\plugins\scratch\forms\common\CommonScratch;
use app\plugins\scratch\models\ScratchLog;

class ScratchLogForm extends Model
{
    public $page;
    public $page_size;
    public $type;
    public $nickname;

    public function rules()
    {
        return [
            [['type'], 'in', 'range' => [1, 2, 3, 4]],
            [['nickname'], 'string'],
            [['nickname'], 'default', 'value' => ''],
            [['page'], 'default', 'value' => 1],
            [['page_size'], 'default', 'value' => 10],
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $list = ScratchLog::find()->alias('p')->where([
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
        ->andWhere(['not', 'p.status = 0'])
        ->keyword($this->type, ['p.type' => $this->type])
        ->page($pagination)
        ->orderBy('p.id DESC')
        ->asArray()
        ->all();

        array_walk($list, function (&$item) {
            $item['nickname'] = $item['user']['nickname'];
            $item['avatar'] = $item['user']['userInfo']['avatar'];
            $item['name'] = CommonScratch::getNewName($item, 'end');
            unset($item['user']);
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
