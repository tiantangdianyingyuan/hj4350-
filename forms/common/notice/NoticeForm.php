<?php


namespace app\forms\common\notice;

use app\core\Pagination;
use app\core\response\ApiCode;
use app\models\AdminNotice;
use app\models\Model;

class NoticeForm extends Model
{
    public $id;
    public $type;

    public function rules()
    {
        return [
            [['id', 'type'], 'integer'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $model = AdminNotice::find()->andWhere(['is_delete' => 0])->orderBy('created_at desc');
            $first = [];
            $mall_notice = '';
            //3条统计页面公告，顺带到期通知
            switch ($this->type) {
                case 3:
                    $pagination = null;
                    $list = $model->limit($this->type)->asArray()->all();
//                $mall = Mall::findOne(\Yii::$app->mall->id);
//                if ($mall->expired_at != '0000-00-00 00:00:00' && strtotime($mall->expired_at) + (86400 * 30) < time()) {
//                    $mall_notice = '商城到期提醒：' . $mall->expired_at . '到期';
//                }
                    break;
                case 2:
                    $list = $model->page($pagination, 5)->asArray()->all();
                    break;
                default:
                    $model_1 = clone $model;
                    $first = strip_tags($model_1->asArray()->one()['content']);
                    /* @var Pagination $pagination */
                    $list = $model->page($pagination, 5)->offset($pagination->offset + 1)->asArray()->all();
                    break;
            }
            foreach ($list as &$item) {
                $item['content_text'] = strip_tags($item['content']);
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'first' => $first,
                    'list' => $list,
                    'mall_notice' => $mall_notice,
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if (!$this->id) {
                throw new \Exception('ID不能为空');
            }
            $data = AdminNotice::find()->andWhere(['is_delete' => 0, 'id' => $this->id])->asArray()->one();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
