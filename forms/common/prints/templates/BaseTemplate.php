<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/2
 * Time: 17:54
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\prints\templates;

use app\forms\common\prints\content\OrderContent;
use app\forms\common\prints\printer\BasePrinter;

/**
 * Class BaseTemplate
 * @package app\forms\common\prints\templates
 * @property OrderContent $data
 * @property BasePrinter $printer
 */
abstract class BaseTemplate
{
    public $data;
    public $printer;

    public function __construct($array = [])
    {
        foreach ($array as $key => $item) {
            if (property_exists($this, $key)) {
                $this->$key = $item;
            }
        }
    }

    abstract public function getContent();

    /**
     * 返回数组 键值说明
     * handle 表示操作方法名
     * content 表示操作方法的参数
     * show 表示是否执行操作
     * children 表示需要先执行的操作数组
     */
    abstract public function getContentByArray();
}
