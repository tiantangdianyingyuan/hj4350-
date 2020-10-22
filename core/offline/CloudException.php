<?php
/**
 * @copyright ©2018 浙江禾匠信息科技有限公司
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/4 18:23:00
 */


namespace app\core\offline;


class CloudException extends \Exception
{
    public $raw;

    public function __construct($message = '', $code = 0, $previous = null, $raw)
    {
        $this->raw = $raw;
        parent::__construct($message, $code, $previous);
    }
}
