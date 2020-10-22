<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/5/6
 * Time: 17:22
 */

namespace app\plugins\flash_sale\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\flash_sale\forms\common\CommonActivity;
use app\plugins\flash_sale\models\FlashSaleActivity;
use Exception;
use Yii;

class ActivityEditForm extends Model
{
    public $id;
    public $keyword;
    public $status;
    public $title;
    public $start_at;
    public $end_at;
    public $notice;
    public $notice_hours;

    public function rules()
    {
        return [
            [['title', 'start_at', 'end_at',], 'required'],
            [['id', 'status', 'notice', 'notice_hours'], 'integer', 'max' => 99999999],
            [['keyword', 'notice'], 'default', 'value' => ''],
            [['status'], 'default', 'value' => FlashSaleActivity::ACTIVITY_UP],
            [['notice'], 'default', 'value' => 0],
            [['start_at', 'end_at', 'title',], 'string'],
            ['start_at', 'compare', 'compareAttribute' => 'end_at', 'operator' => '<', 'message' => '起始时间必须小于结束时间'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '活动名称',
            'start_at' => '活动开始时间',
            'end_at' => '活动结束时间',
            'notice' => '活动预告',
            'notice_hours' => '活动预告时间'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($this->id) {
                $flashSaleActivity = FlashSaleActivity::find()->where(
                    [
                        'id' => $this->id,
                        'is_delete' => 0,
                        'mall_id' => Yii::$app->mall->id
                    ]
                )->one();

                if (empty($flashSaleActivity)) {
                    throw new Exception('活动不存在');
                }
            } else {
                $flashSaleActivity = new FlashSaleActivity();
                $flashSaleActivity->mall_id = Yii::$app->mall->id;
            }

            if ($this->end_at > '2038-01-01 00:00:00' || $this->start_at > '2038-01-01 00:00:00') {
                throw new \Exception('活动时间不能大于2038-01-01 00:00:00');
            }
            $flashSaleActivity->status = $this->status;
            $flashSaleActivity->title = $this->title;
            $flashSaleActivity->start_at = $this->start_at;
            $flashSaleActivity->end_at = $this->end_at;
            $flashSaleActivity->notice = $this->notice;
            if ($this->notice == 2) {
                if ($this->notice_hours < 1) {
                    throw new Exception('活动预告时间必须大于0');
                }
                $flashSaleActivity->notice_hours = $this->notice_hours;
            }
            $res = $flashSaleActivity->save();
            if (!$res) {
                throw new Exception($this->getErrorMsg($flashSaleActivity));
            }

            $this->id = $flashSaleActivity->id;
            $this->checkFlashSale();

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
                'id' => $this->id
            ];
        } catch (Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    /**
     * 检测是活动时间是否冲突
     * @throws Exception
     */
    public function checkFlashSale()
    {
        $check = CommonActivity::check($this->id, $this->start_at, $this->end_at);
        if ($check) {
            throw new Exception('该时间段已有活动,请修改活动时间日期');
        }
    }
}
