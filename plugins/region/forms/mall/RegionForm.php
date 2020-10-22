<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/3
 * Time: 16:22
 */

namespace app\plugins\region\forms\mall;

use app\core\response\ApiCode;
use app\models\DistrictArr;
use app\models\Model;
use app\plugins\region\forms\common\CommonRegion;
use app\plugins\region\forms\export\RegionExport;
use app\plugins\region\models\RegionUser;
use app\plugins\region\models\RegionUserInfo;
use yii\helpers\ArrayHelper;

class RegionForm extends Model
{
    public $keyword;
    public $search_type;
    public $status;
    public $platform;
    public $date_start;
    public $date_end;
    public $level;
    public $province_id;

    public $sort;
    public $page;

    public $fields;
    public $flag;

    public $user_id;
    public $remark;

    public function rules()
    {
        return [
            [['user_id',], 'required', 'on' => ['remark']],
            [['user_id',], 'required', 'on' => ['delete']],
            [['user_id'], 'required', 'on' => ['level']],
            [['date_start', 'date_end', 'keyword', 'status', 'platform', 'remark'], 'trim'],
            [['keyword', 'platform', 'flag', 'remark'], 'string'],
            [['search_type', 'status', 'page', 'level', 'province_id'], 'integer'],
            [['fields'], 'safe'],
            [['status'], 'default', 'value' => -1],
            [['remark'], 'default', 'value' => '', 'on' => ['remark']],
            [['sort'], 'default', 'value' => ['ru.created_at' => SORT_DESC]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_id' => '用户id',
            'remark' => '备注',
            'search_type' => '搜索类型',
            'keyword' => '关键词',
            'level' => '等级',
            'province_id' => '代理区域'
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['remark'] = ['user_id', 'remark'];
        $scenarios['delete'] = ['user_id'];
        return $scenarios;
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = $this->where();

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new RegionExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $list = $query
            ->page($pagination)
            ->orderBy($this->sort)
            ->all();

        $common = CommonRegion::getInstance();

        $newList = [];
        /** @var RegionUser $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['regionInfo'] = ArrayHelper::toArray($item->regionInfo);
            $newItem['regionInfo']['out_bonus'] = $common->getPrice($item->user_id)['cash_bonus'];
            $newItem['user'] = ArrayHelper::toArray($item->user);
            $newItem['user']['userInfo'] = ArrayHelper::toArray($item->user->userInfo);
            $newItem['relation'] = ArrayHelper::toArray($item->regionRelation);
            $province = $newItem['attr'] = DistrictArr::getDistrict($item->province_id)['name'];
            if (!empty($item->levelUp) && $item->status == RegionUser::STATUS_BECOME) {
                $newItem['level_up'] = ArrayHelper::toArray($item->levelUp);
                $newItem['level_up']['level_desc'] = $common->parseLevel($item->levelUp->level);
                $newItem['level_up']['relation'] = ArrayHelper::toArray($item->levelUp->regionRelation);
                foreach ($newItem['level_up']['relation'] as &$v) {
                    $v['name'] = DistrictArr::getDistrict($v['district_id'])['name'];
                }
                unset($v);
            }
            $newItem['level_desc'] = $common->parseLevel($item->level);
            if ($item->level == 3) {
                $parent_id = DistrictArr::getDistrict($item->regionRelation[0]->district_id)['parent_id'];
                $newItem['attr'] = $province . DistrictArr::getDistrict($parent_id)['name'];
                $newItem['city_id'] = $parent_id;
            } else {
                $newItem['attr'] = $province;
            }
            foreach ($newItem['relation'] as &$v) {
                $v['name'] = DistrictArr::getDistrict($v['district_id'])['name'];
            }
            unset($v);
            $newList[] = $newItem;
            unset($newItem);
        }

        $provinceList = [];
        $model = RegionUser::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'status' => [0, 1, 2]])
            ->select(['province_id'])
            ->distinct()
            ->column();
        foreach ($model as $item) {
            $newItem['id'] = $item;
            $newItem['province'] = DistrictArr::getDistrict($item)['name'];
            $provinceList[] = $newItem;
        }

        $area = new AreaForm();
        $allow = $area->getProvinces();
        $arr = DistrictArr::getArr();
        $districtArr = DistrictArr::getList($arr, null);
        $newDistrictArr = [];
        foreach ($allow as $item) {
            foreach ($districtArr as $item1) {
                if ($item1['id'] == $item['province_id']) {
                    $newDistrictArr[] = $item1;
                }
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'province' => $provinceList,
                'allow_province' => $newDistrictArr,
                'pagination' => $pagination,
                'export_list' => (new RegionExport())->fieldsList(),
            ]
        ];
    }

    protected function where()
    {
        $query = RegionUser::find()->alias('ru')->where(['ru.mall_id' => \Yii::$app->mall->id])
            ->joinWith(['regionInfo rui'])
            ->andWhere(['ru.is_delete' => 0])->andWhere(
                [
                    'not in',
                    'ru.status',
                    [RegionUser::STATUS_REMOVE, RegionUser::STATUS_REAPPLYING]
                ]
            )
            ->joinWith(
                [
                    'user u' => function ($query) {
                        if ($this->keyword && $this->search_type == 1) {
                            $query->andWhere(['like', 'u.nickname', $this->keyword]);
                        } elseif ($this->keyword && $this->search_type == 4) {
                            $query->andWhere(['u.id' => $this->keyword]);
                        }
                    },
                    'user.userInfo'
                ]
            )->with(
                [
                    'regionRelation' => function ($query) {
                        $query->andWhere(['is_delete' => 0, 'is_update' => 0]);
                    }
                ]
            )->with(
                [
                    'levelUp' => function ($query) {
                        $query->andWhere(['is_delete' => 0, 'status' => 0]);
                    }
                ]
            )->with(
                [
                    'levelUp.regionRelation' => function ($query) {
                        $query->andWhere(['is_delete' => 0]);
                    }
                ]
            );

        $query
            ->keyword(
                $this->status == 0,
                [
                    'AND',
                    ['ru.is_delete' => 0],
                    ['ru.status' => 0]
                ]
            )->keyword(
                $this->status == 1,
                [
                    'AND',
                    ['ru.is_delete' => 0],
                    ['ru.status' => 1]
                ]
            )->keyword(
                $this->status == 2,
                [
                    'AND',
                    ['ru.is_delete' => 0],
                    ['ru.status' => 2]
                ]
            )->keyword($this->level, ['ru.level' => $this->level])
            ->keyword($this->province_id, ['ru.province_id' => $this->province_id]);

        if ($this->date_start) {
            $query->andWhere(['>=', 'ru.applyed_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'ru.applyed_at', $this->date_end]);
        }

        if ($this->keyword) {
            switch ($this->search_type) {
                case 2:
                    $query->andWhere(
                        [
                            'or',
                            ['like', 'rui.name', $this->keyword],
                        ]
                    );
                    break;
                case 3:
                    $query->andWhere(
                        [
                            'rui.phone' => $this->keyword
                        ]
                    );
                    break;

                default:
            }
        }

        return $query;
    }

    public function remark()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $region = RegionUserInfo::findOne(['user_id' => $this->user_id]);

        if (!$region) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '代理不存在'
            ];
        }
        $region->remark = $this->remark;
        if ($region->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($region);
        }
    }

    public function delete()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $captain = RegionUser::findOne(['user_id' => $this->user_id]);

        if (!$captain) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '代理不存在'
            ];
        }
        $captain->is_delete = 1;
        if ($captain->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        } else {
            return $this->getErrorResponse($captain);
        }
    }

    public function getCount()
    {
        $count = RegionUser::find()->where(
            [
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'status' => 0,
            ]
        )->count();

        return $count;
    }
}
