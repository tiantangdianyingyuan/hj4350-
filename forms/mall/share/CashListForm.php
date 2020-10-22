<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/26
 * Time: 11:05
 */

namespace app\forms\mall\share;


use app\forms\common\share\CommonShareCashList;
use app\models\Model;

class CashListForm extends Model
{
    public $mall;

    public $page;
    public $limit;

    public $status;

    public $start_date;
    public $end_date;
    public $keyword;
    public $platform;

    public $fields;
    public $flag;
    public $user_id;

    public function rules()
    {
        return [
            [['status'], 'required'],
            [['page', 'limit', 'status', 'user_id'], 'integer'],
            [['fields'], 'safe'],
            [['flag'], 'string'],
            [['keyword'], 'trim'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $form = new CommonShareCashList($this->attributes);
        return [
            'code' => 0,
            'data' => $form->search()
        ];
    }
}
