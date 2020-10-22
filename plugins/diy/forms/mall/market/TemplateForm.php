<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/7
 * Time: 14:22
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\forms\mall\market;

use app\core\exceptions\ClassNotFoundException;
use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\mall\home_page\HomePageForm;
use app\models\Banner;
use app\plugins\diy\models\CoreTemplate;
use app\plugins\diy\models\CoreTemplateEdit;
use app\models\HomeBlock;
use app\models\HomeNav;
use app\models\Mall;
use app\models\MallBannerRelation;
use app\models\Model;
use app\models\Option;
use app\plugins\diy\models\CoreTemplateType;
use app\plugins\diy\Plugin;
use app\plugins\diy\models\CloudTemplate;
use yii\helpers\ArrayHelper;

class TemplateForm extends Model
{
    public $page;
    public $search;
    public $mall_id;
    public $template_id;
    public $keyword;
    public $type;
    public $status;
    public $limit;

    public function rules()
    {
        return [
            [['page', 'mall_id', 'template_id', 'status', 'limit'], 'integer'],
            [['search', 'keyword', 'type'], 'trim'],
            [['search', 'keyword', 'type'], 'string'],
            [['page'], 'default', 'value' => 1],
            [['limit'], 'default', 'value' => 20],
            [['status'], 'default', 'value' => -1]
        ];
    }

    /**
     * @return array
     * 获取云市场上的模板列表
     */
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $params = [
                'page' => $this->page - 1,
                'page_size' => $this->limit
            ];
            if ($this->keyword !== '' && $this->keyword !== null) {
                $params['keyword'] = $this->keyword;
            }
            $params['type'] = 'diy';
            $templatePermission = \Yii::$app->role->getTemplate();
            if (\Yii::$app->role->isSuperAdmin) {
                if ($this->status !== -1) {
                    $params['is_buy'] = $this->status;
                }
            } else {
                $common = CommonTemplateCenter::getInstance();
                switch ($this->status) {
                    case 1:
                        $templateId = $common->getUseTemplate($templatePermission);
                        break;
                    case 0:
                        $showList = $common->getShowTemplate($templatePermission);
                        $useList = $common->getUseTemplate($templatePermission);
                        $templateId = array_diff($showList, $useList);
                        break;
                    default:
                        $templateId = $common->getShowTemplate($templatePermission);
                }
                if (!empty($templateId)) {
                    $params['id'] = urldecode(json_encode($templateId, JSON_UNESCAPED_UNICODE));
                }
                $params['dont_validate_domain'] = 1;
            }
            // 模板市场中的模板
            $res['pagination'] = null;
            $list = CloudTemplate::find()->where(['>', 'id', 0])->orderBy(['id' => SORT_DESC])->page($res['pagination'], 16, $this->page)->all();
            $list = ArrayHelper::toArray($list);
            foreach ($list as $key => $item) {
                $list[$key]['pics'] = json_decode($item['pics']);
                $list[$key]['order']['is_pay'] = 1;
                $list[$key]['template_type'] = ['page','module'];
            }
            // 已购买的模板
            /** @var CoreTemplate[] $coreTemplateList */
            $coreTemplateList = CoreTemplate::find()->with(['edit', 'templateType'])
                ->where([
                    'template_id' => array_column($list, 'id'),
                    'is_delete' => 0
                ])->all();
            $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
            $hasDiy = false;
            if (in_array('diy', $permission)) {
                $hasDiy = true;
            }
            foreach ($list as &$item) {
                $item['is_use'] = false;
                $item['is_show'] = 1;
                if (\Yii::$app->role->isSuperAdmin) {
                    $use = isset($item['order']) ? $item['order']['is_pay'] : false;
                } else {
                    $use = true;
                    $item['order'] = '';
                    $item['is_show'] = 0;
                }
                $item['cloud_name'] = $item['name'];
                $item['cloud_price'] = $item['price'];
                foreach ($coreTemplateList as $coreTemplate) {
                    if ($coreTemplate->template_id == $item['id']) {
                        $item['use'] = $use;
                        $item['is_use'] = $item['type'] == 'diy' ? ($hasDiy && $use) : $use;
                        if (!$templatePermission['use_all'] && !in_array($coreTemplate->template_id, $templatePermission['use_list'])) {
                            $item['is_use'] = false;
                        }
                        $template = [
                            'data' => true,
                        ];
                        if (!version_compare($coreTemplate->version, $item['version'], '=')) {
                            $template['is_update'] = true;
                        }
                        $item['template'] = $template;
                        if ($coreTemplate->edit) {
                            $item['name'] = $coreTemplate->edit->name;
                            $item['price'] = $coreTemplate->edit->price;
                        }
                        if ($coreTemplate->templateType) {
                            $item['template_type'] = array_column($coreTemplate->templateType, 'type');
                        }
                        break;
                    }
                }
            }
            unset($item);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $list,
                    'pagination' => $res['pagination']
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array
     * 获取商城
     */
    public function getMall()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $search = json_decode($this->search, true);
            $list = Mall::find()->where(['is_delete' => 0, 'is_recycle' => 0, 'is_disable' => 0])
                ->keyword($search['keyword'], ['like', 'name', $search['keyword']])
                ->page($pagination, 10, $this->page)
                ->select('id,name')
                ->all();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * @return array
     * 获取指定商城的diy模板
     */
    public function getTemplate()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $plugin = \Yii::$app->plugin->getPlugin('diy');
            $data = $plugin->getTemplate($this->mall_id);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $data
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * @return array
     * 发布模板
     */
    public function issue()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            switch ($this->type) {
                case 'diy':
                    $template = $this->diyTemplate();
                    break;
//                case 'home':
//                    $template = $this->home();
//                    break;
                default:
                    throw new \Exception('无效的请求');
            }
            $issue = new Issue();
            $issue->type = 'encode';
            $url = $issue->encode($template['data']);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $url
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \Exception
     * 获取需要导出的diy模板
     *
     */
    private function diyTemplate()
    {
        $plugin = \Yii::$app->plugin->getPlugin('diy');
        if (!method_exists($plugin, 'getTemplateOne')) {
            throw new \Exception('请更新diy插件');
        }
        $data = $plugin->getTemplateOne($this->mall_id, $this->template_id);
        if (!$data) {
            throw new \Exception('无效的模板选择');
        }
        return [
            'data' => json_decode($data->data, true),
            'name' => $data->name
        ];
    }

