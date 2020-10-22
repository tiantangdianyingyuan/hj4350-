<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\data_importing;


use app\forms\PickLinkForm;
use app\models\GoodsCats;
use app\models\HomeNav;

class IntegralCatImporting extends BaseImporting
{
    public static $integralCatIds = [];

    public function import()
    {
        try {
            foreach ($this->v3Data as $datum) {
                $cat = $this->saveCat($datum);
                self::$integralCatIds[$datum['id']] = $cat->id;
                if (isset($datum['childrenList'])) {
                    foreach ($datum['childrenList'] as $item) {
                        $cat2 = $this->saveCat($item, $cat->id);
                        self::$integralCatIds[$item['id']] = $cat2->id;
                    }
                }

            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function saveCat($datum, $parentId = 0)
    {
        $cats = new GoodsCats();
        $cats->mall_id = $this->mall->id;
        $cats->parent_id = $parentId;
        $cats->name = $datum['name'];
        $cats->pic_url = $datum['pic_url'];
        $cats->sort = $datum['sort'];
        $cats->status = 1;
        $cats->is_show = 1;
        $cats->advert_params = '';
        $cats->created_at = date('Y-m-d H:i:s', $datum['addtime']);
        $cats->updated_at = date('Y-m-d H:i:s', $datum['addtime']);
        $res = $cats->save();

        if (!$res) {
            throw new \Exception($this->getErrorMsg($cats));
        }

        return $cats;
    }
}