<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\banner;

use app\core\response\ApiCode;
use app\models\Banner;
use app\models\MallBannerRelation;
use app\models\Model;
use yii\helpers\ArrayHelper;

class MallBannerForm extends Model
{
    public $page_size;
    public $ids;

    public function rules()
    {
        return [
            [['ids'], 'safe'],
            [['page_size'], 'default', 'value' => 10],
            [['ids'], 'default', "value" => []]
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'is_delete' => '删除',
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $bannerIds = Banner::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->select('id');
        $query = MallBannerRelation::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        $list = $query
            ->andWhere(['banner_id' => $bannerIds])
            ->with('banner')
            ->orderBy('id ASC')
            ->all();

        $list = array_map(function ($item) {
            $newItem = ArrayHelper::toArray($item->banner);
            try {
                $newItem['params'] = \Yii::$app->serializer->decode($item->banner->params);
                if (!$newItem['params']) {
                    $newItem['params'] = [];
                }
            } catch (\Exception $exception) {
                $newItem['params'] = [];
            }
            return $newItem;
        }, $list);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => []
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

        MallBannerRelation::updateAll(['is_delete' => 1, 'deleted_at' => date('Y-m-d H:i:s')], [
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);

        foreach ($this->ids as $id) {
            $form = new MallBannerRelation();
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
