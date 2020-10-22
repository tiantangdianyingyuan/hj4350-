<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\mall_member;


use app\core\response\ApiCode;
use app\forms\common\CommonMallMember;
use app\models\MallMembers;
use app\models\MallMemberRights;
use app\models\Model;
use app\models\User;
use app\models\UserIdentity;

class MallMemberForm extends Model
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
            'id' => '会员ID',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = MallMembers::find()->where([
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
        $list = MallMembers::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->asArray()->all();

        $leves = [];
        foreach ($list as $item) {
            $leves[] = $item['level'];
        }

        $levelList = [];
        for ($i = 1; $i <= 100; $i++) {
            if (in_array($i, $leves)) {
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

    /**
     * 获取所有可用会员
     */
    public function getAllMember()
    {
        $list = CommonMallMember::getAllMember();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list
            ]
        ];
    }

    public function getDetail()
    {
        $detail = CommonMallMember::getDetail($this->id);

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
            $member = MallMembers::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

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

            $res = MallMemberRights::updateAll([
                'is_delete' => 1,
            ], [
                'member_id' => $member->id
            ]);

            $userIds = User::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->select('id');
            $userIdentity = UserIdentity::find()->where([
                'user_id' => $userIds,
                'member_level' => $member->level
            ])->one();
            if ($userIdentity) {
                throw new \Exception('有用户属于该会员！无法删除');
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
            $member = MallMembers::findOne($this->id);
            if (!$member) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '数据异常,该条数据不存在'
                ];
            }

            if ($member->status) {
                $userIds = User::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->select('id');
                $userIdentity = UserIdentity::find()->where([
                    'user_id' => $userIds,
                    'member_level' => $member->level
                ])->one();
                if ($userIdentity) {
                    throw new \Exception('有用户属于该会员！无法禁用');
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
}
