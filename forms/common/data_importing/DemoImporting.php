<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/8
 * Time: 10:49
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\data_importing;


use app\models\Video;

/**
 * Class DemoImporting
 * @package app\forms\common\data_importing
 */
class DemoImporting extends BaseImporting
{
    public static $idList = [];
    /**
     * @throws \Exception
     * @return mixed
     * 数据导入
     */
    public function import()
    {
        if (!is_array($this->v3Data)) {
            throw new \Exception('数据格式不正确');
        }
        foreach ($this->v3Data as $datum) {
            $this->save($datum);
        }
        return true;
    }

    /**
     * @param $datum
     * @throws \Exception
     * @return bool
     * 单条数据添加
     */
    protected function save($datum)
    {
        $default = $this->defaultData();
        $data = $this->check($default, $datum);

        $video = new Video();
        $video->attributes = $data;
        if (!$video->save()) {
            throw new \Exception($this->getErrorMsg($video));
        }

        self::$idList[$datum['id']] = $video->id;
        return true;
    }

    /**
     * @return array
     * 默认值，可根据实际情况进行选择
     */
    private function defaultData()
    {
        return [
            'mall_id' => $this->mall->id,
            'title' => '标题',
            'type' => 0,
            'url' => '',
            'content' => '',
            'sort' => 100,
            'pic_url' => '',
            'is_delete' => 0
        ];
    }
}
