<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/6/17
 * Time: 11:11
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\collect\collect_data;


use app\forms\common\collect\collect_api\Taobao;

/**
 * Class JuTaobaoData
 * @package app\forms\common\collect\collect_data
 */
class JuTaobaoData extends AliData
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->api = new Taobao();
    }

    public function getItemId($url)
    {
        $id = $this->pregSubstr('/(\?item_id=|&item_id=)/', '/&/', $url);
        if (empty($id)) {
            throw new \Exception($url . '链接错误，没有包含商品id');
        }
        $itemId = $id[0];
        return $itemId;
    }
}
