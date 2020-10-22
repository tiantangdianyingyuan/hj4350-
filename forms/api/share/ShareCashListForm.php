<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/28
 * Time: 13:47
 */

namespace app\forms\api\share;


use app\core\response\ApiCode;
use app\forms\common\share\CommonShareCashList;
use app\models\Model;
use app\models\User;

class ShareCashListForm extends Model
{
    public $page;
    public $limit;
    public $status;

    public function rules()
    {
        return [
            [['page', 'limit', 'status'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 10],
            ['status', 'default', 'value' => -1]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        /* @var User $user*/
        $user = \Yii::$app->user->identity;
        $form = new CommonShareCashList($this->attributes);
        $form->user_id = $user->id;
        $form->type = 'api';
        $res = $form->search();
        $list = $res['list'];
        $newList = [];
        foreach ($list as $item) {
            $time = date('Y-m-d', strtotime($item['time']['created_at']));
            if (isset($newList[$time])) {
                $newList[$time]['list'][] = $item;
            } else {
                $newItem = [
                    'date' => date('m月d日', strtotime($time)),
                    'list' => [
                        $item
                    ]
                ];
                $newList[$time] = $newItem;
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $newList
            ]
        ];
    }
}
