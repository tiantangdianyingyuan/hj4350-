<?php

/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/24
 * Time: 15:43
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\forms\mall;

use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\plugins\diy\forms\common\CommonPageTwo;
use app\plugins\diy\forms\mall\market\CommonTemplateCenter;
use app\plugins\diy\models\CoreTemplate;
use app\plugins\diy\models\CoreTemplateEdit;
use app\plugins\diy\models\CoreTemplateType;
use app\plugins\diy\models\DiyPage;
use app\plugins\diy\models\DiyTemplate;

/**
 * @property Mall $mall
 */
class TemplateForm extends Model
{
    public $page;
    public $limit;
    public $type;
    public $keyword;
    public $id;
    public $is_home_page;
    public $templateType;

    /** 'module' */

    public function rules()
    {
        return [
            [['page', 'limit', 'id', 'is_home_page'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['limit'], 'default', 'value' => 10],
            [['type', 'keyword'], 'string'],
            [['type', 'keyword'], 'default', 'value' => ''],
        ];
    }

    public function getMarketList()
    {
        $query = CoreTemplate::find()->where([
            'is_delete' => 0,
        ]);

        if (!\Yii::$app->role->isSuperAdmin) {
            $templatePermission = \Yii::$app->role->getTemplate();
            $common = CommonTemplateCenter::getInstance();
            $showList = $common->getShowTemplate($templatePermission);
            $useList = $common->getUseTemplate($templatePermission);
            $templateId = array_intersect($showList, $useList);
            $query->andWhere(['template_id' => $templateId]);
        }

        $list = $query->page($pagination, 23)->asArray()->all();
        // 修改过的模板
        $coreTemplateEdit = CoreTemplateEdit::findAll([
            'template_id' => array_column($list, 'template_id'),
        ]);
        foreach ($list as $key => $item) {
            //$list[$key]['pics'] = \yii\helpers\BaseJson::decode($item['pics']);
            $list[$key]['pics'] = json_decode(stripcslashes(substr($item['pics'], 1, -1)));
            $list[$key]['is_use'] = 1;
            foreach ($coreTemplateEdit as $template) {
                if ($template->template_id == $list[$key]['id']) {
                    $list[$key]['name'] = $template->name;
                    $list[$key]['price'] = $template->price;
                }
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ],
        ];
    }

    public function getHome()
    {
        $diyPage = DiyPage::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'is_home_page' => 1,
        ]);
        $template = $diyPage->template ?? null;
        if ($template) {
            $form = new TemplateEditForm();
            $form->id = $diyPage->id;
            $info = $form->get();

            //格式化api数据 可补充优化
            $data = CommonPageTwo::getCommon(\Yii::$app->mall, '', '');
            $apiData = $data->getPage(0, true);
            //商品
            $goodsTag = [
                //'module' => 'DELETE',
                'goods' => 'sGoods',
                'mch' => 'sMch',
                'pintuan' => 'sPintuan',
                'booking' => 'sBooking',
                'miaosha' => 'sMiaosha',
                'bargain' => 'sBargain',
                'integral-mall' => 'sIntegralMall',
                'lottery' => 'sLottery',
                'quick-nav' => 'sQuickNav',
                'advance' => 'sAdVance',
                'pick' => 'sPick',
                'gift' => 'sGift',
            ];
            foreach ($apiData as $key => $item) {
                if (
                    in_array($item['id'], array_keys($goodsTag))
                    && isset($item['data']['list'])
                    && is_array($item['data']['list'])
                ) {
                    foreach ($item['data']['list'] as $key1 => $item1) {
                        $apiData[$key]['data']['list'][$key1]['picUrl'] = $item1['cover_pic'];
                    }
                }
            }

            $info['data']['data'] = \yii\helpers\BaseJson::encode($apiData);
            return $info;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $template,
        ];
    }

    //FIXME
    private function childFork(array $list)
    {
        //        $key = 'diy-access-page-count:' . $this->mall->id;
        //        return \Yii::$app->cache->set($key, $values, 0);

        //        $key = 'diy-access-page-count:' . $this->mall->id;
        //        return \Yii::$app->cache->get($key) ?: [];


        $forkNums = ceil(count($list) / 2) > 10 ? 10 : ceil(count($list) / 2);
        if (!function_exists('pcntl_fork')) {
            return false;
        }
        for (
            $i = 0; $i < $forkNums;
            $i++
        ) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                \Yii::error('创建子进程失败');
                return false;
            } elseif ($pid) {
                pcntl_wait($status);
                //等待子进程中断，防止子进程成为僵尸进程。
            } else {
                //     $filename = sprintf('diy_template_fork_pid_%s.name', '80');
                //        $filename = '.' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $filename;
                //        file_put_contents($filename, 'data');
            }
        }
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = DiyPage::find()->alias('p')->select('p.*')->where([
            'p.mall_id' => \Yii::$app->mall->id,
            'p.is_delete' => 0,
        ])->innerJoinwith(['templateOne t' => function ($query) {
            $query->where(['t.type' => DiyTemplate::TYPE_PAGE]);
        }]);
        //->leftJoin([
        //    'pn' => DiyPageNav::find()->select('page_id')->where([
        //        'mall_id' => \Yii::$app->mall->id,
        //    ])
        //], 'pn.page_id = p.id')->groupBy('p.id');

        empty($this->keyword) || $query->andWhere(['p.like', 'name', $this->keyword]);
        $list = $query
            //->having(['num' => 1])
            ->page($pagination, 10)
            ->orderBy(['p.is_home_page' => SORT_DESC, 'p.created_at' => SORT_DESC])
            ->asArray()
            ->all();
        //$this->childFork($list);

        $newList = [];
        $model = CommonPageTwo::getCommon(\Yii::$app->mall);
        $diyAccessLog = $model->getLog();
        //todo slow
        foreach ($list as $key => $template) {
            /*try {
                \Yii::$app->request->headers['x-app-version'] = '4.2.82';
                $goodsCount = $model->getGoodsCount($template['id']);
            } catch (\Exception $e) {
                $goodsCount = 0;
               // dd($e);//开发测试
            }*/

            array_push($newList, [
                'id' => $template['id'],
                'name' => $template['title'],
                'is_home_page' => $template['is_home_page'],
                'created_at' => $template['created_at'],
                'userCount' => count($diyAccessLog[$template['id']]['userIds'] ?? []),
                'accessCount' => $diyAccessLog[$template['id']]['accessCount'] ?? 0,
                //'goodsCount' => $goodsCount,
            ]);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination
            ]
        ];
    }


    public function destroy($id)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $diyPage = DiyPage::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $id,
            ]);
            if (!$diyPage) {
                throw new \Exception('数据已删除');
            }

            $diyPage->is_delete = 1;
            $diyPage->save();
            if ($diyPage->template) {
                foreach ($diyPage->template as $template) {
                    $template->is_delete = 1;
                    $template->save();
                }
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function changeHasHomeStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $diyPage = DiyPage::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'is_delete' => 0,
            ]);
            if (empty($diyPage)) {
                throw new \Exception('数据不存在');
            }

            if ($this->is_home_page == 1) {
                $t = \Yii::$app->db->beginTransaction();
                $updateArr = ['is_home_page' => 0, 'updated_at' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])];
                $beginArr = ['is_home_page' => 1, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id];
                diyPage::updateAll($updateArr, $beginArr);
                $diyPage->is_home_page = 1;
                $diyPage->save();
                $t->commit();
            } else {
                $diyPage->is_home_page = 0;
                $diyPage->save();
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $this->is_home_page ? sprintf('设置%s为店铺首页成功', $diyPage->title) : '取消店铺首页成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
