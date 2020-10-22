<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/18
 * Time: 17:56
 * @copyright: ©2019 .浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\diy\models\CoreTemplateType;
use app\plugins\diy\models\CloudTemplate;
use yii\helpers\ArrayHelper;

class SyncTemplateTypeForm extends Model
{
    public function sync()
    {
        // $time = 3600;
        // $cacheKey = 'TEMPLATE_TYPE_SYNC_TIME';
        // $lastSyncTime = \Yii::$app->cache->get($cacheKey);
        // if ($lastSyncTime && (time() - $lastSyncTime) < $time) {
        //     return [
        //         'code' => ApiCode::CODE_SUCCESS,
        //         'msg' => 'synced recently, do nothing.',
        //     ];
        // }
        // $list = CloudTemplate::find()->where(['>', 'id', 0])->orderBy(['id' => SORT_DESC])->page($res['pagination'], 16, $this->page)->all();
        // $list = ArrayHelper::toArray($list);
        // // $res = \Yii::$app->cloud->template->getList([
        // //     'dont_validate_domain' => 1,
        // //     'page_size' => 100
        // // ]);
        // foreach ($list['list'] as $item) {
        //     $model = CoreTemplateType::findOne([
        //         'template_id' => $item['id'],
        //         'type' => 'page',
        //     ]);
        //     if (!$model) {
        //         $model = new CoreTemplateType();
        //         $model->template_id = $item['id'];
        //         $model->type = 'page';
        //         $model->is_delete = 0;
        //         $model->save();
        //     }
        //     if (!in_array($item['id'], [41, 42])) {
        //         $model = CoreTemplateType::findOne([
        //             'template_id' => $item['id'],
        //             'type' => 'module',
        //         ]);
        //         if (!$model) {
        //             $model = new CoreTemplateType();
        //             $model->template_id = $item['id'];
        //             $model->type = 'module';
        //             $model->is_delete = 0;
        //             $model->save();
        //         }
        //     }
        // }
        // \Yii::$app->cache->set($cacheKey, time(), $time);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => 'sync ok.',
        ];
    }
}
