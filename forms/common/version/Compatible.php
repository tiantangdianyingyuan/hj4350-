<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/9/23
 * Time: 11:31
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\version;


use yii\base\BaseObject;

class Compatible extends BaseObject
{
    private static $instance;

    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        self::$instance = new self();
        return self::$instance;
    }

    /**
     * @param integer|array|string $data
     * @return array
     * 兼容4.1.0之前的发货方式
     */
    public function sendType($data = null)
    {
        if (!$data) {
            $data = ['express', 'offline'];
        } elseif (!is_array($data)) {
            $data = json_decode($data, true);
            if (!is_array($data)) {
                if (is_numeric($data)) {
                    if ($data == 2) {
                        $data = ['offline'];
                    } elseif ($data == 1) {
                        $data = ['express'];
                    } else {
                        $data = ['express', 'offline'];
                    }
                } else {
                    $data = ['express', 'offline'];
                }
            }
        }
        return $data;
    }
}
