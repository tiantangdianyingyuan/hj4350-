<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/26
 * Time: 17:13
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fxhb\forms\api;


use app\models\Mall;
use app\models\Model;
use app\models\User;

/**
 * @property Mall $mall
 * @property User $user
 */
class ApiModel extends Model
{
    public $mall;
    public $user;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        if (!$this->mall) {
            $this->mall = \Yii::$app->mall;
        }
        if ($this->user) {
            $this->user = \Yii::$app->user->identity;
        }
    }
}
