<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\data_importing;

use app\forms\PickLinkForm;
use app\models\HomeBlock;

class HomeBlockImporting extends BaseImporting
{
    public function import()
    {
        try {
            foreach ($this->v3Data as $datum) {
                $model = new HomeBlock();
                $model->mall_id = $this->mall->id;
                $model->name = $datum['name'];
                $model->type = (int)$datum['style'] + 1;
                $model->created_at = date('Y-m-d H:i:s', $datum['addtime']);
                $model->updated_at = date('Y-m-d H:i:s', $datum['addtime']);
                $list = \Yii::$app->serializer->decode($datum['data']);
                $newValue = [];
                foreach ($list['pic_list'] as $item) {
                    $pickLink = PickLink::getNewLink($item['url']);
                    $arr = [];
                    $arr['link_url'] = $pickLink['url'];
                    $arr['pic_url'] = $item['pic_url'];
                    $arr['link'] = [
                        'new_link_url' => $pickLink['url'],
                        'open_type' => isset($pickLink['data']['open_type']) ? $pickLink['data']['open_type'] : PickLinkForm::OPEN_TYPE_2,
                    ];
                    if (isset($pickLink['data']['params'])) {
                        $arr['link']['params'] = $pickLink['data']['params'];
                    }
                    $newValue[] = $arr;
                }
                $model->value = \Yii::$app->serializer->encode($newValue);
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