<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/3/13
 * Time: 14:51
 */

namespace app\plugins\region\forms\mall;

use app\core\response\ApiCode;
use app\models\DistrictArr;
use app\models\Model;
use app\plugins\region\models\RegionArea;
use app\plugins\region\models\RegionAreaDetail;
use app\plugins\region\models\RegionUser;
use yii\helpers\ArrayHelper;

class AreaForm extends Model
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [

        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = $this->where();

        $list = $query
            ->page($pagination)
            ->all();

        $newList = [];
        /** @var RegionArea $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            if ($item->become_type == 1 || $item->become_type == 4) {
                $newItem['province_condition'] = (int)$newItem['province_condition'];
                $newItem['city_condition'] = (int)$newItem['city_condition'];
                $newItem['district_condition'] = (int)$newItem['district_condition'];
            }
            $newItem['detail'] = ArrayHelper::toArray($item->areaDetail);
            foreach ($newItem['detail'] as &$v) {
                $v['name'] = DistrictArr::getDistrict($v['province_id'])['name'];
            }
            unset($v);
            $newList[] = $newItem;
        }

        $provinces = $this->getProvinces();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'provinces' => $provinces,
                'pagination' => $pagination
            ]
        ];
    }

    protected function where()
    {
        $query = RegionArea::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->with(
                [
                    'areaDetail' => function ($query) {
                        $query->andWhere(['is_delete' => 0]);
                    }
                ]
            );

        return $query;
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $model = RegionArea::find()
                ->select(
                    [
                        'name',
                        'province_rate',
                        'city_rate',
                        'district_rate',
                        'province_condition',
                        'city_condition',
                        'district_condition',
                        'become_type'
                    ]
                )
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $this->id])
                ->with(
                    [
                        'areaDetail' => function ($query) {
                            $query->andWhere(['is_delete' => 0]);
                        }
                    ]
                )
                ->one();

            if (!$model) {
                throw new \Exception('该记录不存在');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $model,
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function delete()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            $model = RegionArea::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $this->id])
                ->with(
                    [
                        'areaDetail' => function ($query) {
                            $query->andWhere(['is_delete' => 0]);
                        }
                    ]
                )
                ->one();

            if (!$model) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '数据不存在或已经删除',
                ];
            }

            if (!empty($model->areaDetail) && is_array($model->areaDetail)) {
                $provinceIds = array_column($model->areaDetail, 'province_id');
                $exists = RegionUser::find()
                    ->where(
                        [
                            'mall_id' => \Yii::$app->mall->id,
                            'is_delete' => 0,
                            'province_id' => $provinceIds,
                            'status' => [0, 1, 2]
                        ]
                    )
                    ->select(['province_id'])
                    ->distinct(true)
                    ->all();
                if ($exists) {
                    $waring = '';
                    foreach ($exists as $item) {
                        if ($waring) {
                            $waring .= "、" . DistrictArr::getDistrict($item->province_id)['name'];
                        } else {
                            $waring .= DistrictArr::getDistrict($item->province_id)['name'];
                        }
                    }
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => $waring . '尚有代理,不可删除区域组'
                    ];
                }
            }

            $model->is_delete = 1;
            $model->save();

            RegionAreaDetail::updateAll(
                ['is_delete' => 1],
                [
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'area_id' => $model->id
                ]
            );

            if ($model->save()) {
                $t->commit();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '删除成功'
                ];
            } else {
                $t->rollBack();
                return $this->getErrorResponse($model);
            }
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function getProvinces()
    {
        $provinces = RegionAreaDetail::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->select(['province_id'])
            ->distinct(true)
            ->asArray()
            ->all();

        foreach ($provinces as &$item) {
            $item['name'] = DistrictArr::getDistrict($item['province_id'])['name'];
        }
        unset($item);

        return $provinces;
    }
}
