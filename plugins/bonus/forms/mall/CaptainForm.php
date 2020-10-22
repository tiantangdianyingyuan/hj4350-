<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/3
 * Time: 16:22
 */

namespace app\plugins\bonus\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\UserInfo;
use app\plugins\bonus\forms\common\CommonCaptain;
use app\plugins\bonus\forms\export\CaptainExport;
use app\plugins\bonus\models\BonusCaptain;
use app\plugins\bonus\models\BonusMembers;

class CaptainForm extends Model
{
    public $keyword;
    public $search_type;
    public $status;
    public $platform;
    public $date_start;
    public $date_end;

    public $limit = 10;
    public $page = 1;
    public $sort;

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
            [['search_type', 'status', 'limit', 'page'], 'integer'],
            [['fields'], 'safe'],
            [['status'], 'default', 'value' => -1],
            [['remark'], 'default', 'value' => '', 'on' => ['remark']],
            [['sort'], 'default', 'value' => ['b.status' => SORT_ASC, 'b.created_at' => SORT_DESC]],
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
        $pagination = null;

        $query = $this->where();

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new CaptainExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $list = $query->select(['b.*', 'i.avatar'])->asArray()->page($pagination, $this->limit, $this->page)
            ->orderBy($this->sort)->all();

        foreach ($list as &$v) {
            $v['all_bonus'] = price_format($v['all_bonus']);
            $v['total_bonus'] = price_format($v['total_bonus']);
        }
        unset($v);

        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
                'export_list' => (new CaptainExport())->fieldsList()
            ]
        ];
    }

    protected function where()
    {
        $query = BonusCaptain::find()->alias('b')
            ->where(['b.is_delete' => 0, 'b.mall_id' => \Yii::$app->mall->id])
            ->andWhere(['!=', 'b.status', -1])
            ->joinWith(['user u' => function ($query) {
                $query->select(['nickname', 'id', 'username', 'mobile', 'mall_id']);
                if ($this->keyword && $this->search_type == 1) {
                    $query->andWhere(['like', 'nickname', $this->keyword]);
                } elseif ($this->keyword && $this->search_type == 4) {
                    $query->andWhere(['u.id' => $this->keyword]);
                }
            }])->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = u.id')
            ->with(['level']);

        $query->keyword($this->status == 1, ['AND', ['b.is_delete' => 0], ['status' => 1]])
            ->keyword($this->status == 0, [
                'AND',
                ['b.is_delete' => 0],
                ['status' => 0]
            ]);

        if ($this->date_start) {
            $query->andWhere(['>=', 'b.created_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'b.created_at', $this->date_end]);
        }

        if ($this->keyword) {
            switch ($this->search_type) {
                case 2:
                    $query->andWhere([
                        'or',
                        ['like', 'b.name', $this->keyword],
                    ]);
                    break;
                case 3:
                    $query->andWhere([
                        'b.mobile' => $this->keyword
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

        $captain = BonusCaptain::findOne(['user_id' => $this->user_id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);

        if (!$captain) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '队长不存在'
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

    public function level()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $captain = BonusCaptain::findOne(['user_id' => $this->user_id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);

        if (!$captain) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '队长不存在'
            ];
        }

        if ($this->level != 0) {
            $level = BonusMembers::findOne(['id' => $this->level, 'is_delete'=>0,'status'=>1,'mall_id' => \Yii::$app->mall->id]);
            if (!$level) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '该等级记录不存在'
                ];
            }
        }

        $captain->level = $this->level;
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
        $captain = BonusCaptain::findOne(['user_id' => $this->user_id, 'status' => CommonCaptain::STATUS_REJECT, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);

        if (!$captain) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '队长不存在'
            ];
        }
        $captain->is_delete = 1;
        if ($captain->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($captain);
        }
    }

    public function getCount()
    {
        $count = BonusCaptain::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'status' => 0,
        ])->count();

        return $count;
    }
}
