<?php


namespace app\forms\common\data_importing;


use app\models\GoodsCats;
use Exception;

class YyCatImporting extends BaseImporting
{
    public static $yyCatIds = [];

    public function import()
    {
        if (!is_array($this->v3Data)) {
            throw new \Exception('数据格式不正确');
        }
        foreach ($this->v3Data as $datum) {
            self::$yyCatIds[$datum['id']] = $this->save($datum);
        }
        return true;
    }

    private function getData($datum, $parent_id = 0)
    {
        $value = [
            'mall_id' => $this->mall->id,
            'mch_id' => 0,
            'parent_id' => $parent_id,
            'name' => $datum['name'],
            'pic_url' => $datum['pic_url'],
            'sort' => $datum['sort'],
            'big_pic_url' => '',
            'advert_pic' => '',
            'advert_url' => '',
            'status' => 1,
            'is_show' => 1,
            'advert_params' => '',
            'created_at' => mysql_timestamp($datum['addtime']),
            'deleted_at' => '0000-00-00 00:00:00',
            'updated_at' => '0000-00-00 00:00:00',
            'is_delete' => $datum['is_delete'],
        ];
        return $value;
    }

    protected function save($datum)
    {
        $model = new GoodsCats();
        $model->attributes = $this->getData($datum);
        if ($model->save()) {
            return $model->attributes['id'];
        } else {
            throw new Exception($this->getErrorMsg($model));
        }
    }
}