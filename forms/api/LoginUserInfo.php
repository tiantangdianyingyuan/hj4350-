<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/5 15:32
 */


namespace app\forms\api;


use yii\base\Component;

class LoginUserInfo extends Component
{
    public $nickname;
    public $username;
    public $avatar;
    public $platform_user_id;
    public $platform;

    /**
     * @var string $scope
     * auth_info 用户授权
     * auth_base 静默授权
     */
    public $scope = 'auth_info';
    public $unionId = '';
}
