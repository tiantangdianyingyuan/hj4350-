<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\plugins\step\forms\mall;

use app\core\response\ApiCode;
use app\plugins\step\models\StepBannerRelation;
use app\models\Model;

class BannerForm extends Model
{
    public $ids;

    public function rules()
    {
        return [
            [['ids'], 'safe'],
            [['ids'], 'default', "value" => []]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = StepBannerRelation::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        $list = $query->with('banner')
            ->page($pagination, 20)
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
                'pagination' => $pagination
            ]
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $t = \Yii::$app->db->beginTransaction();

        StepBannerRelation::updateAll(['is_delete' => 1,'deleted_at' => date('Y-m-d H:i:s')], [
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);

        foreach ($this->ids as $id) {
            $form = new StepBannerRelation();
            $form->banner_id = $id;
            $form->mall_id = \Yii::$app->mall->id;
            $form->is_delete = 0;
            if (!$form->save()) {
                $t->rollBack();
                return $this->getErrorResponse($form);
            };
        };
        $t->commit();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功',
        ];
    }
}
