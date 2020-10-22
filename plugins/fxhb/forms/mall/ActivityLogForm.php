<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\fxhb\forms\mall;

use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\plugins\fxhb\models\FxhbActivity;
use app\plugins\fxhb\models\FxhbUserActivity;

/**
 * @property Mall $mall
 */
class ActivityLogForm extends Model
{
    public $mall;
    public $page;
    public $status;

    public function rules()
    {
        return [
            [['page', 'status'], 'integer'],
            [['status'], 'default', 'value' => -1]
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $list = FxhbUserActivity::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'parent_id' => 0
        ])->with(['children.user.userInfo', 'user.userInfo', 'activity'])
            ->keyword($this->status != -1, ['status' => $this->status])
            ->orderBy('created_at DESC')
            ->page($pagination)->asArray()->all();

        $newArr = [];
        foreach ($list as $key => $item) {
            $newArr['id'] = $item['id'];
            $newArr['get_price'] = $item['get_price'];
            $newArr['created_at'] = $item['created_at'];
            $newArr['user'] = $item['user'];
            array_unshift($list[$key]['children'], $newArr);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}
