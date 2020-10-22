<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/25
 * Time: 10:24
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\ecard;


use app\models\Ecard;
use app\models\EcardLog;
use app\models\EcardOptions;
use app\models\EcardOrder;
use app\models\Goods;
use app\models\Model;
use app\models\OrderDetail;

/**
 * Class CheckGoods
 * @package app\forms\common\ecard
 * @property Ecard $ecard
 */
class CheckGoods extends Model
{
    const STATUS_ADD = 'add';
    const STATUS_SALES = 'sales';
    const STATUS_OCCUPY = 'occupy';
    const STATUS_DELETE = 'delete';
    const STATUS_REFUND = 'refund';

    public $ecard;
    public $status;
    public $sign;
    public $number;
    public $oldStock;
    public $goods_id;

    public function save()
    {
        \Yii::warning('电子卡密日志');
        if (!$this->ecard || !($this->ecard instanceof Ecard)) {
            \Yii::warning('无效的电子卡密');
            return false;
        }
        try {
            $this->log($this->status, $this->number);
        } catch (\Exception $exception) {
            \Yii::warning('电子卡密日志验证为通过，不予记录');
            \Yii::warning($exception);
            return false;
        }
    }

    private function log($status, $number)
    {
        $log = new EcardLog();
        $log->mall_id = $this->ecard->mall_id;
        $log->ecard_id = $this->ecard->id;
        $log->status = $status;
        $log->number = $number;
        $log->sign = $this->sign;
        $log->goods_id = $this->goods_id;
        $log->created_at = mysql_timestamp();
        if (!$log->save()) {
            \Yii::warning((new Model())->getErrorMsg($log));
        }
    }
}
