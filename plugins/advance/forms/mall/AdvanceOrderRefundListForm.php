<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/30
 * Time: 9:27
 */

namespace app\plugins\advance\forms\mall;

use app\forms\mall\order\OrderRefundListForm;

class AdvanceOrderRefundListForm extends OrderRefundListForm
{
    public $flag;

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = $this->where();
        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new OrderRefundExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }
    }
}
