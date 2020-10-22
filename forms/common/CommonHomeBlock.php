<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common;


use app\models\HomeBlock;

class CommonHomeBlock
{
    /**
     * 获取所有图片魔方
     * @return array
     */
    public static function getAll()
    {
        $list = HomeBlock::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->asArray()->all();


        return [
            'list' => $list,
        ];
    }
}
