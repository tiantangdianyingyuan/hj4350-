<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\data_importing;


use app\forms\PickLinkForm;
use app\models\HomeNav;

class HomeNavImporting extends BaseImporting
{
    public function import()
    {
        try {
            foreach ($this->v3Data as $datum) {
                $res = PickLink::getNewLink($datum['url']);
                $model = new HomeNav();
                $model->mall_id = $this->mall->id;
                $model->name = $datum['name'];
                $model->url = $res['url'];
                $model->open_type = isset($res['data']['open_type']) ? $res['data']['open_type'] : PickLinkForm::OPEN_TYPE_2;
                $model->icon_url = $datum['pic_url'];
                $model->sort = $datum['sort'];
                $model->status = $datum['is_hide'];
                $model->params = \Yii::$app->serializer->encode(isset($res['data']['params']) ? $res['data']['params'] : []);
                $model->created_at = date('Y-m-d H:i:s', $datum['addtime']);
                $model->updated_at = date('Y-m-d H:i:s', $datum['addtime']);
                $model->is_delete = $datum['is_delete'];
                $res = $model->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($model));
                }
            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}