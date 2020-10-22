<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/12
 * Time: 13:59
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\models;

use app\models\Model;

/**
 * Class Code
 * @package app\plugins\bargain\models
 * 所有的数据库状态码
 */
class Code extends Model
{
    const ALLOW_MIDWAY = 1; // 允许中途下单
    const UNALLOWED_MIDWAY = 2; // 不允许中途下单
    const BARGAIN_PROGRESS = 0; // 砍价进行中
    const BARGAIN_SUCCESS = 1; // 砍价成功
    const BARGAIN_FAIL = 2; // 砍价失败
    const OPEN = 1; // 开启
    const CLOSED = 0; // 关闭
    const SEND_TYPE_ALL = 0; // 发货方式 快递和自提
    const SEND_TYPE_EXPRESS = 1; // 快递
    const SEND_TYPE_OFFLINE = 2; // 自提
}
