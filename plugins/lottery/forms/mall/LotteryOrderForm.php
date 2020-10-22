<?php


namespace app\plugins\lottery\forms\mall;

use app\forms\mall\order\OrderForm;

class LotteryOrderForm extends OrderForm
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
            $exp = new OrderExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->send_type = $this->send_type;
            $exp->export($new_query);
            return false;
        }
    }
}