<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/10
 * Time: 16:35
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\ecard\forms\mall;


use app\plugins\ecard\forms\Model;
use app\models\Ecard;

class IndexForms extends Model
{
    public $keyword;
    public $page;

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['keyword'], 'string'],
            [['page'], 'integer'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $list = Ecard::find()->andWhere(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
            ->with('goodsWarehouse')
            ->keyword($this->keyword !== '', ['like', 'name', $this->keyword])
            ->page($pagination, 20, $this->page)
            ->orderBy(['id' => SORT_DESC])
            ->all();
        /* @var Ecard[] $list*/
        $newList = [];
        foreach ($list as $item) {
            $newList[] = [
                'id' => $item->id,
                'name' => $item->name,
                'created_at' => $item->created_at,
                'sales' => $item->sales,
                'stock' => $item->stock,
                'can_delete' => !$item->goodsWarehouse || empty($item->goodsWarehouse)
            ];
        }
        return $this->success([
            'list' => $newList,
            'pagination' => $pagination
        ]);
    }
}
