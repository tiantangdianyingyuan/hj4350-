<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/3
 * Time: 16:23
 */

namespace app\plugins\stock\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\User;
use app\models\UserIdentity;
use app\plugins\stock\models\StockUser;
use Yii;

class ShareForm extends Model
{
    public $nickname;

    public function rules()
    {
        return [
            [['nickname'], 'string']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $stock_list = StockUser::find()->select('user_id')
            ->andWhere(['status' => 1])
            ->andWhere(['is_delete' => 0])->asArray()->all();

        $stock_arr = [];
        foreach ($stock_list as $item) {
            $stock_arr[] = $item['user_id'];
        }

        $model = User::find()->alias('u')->select(['u.id', 'u.nickname'])
            ->where(['u.is_delete' => 0, 'u.mall_id' => Yii::$app->mall->id])
            ->leftJoin(['ui' => UserIdentity::tableName()], 'ui.user_id = u.id')
            ->andWhere(['ui.is_delete' => 0, 'ui.is_distributor' => 1])
            ->andWhere(['not in', 'u.id', $stock_arr]);
        if ($this->nickname) {
            $model->andWhere(['like', 'u.nickname', $this->nickname]);
        }
        $list = $model->asArray()
            ->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $list
        ];
    }

}