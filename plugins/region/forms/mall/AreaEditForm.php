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

class AreaEditForm extends Model
{
    public $id;
    public $area_ids;
    public $name;
    public $province_rate;
    public $city_rate;
    public $district_rate;
    public $become_type;
    public $province_condition;
    public $city_condition;
    public $district_condition;

    public function rules()
    {
        return [
            [
                [
                    'area_ids',
                    'province_rate',
                    'city_rate',
                    'district_rate',
                    'become_type',
                    'province_condition',
                    'district_condition',
                    'city_condition',
                ],
                'required'
            ],
            [
                [
                    'province_rate',
                    'city_rate',
                    'district_rate',
                ],
                'number',
                'min' => 0,
                'max' => 100
            ],
            [
                [
                    'city_condition',
                    'district_condition',
                    'province_condition'
                ],
                'number',
                'min' => 0,
                'max' => 99999999
            ],
            [
                'district_condition',
                'compare',
                'compareAttribute' => 'city_condition',
                'operator' => '<',
                'message' => '区/县代理条件需小于市级代理'
            ],
            [
                'city_condition',
                'compare',
                'compareAttribute' => 'province_condition',
                'operator' => '<',
                'message' => '市级代理条件需小于省级代理'
            ],
            [['name'], 'string', 'max' => 100],
            [['id'], 'string'],
            [['area_ids'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'area_ids' => '区域',
            'name' => '名称',
            'province_rate' => '省代理分红比例',
            'city_rate' => '市代理分红比例',
            'district_rate' => '区/县代理分红比例',
            'province_condition' => '省代理条件',
            'district_condition' => '市代理条件',
            'city_condition' => '区/县代理条件',
            'become_type' => '成为代理条件'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            if ($this->id) {
                $area = RegionArea::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $this->id]);
                if (empty($area)) {
                    throw new \Exception('区域不存在');
                }
            } else {
                $area = new RegionArea();
                $area->mall_id = \Yii::$app->mall->id;
            }
            if (in_array($this->become_type, [1, 4])) {
                $this->province_condition = (int) $this->province_condition;
                $this->city_condition = (int) $this->city_condition;
                $this->district_condition = (int) $this->district_condition;
            }
            $area->name = $this->name ?? '';
            $area->province_rate = $this->province_rate;
            $area->city_rate = $this->city_rate;
            $area->district_rate = $this->district_rate;
            $area->province_condition = $this->province_condition;
            $area->city_condition = $this->city_condition;
            $area->district_condition = $this->district_condition;
            $area->become_type = $this->become_type;
            $area->save();
            $this->dealAreaIds();
            $this->check($area);
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '添加成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    private function check($area)
    {
        $detail = RegionAreaDetail::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'area_id' => $area->id])
            ->select(['province_id'])
            ->column();

        //新增的不能被其他区域拥有
        $insert = array_diff($this->area_ids, $detail);
        if ($insert) {
            foreach ($insert as $item) {
                if (DistrictArr::getDistrict($item)['level'] != 'province') {
                    throw new \Exception('省数据错误');
                }
            }
            $model = RegionAreaDetail::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'province_id' => $insert])
                ->andWhere(['!=', 'area_id', $area->id])
                ->one();

            if ($model) {
                $province = DistrictArr::getDistrict($model->province_id);
                throw new \Exception($province->name . '已被其他区域组拥有');
            }
        }

        $add = [];
        $mallId = \Yii::$app->mall->id;
        foreach ($insert as $v) {
            $add[] = [$mallId, $area->id, $v];
        }

        if ($add) {
            \Yii::$app->db->createCommand()->batchInsert(
                RegionAreaDetail::tableName(),
                ['mall_id', 'area_id', 'province_id'],
                $add
            )->execute();
        }

        //删除的不能包含代理
        $delete = array_diff($detail, $this->area_ids);
        if ($delete) {
            $model = RegionUser::find()
                ->where(
                    [
                        'mall_id' => \Yii::$app->mall->id,
                        'is_delete' => 0,
                        'province_id' => $delete,
                        'status' => [0, 1, 2]
                    ]
                )
                ->select(['province_id'])
                ->one();

            if ($model) {
                $province = DistrictArr::getDistrict($model->province_id);
                throw new \Exception($province->name . '中还存在代理，无法删除');
            }
        }

        RegionAreaDetail::updateAll(
            [
                'is_delete' => 1
            ],
            [
                'mall_id' => $mallId,
                'is_delete' => 0,
                'province_id' => $delete
            ]
        );
    }

    private function dealAreaIds()
    {
        //全国
        if ($this->area_ids[0] == 0) {
            $this->area_ids = [];
            $list = DistrictArr::getArr();
            $district = DistrictArr::getList($list, 1);
            foreach ($district as $item) {
                if ($item['id'] == 3268) {
                    continue;
                }
                $this->area_ids[] = $item['id'];
            }
        }
    }
}
