<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\diy\forms\common;


use app\forms\mall\live\LiveForm;
use app\models\Model;

class DiyLiveForm extends Model
{
    public function getLiveList()
    {
        $form = new LiveForm();
        $list = $form->getList();

        return $list;
    }

    public function getNewList($data, $res)
    {
        $liveList = isset($res['data']['list']) ? $res['data']['list'] : [];
        $data['live_list'] = [];
        if ($data['number'] >= count($liveList)) {
            $data['live_list'] = $liveList;
        } else {
            $data['live_list'] = array_slice($liveList, 0, $data['number']);
        }
        return $data;
    }
}
