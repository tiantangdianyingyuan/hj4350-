<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/23
 * Time: 10:42
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\jobs;


use app\forms\common\ecard\CheckGoods;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Class EcardLogJob
 * @package app\jobs
 * @property CheckGoods $checkGoods
 */
class EcardLogJob extends BaseObject implements JobInterface
{
    public $checkGoods;

    public function execute($queue)
    {
        $this->checkGoods->save();
    }
}
