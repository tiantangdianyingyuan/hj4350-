<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\common;


use app\models\Model;
use app\plugins\mch\models\MchCommonCat;

class CommonCat extends Model
{
    public $page;
    public $keyword;

    public function getList()
    {
        $query = MchCommonCat::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);

        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }

        $list = $query->orderBy(['sort' => SORT_ASC])->page($pagination)
            ->asArray()
            ->all();

        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }

    public function getAllList()
    {
        $all = MchCommonCat::find()->where([
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'status' => 1,
        ])->orderBy(['sort' => SORT_ASC])->asArray()->all();

        return $all;
    }
}
