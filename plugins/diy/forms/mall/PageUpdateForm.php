<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/24
 * Time: 15:42
 */

namespace app\plugins\diy\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\diy\models\DiyPage;

class PageUpdateForm extends Model
{
    public $id;
    public $title;
    public $show_navs;
    public $is_disable;
    public $is_home_page;
    public $is_delete;

    public function rules()
    {
        return [
            [['id', 'title', 'show_navs', 'is_disable', 'is_home_page', 'is_delete'], 'required'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = DiyPage::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '内容不存在。',
            ];
        }
        $model->attributes = $this->attributes;
        if (!$model->save()) {
            return $this->getErrorResponse($model);
        }
        if ($model->is_home_page == 1) {
            DiyPage::updateAll(['is_home_page' => 0,], [
                'AND',
                ['mall_id' => \Yii::$app->mall->id],
                ['is_delete' => 0],
                ['!=', 'id', $model->id],
            ]);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '操作成功。',
        ];
    }
}
