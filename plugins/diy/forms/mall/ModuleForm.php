<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\forms\mall;

use app\core\response\ApiCode;
use app\forms\admin\mall\MallOverrunForm;
use app\models\Model;
use app\plugins\diy\forms\common\CommonFormat;
use app\plugins\diy\forms\common\CommonTemplate;
use app\plugins\diy\models\DiyPage;
use app\plugins\diy\models\DiyTemplate;

class ModuleForm extends Model
{
    public $page;
    public $limit;
    public $keyword;
    public $id;
    public $name;
    public $data;
    /** 'module' */

    public function rules()
    {
        return [
            [['page', 'limit', 'id'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['limit'], 'default', 'value' => 10],
            [['keyword', 'name', 'data'], 'string'],
            [['keyword'], 'default', 'value' => ''],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $common = CommonTemplate::getCommon();
        $list = $common->getList($pagination, [
            'type' => DiyTemplate::TYPE_MODULE,
            'keyword' => $this->keyword,
        ]);
        $list = \yii\helpers\ArrayHelper::toArray($list);

        $extra = [];
        $common = CommonTemplate::getCommon();
        foreach ($common->allComponents() as $key => $allComponent) {
            foreach ($allComponent['list'] as $key2 => $item) {
                if ((isset($item['single']) && $item['single']) || $item['id'] === 'module') {
                    array_push($extra, $item['id']);
                }
            }
        }

        foreach ($list as $key => $item) {
            $newData = \yii\helpers\BaseJson::decode($item['data']);
            $newData = array_filter($newData, function ($item1) use ($extra) {
                return !in_array($item1['id'], $extra);
            });
            $list[$key]['data'] = \yii\helpers\BaseJson::encode((new CommonFormat())->handleAll($newData));
        }


        //todo 全查被应用次数
        $allList = DiyTemplate::find()->select('id,data')->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'type' => DiyTemplate::TYPE_PAGE,
        ])->all();
        $counts = [];
        foreach ($allList as $item) {
            $data = \yii\helpers\BaseJson::decode($item['data']);
            foreach ($data as $item1) {
                if ($item1['id'] === 'module') {
                    if (
                        !isset($item1['data']['id']) //开发兼容的判断
                        && isset($item1['data']['list'])
                        && is_array($item1['data']['list'])
                    ) {
                        $ids = array_column($item1['data']['list'], 'id');
                        $counts = array_merge($counts, $ids);
                    }
                }
            }
        }
        $counts = array_count_values($counts);
        foreach ($list as $key => $item) {
            $list[$key]['useCount'] = $counts[$item['id']] ?? 0;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    private function getModel()
    {
        $query = DiyPage::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        $this->is_home_page == 1 ? $query->andWhere(['is_home_page' => 1]) : $query->andWhere(['id' => $this->id]);
        return $query->one();;
    }

    public function get()
    {
        $common = CommonTemplate::getCommon();
        $data = [
            'allComponents' => $common->allComponents(),
            'overrun' => (new MallOverrunForm())->getSetting()
        ];
        foreach ($data['allComponents'] as $key => $allComponent) {
            $data['allComponents'][$key]['list'] = array_filter($allComponent['list'], function ($item) {
                return (!isset($item['single']) || !$item['single']) && $item['id'] !== 'module';
            });
            $data['allComponents'][$key]['list'] = array_values($data['allComponents'][$key]['list']);
        }


        if ($template = $common->getTemplate($this->id)) {
            $newData = [];
            $templateData = \yii\helpers\BaseJson::decode($template->data);
            foreach ($templateData as $datum) {
                $flag = false;
                foreach ($data['allComponents'] as $allComponent) {
                    foreach ($allComponent['list'] as $item) {
                        if ($item['id'] !== 'module' && ($datum['id'] == $item['id'] || $datum['id'] == 'background')) {
                            $flag = true;
                        }
                    }
                }
                if ($flag) {
                    $newData[] = $datum;
                }
            }
            $data['name'] = $template->name;
            $data['data'] = json_encode((new CommonFormat())->handleAll($newData), JSON_UNESCAPED_UNICODE);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $data
        ];
    }

    public function post()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $common = CommonTemplate::getCommon();
            $template = $common->getTemplate($this->id);
            if (!$template) {
                $template = new DiyTemplate();
                $template->type = DiyTemplate::TYPE_MODULE;
                $template->is_delete = 0;
                $template->mall_id = \Yii::$app->mall->id;
            }
            $template->name = $this->name;
            $template->data = $this->data;
            if (!$template->save()) {
                throw new \Exception('保存失败');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
                'data' => [
                    'id' => $template->id
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $e->getMessage(),
            ];
        }
    }

    public function destroy($id)
    {
        try {
            $common = CommonTemplate::getCommon();
            $template = $common->getTemplate($id);
            if (!$template) {
                throw new \Exception('数据不存在');
            }
            $template->is_delete = 1;
            $template->save();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
