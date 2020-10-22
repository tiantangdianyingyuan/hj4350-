<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\goods;


use app\models\GoodsCats;
use app\models\Model;

class CommonGoodsCats extends Model
{
    /**
     * 搜索商品分类
     * @param string $keyword
     * @param int $limit
     * @return array
     */
    public static function searchCat(string $keyword = '', int $limit = 20)
    {
        $keyword = trim($keyword);

        $query = GoodsCats::find()->where([
            'AND',
            ['LIKE', 'name', $keyword],
            ['mall_id' => \Yii::$app->mall->id],
        ]);

        $list = $query->select('id,pic_url,name')->orderBy('name')->limit($limit)->asArray()->all();
        return [
            'list' => $list,
        ];
    }

    /**
     * 获取所有一级分类
     * @return array
     */
    public static function allParentCat()
    {
        $list = GoodsCats::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'parent_id' => 0,
            'is_delete' => 0,
            'mch_id' => 0
        ])->asArray()->all();

        return [
            'list' => $list
        ];
    }
}
