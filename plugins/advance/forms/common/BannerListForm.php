<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/10/10
 * Time: 14:37
 */

namespace app\plugins\advance\forms\common;

use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\plugins\advance\models\AdvanceBanner;

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
        $query = AdvanceBanner::find()->where([
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
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ]
        ];
    }
}
