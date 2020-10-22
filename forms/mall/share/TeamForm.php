<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/25
 * Time: 9:23
 */

namespace app\forms\mall\share;


use app\forms\common\share\CommonShareTeam;
use app\models\Model;
use app\models\User;

class TeamForm extends Model
{
    public $status;
    public $id;

    public function rules()
    {
        return [
            [['status', 'id'], 'integer']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $form = new CommonShareTeam();
        $form->mall = \Yii::$app->mall;
        $res = $form->info($this->id, $this->status);

        $list = User::find()->alias('u')->where(['u.id' => $res, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->with(['userInfo'])->all();

        $newList = [];
        /* @var User[] $list*/
        foreach ($list as $item) {
            $newItem = [
                'nickname' => $item->nickname,
                'junior_at' => $item->userInfo->junior_at
            ];
            $newList[] = $newItem;
        }

        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $newList
            ]
        ];
    }
}
