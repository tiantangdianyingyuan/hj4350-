<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/23
 * Time: 16:22
 */

namespace app\plugins\diy\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\diy\models\DiyPage;
use app\plugins\diy\models\DiyPageNav;
use app\plugins\diy\models\DiyTemplate;

class PageEditForm extends Model
{
    public $id;
    public $title;
    public $show_navs;
    public $is_disable;
    public $navs;

    public function rules()
    {
        return [
            [['id'], 'safe'],
            [['title'], 'trim'],
            [['title', 'show_navs', 'is_disable', 'navs'], 'required'],
            [['show_navs', 'is_disable'], 'in', 'range' => [0, 1]],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $model = DiyPage::findOne([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ]);
            if (!$model) {
                $model = new DiyPage();
                $model->mall_id = \Yii::$app->mall->id;
            }
            $model->title = $this->title;
            $model->show_navs = $this->show_navs;
            $model->is_disable = $this->is_disable;
            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            }
            DiyPageNav::deleteAll([
                'page_id' => $model->id,
                'mall_id' => \Yii::$app->mall->id,
            ]);
            foreach ($this->navs as $nav) {
                $templateExists = DiyTemplate::find()->where([
                    'id' => $nav['template_id'],
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                ])->exists();
                if (!$templateExists) {
                    throw new \Exception('所选模板不存在。');
                }
                $pageNav = new DiyPageNav();
                $pageNav->mall_id = \Yii::$app->mall->id;
                $pageNav->page_id = $model->id;
                $pageNav->name = trim($nav['name']);
                $pageNav->template_id = $nav['template_id'];
                if (!$pageNav->save()) {
                    throw new \Exception($this->getErrorMsg($pageNav));
                }
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功。',
                'data' => [
                    'id' => $model->id,
                ],
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }
}
