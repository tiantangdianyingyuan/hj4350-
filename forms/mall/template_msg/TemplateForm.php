<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\template_msg;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Formid;
use app\models\Model;
use app\models\Option;
use app\models\User;
use app\models\UserIdentity;
use app\models\UserInfo;

class TemplateForm extends Model
{
    public $keyword;

    public function rules()
    {
        return [
            [['keyword'], 'string']
        ];
    }

    public function getList()
    {
        $option = CommonOption::get(Option::NAME_WX_TEMPLATE, \Yii::$app->mall->id, Option::GROUP_APP);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $option ? $option : [],
            ]
        ];
    }

    public function getUsers($platform = '')
    {
        $newUserIds = Formid::find()->alias('f')
            ->andWhere(['>', 'f.remains', 0])
            ->andWhere(['>', 'f.expired_at', date('Y-m-d H:i:s')])
            ->select('user_id');

        $query = User::find()->alias('u')->where([
            'u.is_delete' => 0,
            'u.mall_id' => \Yii::$app->mall->id,
            'u.id' => $newUserIds
        ])->with(['oneFormId' => function ($query) {
            $query->andWhere(['>', 'remains', 0])->andWhere(['>', 'expired_at', date('Y-m-d H:i:s')]);
        }]);

        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'u.nickname', $this->keyword],
                ['like', 'u.id', $this->keyword],
            ]);
        }

        if ($platform) {
            $query->leftJoin(['ui' => UserInfo::tableName()], 'u.id = ui.user_id')
                ->andWhere(['ui.platform' => $platform]);
        }
        $users = $query->page($pagination, 10)->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'users' => $users
            ]
        ];
    }

    /**
     * 获取所有FormID的用户
     * @return array
     */
    public function getAllUsers()
    {
        $query1 = Formid::find()->alias('f')
            ->andWhere(['>', 'f.remains', 0])
            ->andWhere(['>', 'f.expired_at', date('Y-m-d H:i:s')])
            ->select('f.user_id');

        $query = User::find()->alias('u')->where([
            'u.is_delete' => 0,
            'u.mall_id' => \Yii::$app->mall->id
        ])->andWhere(['id' => $query1]);

        $users = $query->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'users' => $users
            ]
        ];
    }
}
