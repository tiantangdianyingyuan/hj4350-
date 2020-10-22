<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/30
 * Time: 9:27
 */

namespace app\plugins\advance\forms\mall;

use app\core\CsvExport;
use app\forms\mall\export\BaseExport;
use app\models\OrderRefund;

class OrderRefundExport extends \app\forms\mall\export\OrderRefundExport
{
    public function export($query)
    {
        $list = $query->orderBy('created_at DESC')->with(['user.userInfo', 'order'])
            ->asArray()
            ->all();

        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '预售-售后订单' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

}
