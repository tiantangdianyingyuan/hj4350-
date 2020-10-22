<?php

/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/24
 * Time: 19:29
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\forms\mall;

use app\core\response\ApiCode;
use app\forms\admin\mall\MallOverrunForm;
use app\models\Model;
use app\plugins\diy\forms\common\CommonFormat;
use app\plugins\diy\forms\common\CommonTemplate;
use app\plugins\diy\models\DiyPage;
use app\plugins\diy\models\DiyPageNav;
use app\plugins\diy\models\DiyTemplate;

class TemplateEditForm extends Model
{
    public $name;
    public $data;
    public $id;
    public $type;
    public $is_home_page;
    public function rules()
    {
        return [
            [['name', 'data'], 'required'],
            [['id', 'is_home_page'], 'integer'],
            [['type'], 'string', 'max' => 100],
            [['data'], dataValidator::className()],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '微页面标题',
            'data' => '微页面内容',
            'type' => '类型',
        ];
    }

    private function getModel()
    {
        $query = DiyPage::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        $this->is_home_page == 1 ? $query->andWhere(['is_home_page' => 1]) : $query->andWhere(['id' => $this->id]);
        return $query->one();
    }

    public function get()
    {
        $common = CommonTemplate::getCommon();
        $data = [
            'allComponents' => $common->allComponents(),
            'overrun' => (new MallOverrunForm())->getSetting(),
        ];

        $diyPage = $this->getModel();
        if ($template = $diyPage->templateOne ?? null) {
            $newData = [];
            $templateData = \yii\helpers\BaseJson::decode($template->data);
            $formatModel = new CommonFormat();
            foreach ($templateData as $datum) {
                $datum = $formatModel->handleOne($datum);
                if ($datum['id'] === 'module') {
                    foreach ($datum['data']['list'] as $key1 => $item1) {
                        if ($moduleModel = $common->getTemplate($item1['id'])) {
                            $moduleArray = \yii\helpers\ArrayHelper::toArray($moduleModel);
                            $datum['data']['list'][$key1]['data'] = $formatModel->handleAll(\yii\helpers\BaseJson::decode($moduleArray['data']));
                        }
                    }
                }
                $flag = false;
                foreach ($data['allComponents'] as $allComponent) {
                    foreach ($allComponent['list'] as $item) {
                        if ($datum['id'] == $item['id'] || $datum['id'] == 'background') {
                            $flag = true;
                        }
                    }
                }
                if ($flag) {
                    $newData[] = $datum;
                }
            }
            $data['name'] = $diyPage->title;
        //    dd($newData);
            $data['data'] = json_encode($newData, JSON_UNESCAPED_UNICODE);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $data
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            /** @var DiyPage $diyPage */
            $diyPage = $this->getModel();
            $template = $diyPage->templateOne ?? null;
            if (!$template) {
                $template = new DiyTemplate();
                $template->type = DiyTemplate::TYPE_PAGE;
                $template->is_delete = 0;
                $template->mall_id = \Yii::$app->mall->id;
                $template->name = $this->name;
                $template->data = $this->data;
                if (!$template->save()) {
                    throw new \Exception('template错误');
                }

                $diyPage = new DiyPage();
                $diyPage->mall_id = \Yii::$app->mall->id;
                $diyPage->show_navs = 0;
                $diyPage->is_disable = 0;
                $diyPage->title = $this->name;
                $diyPage->is_home_page = $this->is_home_page == 1 ? 1 : 0;
                if (!$diyPage->save()) {
                    throw new \Exception('diyPage错误');
                }

                $diyPageNav = new DiyPageNav();
                $diyPageNav->mall_id = \Yii::$app->mall->id;
                $diyPageNav->name = $this->name;
                $diyPageNav->template_id = $template->id;
                $diyPageNav->page_id = $diyPage->id;
                if (!$diyPageNav->save()) {
                    throw new \Exception('diyPageNav错误');
                }
            } else {
                $template->name = $this->name;
                $template->data = $this->data;
                $diyPage->title = $this->name;
                if (!$diyPage->save() || !$template->save()) {
                    throw new \Exception('保存失败');
                }
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
                'data' => [
                    'id' => $diyPage->id
                ]
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
