<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\home_page;

use app\models\Banner;
use app\models\MallBannerRelation;
use app\models\Model;

class HomeBannerForm extends Model
{
    public function getBanners()
    {
        $bannerIds = Banner::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->select('id');

        $query = MallBannerRelation::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        $list = $query
            ->andWhere(['banner_id' => $bannerIds])
            ->orderBy('id ASC')
            ->with('banner')
            ->all();

        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        $permissionFlip = array_flip($permission);
        $newList = [];
        /** @var MallBannerRelation $item */
        foreach ($list as $item) {
            if ($item->banner->sign && !isset($permissionFlip[$item->banner->sign])) {
                continue;
            }
            $arr = [
                'id' => $item->banner->id,
                'title' => $item->banner->title,
                'params' => $item->banner->params ? json_decode($item->banner->params, true) : '',
                'open_type' => $item->banner->open_type,
                'pic_url' => $item->banner->pic_url,
                'page_url' => $item->banner->page_url,
            ];
            $newList[] = $arr;
        }

        return $newList;
    }
}
