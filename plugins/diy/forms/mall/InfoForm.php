<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/6
 * Time: 13:48
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\diy\models\DiyForm;

class InfoForm extends Model
{
    public $page;
    public $limit;
    public $date_start;
    public $date_end;
    public $fields;
    public $flag;

    public $id;

    public function rules()
    {
        return [
            [['page', 'limit'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['limit'], 'default', 'value' => 20],
            [['date_start', 'date_end', 'flag'], 'string'],
            [['date_start', 'date_end'], 'trim'],
            [['fields'], 'safe'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        /* @var DiyForm[] $list */
        $query = DiyForm::find()->with('user.userInfo')
            ->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
            ->keyword($this->date_start, ['>=', 'created_at', $this->date_start])
            ->keyword($this->date_end, ['<=', 'created_at', $this->date_end]);

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new InfoExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $list = $query->page($pagination, $this->limit, $this->page)->orderBy(['created_at' => SORT_DESC])->all();

        $newList = [];
        foreach ($list as $item) {
            $formData = \Yii::$app->serializer->decode($item->form_data);

            // 新增图片上传数据格式转换
            if (is_array($formData) || $formData instanceof \ArrayObject) {
                foreach ($formData as &$_item) {
                    if (!isset($_item['key']) || $_item['key'] !== 'img_upload') {
                        continue;
                    }
                    if (!isset($_item['value'])) {
                        continue;
                    }
                    if (is_string($_item['value'])) {
                        $_item['value'] = [$_item['value']];
                    }
                }
            }

            $newList[] = [
                'form_data' => $formData,
                'user_id' => $item->user_id,
                'nickname' => $item->user->nickname,
                'avatar' => $item->user->userInfo->avatar,
                'created_at' => $item->created_at,
                'id' => $item->id
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
                'export_list' => (new InfoExport())->fieldsList()
            ]
        ];
    }

    public function delete()
    {
        try {
            $info = DiyForm::findOne(['id' => $this->id, 'mall_id' => \Yii::$app->mall->id]);
            if (!$info) {
                throw new \Exception('所选信息不存在');
            }
            if ($info->is_delete == 1) {
                throw new \Exception('所选信息已删除');
            }
            $info->is_delete = 1;
            if (!$info->save()) {
                throw new \Exception($this->getErrorMsg($info));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => $exception
            ];
        }
    }
}