    private function home()
    {
        $option = CommonOption::get(
            Option::NAME_HOME_PAGE,
            $this->mall_id,
            Option::GROUP_APP,
            (new HomePageForm())->getDefault()
        );
        $dataList = [];
        foreach ($option as $item) {
            $key = $item['key'] . '-' . $item['relation_id'];
            if (isset($dataList[$key])) {
                continue;
            }
            $data = [];
            switch ($item['key']) {
                case 'banner':
                    $bannerId = MallBannerRelation::find()->select('banner_id')
                        ->where(['is_delete' => 0, 'mall_id' => $this->mall_id]);
                    $data = Banner::find()->where(['mall_id' => $this->mall_id, 'is_delete' => 0, 'id' => $bannerId])
                        ->select('pic_url,title,page_url,open_type,params')->asArray()->all();
                    break;
                case 'home_nav':
                    $data = HomeNav::find()->where([
                        'mall_id' => $this->mall_id,
                        'status' => 1,
                        'is_delete' => 0,
                    ])->orderBy(['sort' => SORT_ASC])
                        ->select('name,url,icon_url,sort,params,open_type,status')
                        ->asArray()->all();
                    break;
                case 'block':
                    $data = HomeBlock::find()->select('name,value,type')
                        ->where(['mall_id' => $this->mall_id, 'id' => $item['relation_id'], 'is_delete' => 0])
                        ->asArray()->one();
                    $data['value'] = json_decode($data['value'], true);
                    break;
                default:
                    break;
            }
            if (!empty($data)) {
                $dataList[$key] = $data;
            }
        }
        return [
            'data' => [
                'list' => $option,
                'model' => $dataList
            ],
            'name' => '首页布局' . rand(1, 999)
        ];
    }

    /**
     * @return array
     * 安装云市场上的模板到本地服务器
     */
    public function install()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $detail = CloudTemplate::findOne([
                'id' => $this->template_id
            ]);
            $detail = ArrayHelper::toArray($detail);
            $detail['order']['order_no'] = 'm3BcWpctVjRRQoWgfEzFIQ==';
            $detail['user']['name'] = '匿名';
            //$detail['template_type'] = ['page','module'];
            $data = $detail;
            $coreTemplate = CoreTemplate::findOne([
                'template_id' => $this->template_id,
                'is_delete' => 0
            ]);
            if ($coreTemplate && version_compare($coreTemplate->version, $detail['version'], '=')) {
                throw new \Exception('已安装模板，无需重复安装');
            }
            $issue = new Issue();
            $issue->type = 'decode';
            $list = $issue->decode($data['package']);

            if (!$coreTemplate) {
                $coreTemplate = new CoreTemplate();
                $coreTemplate->is_delete = 0;
                $coreTemplate->template_id = $detail['id'];
                $coreTemplate->order_no = $detail['order']['order_no'];
                $coreTemplate->type = $detail['type'];
            }
            $coreTemplate->name = $detail['name'];
            $coreTemplate->data = json_encode($list, JSON_UNESCAPED_UNICODE);
            $coreTemplate->price = $detail['price'];
            $coreTemplate->detail = $detail['detail'];
            $coreTemplate->version = $detail['version'];
            $coreTemplate->author = $detail['user']['name'];
            $coreTemplate->pics = json_encode($detail['pics'], JSON_UNESCAPED_UNICODE);
            if (!$coreTemplate->save()) {
                throw new \Exception($this->getErrorMsg($coreTemplate));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '安装完成',
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * @return array
     * 向云市场发起模板购买生成订单
     */
    public function buy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $plugin = new Plugin();
            $res = \Yii::$app->cloud->template->createOrder([
                'id' => $this->template_id,
                'diy_version' => $plugin->getVersionFileContent()
            ]);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '下单成功，订单号为：' . $res['order_no']
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * @return array
     * 向云市场发起模板购买生成订单
     */
    public function pay()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $res = \Yii::$app->cloud->template->getDetail([
                'id' => $this->template_id
            ]);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $res
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
