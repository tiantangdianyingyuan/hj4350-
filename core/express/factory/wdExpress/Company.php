<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\core\express\factory\wdExpress;


use app\core\express\core\Wd;

class Company
{
    public $chart = [];
    public $file_path = 'statics/text/';
    public $file_name = 'alipay_wdexpress_list.json';

    public $file;

    public function __construct()
    {
        $this->file = $this->file_path . $this->file_name;
    }

    public function getList()
    {
        if (empty($this->chart)) {
            if (is_file($this->file) && is_readable($this->file)) {
                $data = file_get_contents($this->file);
                return json_decode($data, true);
            }
            return $this->putList();
        }
        return $this->chart;
    }

    public function putList()
    {
        if (is_writable($this->file_path)) {
            try {
                //正常不用执行到
                $wdExpress = new Wd(['code' => 'ea41090f0e55487897a188b04bcf5ad3']);
                $data = $wdExpress->getList();
                if ($data['status'] == 200) {
                    file_put_contents($this->file, json_encode($data['result']));
                    return $this->chart = $data['result'];
                }
            } catch (\Exception $e) {
                \Yii::error($e->getMessage());
            }
        }
        return false;
    }
}