<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/8
 * Time: 10:40
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\data_importing;


use app\models\Mall;
use app\models\Model;

/**
 * Class BaseImporting
 * @package app\forms\common\data_importing
 * @property Mall $mall
 */
abstract class BaseImporting extends Model
{
    public $mall;
    public $v3Data;
    protected $ignore = ['mall_id', 'store_id'];

    /**
     * @throws \Exception
     * @return mixed
     * 数据导入
     */
    abstract public function import();

    /**
     * @param $default
     * @param $datum
     * @return mixed
     * 将$datum中的数据赋值到$default中，并返回$default
     */
    protected function check($default, $datum)
    {
        foreach ($default as $index => $item) {
            // mall_id和store_id有更改，需要重新赋值
            if (isset($datum[$index]) && !in_array($index, $this->ignore)) {
                $default[$index] = $datum[$index];
            }
        }
        return $default;
    }
}
