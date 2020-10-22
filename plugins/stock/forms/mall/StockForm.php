<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/3
 * Time: 16:22
 */

namespace app\plugins\stock\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\UserInfo;
use app\plugins\stock\forms\common\CommonStock;
use app\plugins\stock\forms\export\StockExport;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\StockUser;
use app\plugins\stock\models\StockUserInfo;

class StockForm extends Model
{
    public $keyword;
    public $search_type;
    public $status;
    public $platform;
    public $date_start;
    public $date_end;
    public $level_id;

    public $sort;
    public $page;

    public $fields;
    public $flag;

    public $user_id;
    public $remark;

    public $level;

    public function rules()
    {
        return [
            [['user_id',], 'required', 'on' => ['remark']],
            [['user_id',], 'required', 'on' => ['delete']],
            [['level', 'user_id'], 'required', 'on' => ['level']],
            [['date_start', 'date_end', 'keyword', 'status', 'platform', 'remark'], 'trim'],
            [['keyword', 'platform', 'flag', 'remark'], 'string'],
            [['search_type', 'status', 'page', 'level_id'], 'integer'],
            [['fields'], 'safe'],
            [['status'], 'default', 'value' => -1],
            [['remark'], 'default', 'value' => '', 'on' => ['remark']],
            [['sort'], 'default', 'value' => ['su.created_at' => SORT_DESC]],
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
            $exp = new StockExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $list = $query->select(['sui.*', 'su.*'])
            ->page($pagination)
            ->orderBy($this->sort)
            ->asArray()
            ->all();

        foreach ($list as &$v) {
            $v['all_bonus'] = price_format($v['all_bonus']);
            $v['total_bonus'] = price_format($v['total_bonus']);
            $v['level_name'] = $v['level']['name'];
            $v['nickname'] = $v['user']['nickname'];
            $v['avatar'] = $v['user']['userInfo']['avatar'];
            $v['mobile'] = $v['user']['mobile'];
            unset($v['level']);
            unset($v['user']);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
                'export_list' => (new StockExport())->fieldsList(),
                'level_list' => StockLevel::find()->select(['id', 'name'])->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->asArray()->all()
            ]
        ];
    }

    protected function where()
    {
        $query = StockUser::find()->alias('su')->where(['su.mall_id' => \Yii::$app->mall->id])
            ->leftJoin(['sui' => StockUserInfo::tableName()], 'sui.user_id = su.user_id')
            ->andWhere(['su.is_delete' => 0])->andWhere(['not in', 'su.status', [CommonStock::STATUS_REMOVE, CommonStock::STATUS_REAPPLYING]])
            ->joinWith(['user u' => function ($query) {
                if ($this->keyword && $this->search_type == 1) {
                    $query->andWhere(['like', 'u.nickname', $this->keyword]);
                } elseif ($this->keyword && $this->search_type == 4) {
                    $query->andWhere(['u.id' => $this->keyword]);
                }
            }, 'user.userInfo'])->joinWith(['level l' => function ($query) {
                if ($this->level_id) {
                    $query->andWhere(['l.id' => $this->level_id]);
                }
            }]);

        $query
            ->keyword($this->status == 0, [
                'AND',
                ['su.is_delete' => 0],
                ['su.status' => 0]
            ])->keyword($this->status == 1, [
                'AND', ['su.is_delete' => 0],
                ['su.status' => 1]
            ])->keyword($this->status == 2, [
                'AND', ['su.is_delete' => 0],
                ['su.status' => 2]
            ]);

        if ($this->date_start) {
            $query->andWhere(['>=', 'su.applyed_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'su.applyed_at', $this->date_end]);
        }

        if ($this->keyword) {
            switch ($this->search_type) {
                case 2:
                    $query->andWhere([
                        'or',
                        ['like', 'sui.name', $this->keyword],
                    ]);
                    break;
                case 3:
                    $query->andWhere([
                        'sui.phone' => $this->keyword
                    ]);
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

        $captain = StockUserInfo::findOne(['user_id' => $this->user_id]);

        if (!$captain) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '股东不存在'
            ];
        }
        $captain->remark = $this->remark;
        if ($captain->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($captain);
        }
    }

    public function delete()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $captain = StockUser::findOne(['user_id' => $this->user_id]);

        if (!$captain) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '股东不存在'
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
        $count = StockUser::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'status' => 0,
        ])->count();

        return $count;
    }
}
