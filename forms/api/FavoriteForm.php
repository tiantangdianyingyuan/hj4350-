<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 13:59
 */

namespace app\forms\api;


use app\core\response\ApiCode;
use app\models\Favorite;
use app\models\Mall;
use app\models\Model;
use app\models\User;

/**
 * @property User $user
 * @property Mall $mall
 */
class FavoriteForm extends Model
{
    public $goods_id;
    public $goods_ids;

    public $user;
    public $mall;

    public function rules()
    {
        return [
            [['goods_id'], 'integer'],
            [['goods_ids'], 'safe']
        ];
    }

    public function __construct(array $config = [])
    {
        $this->mall = \Yii::$app->mall;
        $this->user = \Yii::$app->user->identity;
        parent::__construct($config);
    }

    public function create()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $res = Favorite::createModel($this->mall->id, $this->user->id, $this->goods_id);
            if ($res) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '收藏成功',
                    'data' => true
                ];
            } else {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '收藏失败'
                ];
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function delete()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $res = Favorite::deleteModel($this->mall->id, $this->user->id, $this->goods_id);
        if ($res) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '取消成功',
                'data' => false
            ];
        } else {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '取消失败'
            ];
        }
    }

    public function batchRemove()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $res = Favorite::removeBatch($this->mall->id, $this->user->id, $this->goods_ids);
        if ($res) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '取消成功',
                'data' => false
            ];
        } else {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '取消失败'
            ];
        }
    }
}
