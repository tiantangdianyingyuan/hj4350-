<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\common\v2;


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
        $list = PintuanBanners::find()
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0])
            ->with('banner')
            ->orderBy('id ASC')
            ->all();

        $list = array_map(function ($item) {
            /** @var PintuanBanners $item */
            return [
                'id' => $item->banner->id,
                'pic_url' => $item->banner->pic_url,
                'title' => $item->banner->title,
                'page_url' => $item->banner->page_url,
                'open_type' => $item->banner->open_type,
                'params' => \Yii::$app->serializer->decode($item->banner->params),
            ];
        }, $list);


        return $list;
    }
}
