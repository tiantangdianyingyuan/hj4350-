<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\data_importing;

use app\forms\PickLinkForm;
use app\models\MallMemberRights;
use app\models\MallMembers;
use app\models\Model;

class MemberImporting extends BaseImporting
{
    public function import()
    {
        try {
            foreach ($this->v3Data as $datum) {
                $level = (int)$datum['level'] + 1;
                $model = new MallMembers();
                $model->mall_id = $this->mall->id;
                $model->level = $level <= 100 ? $level : 100;
                $model->name = $datum['name'];
                $model->money = $datum['money'];
                $model->price = $datum['price'] ? $datum['price'] : 0;
                $model->discount = $datum['discount'];
                $model->is_purchase = $datum['price'] && $datum['price'] > 0 ? 1 : 0;
                $model->auto_update = $datum['money'] > 0 ? 1 : 0;
                $model->status = $datum['status'];
                $model->pic_url = $datum['image'] ?: '';
                $model->bg_pic_url = $datum['image'] ?: '';
                $model->rules = $datum['detail'] ?: '';
                $model->created_at = date('Y-m-d H:i:s', $datum['addtime']);
                $model->updated_at = date('Y-m-d H:i:s', $datum['addtime']);
                $res = $model->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($model));
                }

                $list = $datum['synopsis'] ? \Yii::$app->serializer->decode( $datum['synopsis']) : [];
                foreach ($list as $item) {
                    $rights = new  MallMemberRights();
                    $rights->member_id = $model->id;
                    $rights->title = $item['title'];
                    $rights->content = $item['content'];
                    $rights->pic_url = $item['pic'] ?: '';
                    $res = $rights->save();
                    if (!$res) {
                        throw new \Exception($this->getErrorMsg($rights));
                    }
                }
            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}