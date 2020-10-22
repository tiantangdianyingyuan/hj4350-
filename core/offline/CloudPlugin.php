<?php
/**
 * @copyright ©2018 浙江禾匠信息科技有限公司
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/4 18:20:00
 */


namespace app\core\offline;


class CloudPlugin extends CloudBase
{
    // public $classVersion = '4.2.10';

    /**
     * @param array $args 查询参数
     * @return array ['list'=>[],'pagination'=>[]]
     * @throws CloudException
     */
    public function getList($args = [])
    {
        return $this->httpGet('/mall/plugin/index');
    }

    /**
     * @param $args
     * @return mixed
     * @throws CloudException
     */
    public function getDetail($args)
    {
        return $this->httpGet('/mall/plugin/detail', $args);
    }

    /**
     * @param $id
     * @return mixed
     * @throws CloudException
     */
    // public function createOrder($id)
    // {
    //     return $this->httpPost('/mall/plugin/create-order', [], [
    //         'id' => $id,
    //     ]);
    // }

    /**
     * @param $id
     * @return mixed
     * @throws CloudException
     */
    public function install($id)
    {
        return $this->httpGet('/mall/plugin/install', ['id' => $id]);
    }
}
