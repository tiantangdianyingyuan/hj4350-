<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\common;


use app\models\Mall;
use app\models\Model;
use app\plugins\pintuan\models\PintuanBanners;

/**
 * @property Mall $mall
 */
class BannerListForm extends Model
{
    public $mall;

    public function search()
    {
        if (!$this->mall) {
            $this->mall = \Yii::$app->mall;
        }
        $query = PintuanBanners::find()->where([
            'mall_id' => $this->mall->id,
            'is_delete' => 0,
        ]);

        $list = $query->with('banner')
            ->orderBy('id ASC')
            ->asArray()
            ->all();

        $list = array_map(function ($item) {
            return $item['banner'];
        }, $list);


        return $list;
    }
}
