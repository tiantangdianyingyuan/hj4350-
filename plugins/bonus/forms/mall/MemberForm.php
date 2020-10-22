<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/30
 * Time: 16:15
 */

namespace app\plugins\bonus\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\bonus\models\BonusCaptain;
use app\plugins\bonus\models\BonusMembers;

class MemberForm extends Model
{
    public $id;
    public $page;
    public $keyword;


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword'], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '等级ID',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = BonusMembers::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }

        $list = $query->page($pagination)->orderBy(['level' => SORT_ASC])->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    /**
     * 获取会员等级列表
     * @return array
     */
    public function getOptionList()
    {
        $list = BonusMembers::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->asArray()->all();

        $level = [];
        foreach ($list as $item) {
            $level[] = $item['level'];
        }

        $levelList = [];
        for ($i = 1; $i <= 100; $i++) {
            if (in_array($i, $level)) {
                $levelList[] = [
                    'name' => '等级' . $i,
                    'level' => $i,
                    'disabled' => true,
                ];
            } else {
                $levelList[] = [
                    'name' => '等级' . $i,
                    'level' => $i,
                    'disabled' => false,
                ];
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $levelList
            ]
        ];
    }

    public function getDetail()
    {
        $detail = BonusMembers::find()->where([
            'id' => $this->id
        ])->asArray()->one();

        $detail['level'] = (int)$detail['level'];

        if ($detail) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                ]
            ];
        }

        return [
            'code' => ApiCode::CODE_ERROR,
            'msg' => '请求失败',
        ];
    }

    public function destroy()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $member = BonusMembers::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

            if (!$member) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '数据异常,该条数据不存在'
                ];
            }

            $member->is_delete = 1;
            $res = $member->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($member));
            }

            $user = BonusCaptain::find()->where(['is_delete' => 0, 'status' => 1, 'mall_id' => \Yii::$app->mall->id, 'level' => $member->id])->one();
            if ($user) {
                throw new \Exception('有用户属于该等级！无法删除');
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                    'msg' => $e->getMessage(),
                ]
            ];
        }
    }

    public function switchStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $member = BonusMembers::findOne($this->id);
            if (!$member) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '数据异常,该条数据不存在'
                ];
            }

            if ($member->status) {
                $user = BonusCaptain::find()->where(['is_delete' => 0, 'status' => 1, 'mall_id' => \Yii::$app->mall->id, 'level' => $member->id])->one();
                if ($user) {
                    throw new \Exception('有用户属于该等级！无法禁用');
                }
            }

            $member->status = $member->status ? 0 : 1;
            $res = $member->save();
            if (!$res) {
                $this->getErrorMsg($member);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    /**
     * 获取所有可用会员
     */
    public function getAllMember()
    {
        $list = BonusMembers::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'status' => 1,
        ])->orderBy('level')->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list
            ]
        ];
    }
}
