<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/14
 * Time: 15:10
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\index;

use app\core\response\ApiCode;
use app\forms\api\home_page\HomeBannerForm;
use app\forms\api\home_page\HomeBlockForm;
use app\forms\api\home_page\HomeNavForm;
use app\forms\api\home_page\HomeTopicForm;
use app\forms\common\CommonAppConfig;
use app\forms\common\video\Video;
use app\models\Model;
use app\plugins\diy\Plugin;
use Yii;

class NewIndexForm extends Model
{
    protected $type;

    public $page_id;

    public function rules()
    {
        return [
            [['page_id'], 'integer']
        ];
    }

    // 获取原始数据
    public function getData()
    {
        try {
            /* @var Plugin $plugin */
            $plugin = Yii::$app->plugin->getPlugin('diy');
            $this->type = 'diy';
            $page = $plugin->getPage($this->page_id);
        } catch (\Exception $exception) {
            Yii::warning('diy页面报错');
            Yii::warning($exception);
            $homePages = CommonAppConfig::getHomePageConfig();
            $homePages[] = [
                'key' => 'fxhb',
                'name' => '裂变红包'
            ];
            $page = $this->getDefault($homePages);
            $this->type = 'mall';
        }
        return [
            'home_pages' => $page,
            'type' => $this->type,
            'time' => date('Y-m-d H:i:s', time())
        ];
    }

    public function getIndex()
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => $this->getData()
        ];
    }

    // 首页布局第一次处理数据
    protected function getDefault($homePages)
    {
        $newList = [];
        $dataIdsList = [];
        $dataList = [];
        // 获取需要查询的组件信息
        foreach ($homePages as $homePageKey => $homePage) {
            switch ($homePage['key']) {
                case 'block':
                    $dataIdsList[$homePage['key']][] = $homePage['relation_id'];
                    break;
                case 'topic':
                case 'home_nav':
                case 'banner':
                    $dataIdsList[$homePage['key']] = [];
                    break;
                default:
            }
        }
        // 统一查询数据
        foreach ($dataIdsList as $key => $item) {
            switch ($key) {
                case 'block':
                    $homeBlockForm = new HomeBlockForm();
                    $dataList[$key] = $homeBlockForm->getBlock($item);
                    break;
                case 'banner':
                    $homeBannerForm = new HomeBannerForm();
                    $dataList[$key] = $homeBannerForm->getBanners();
                    break;
                case 'home_nav':
                    $homeNavForm = new HomeNavForm();
                    $dataList[$key] = $homeNavForm->getHomeNav();
                    break;
                case 'topic':
                    $homeTopicForm = new HomeTopicForm();
                    $dataList[$key] = $homeTopicForm->getTopics();
                    break;
                default:
            }
        }
        // 统一处理数据
        foreach ($homePages as $homePageKey => $homePage) {
            switch ($homePage['key']) {
                case 'block':
                    $homeBlockForm = new HomeBlockForm();
                    $homePage = $homeBlockForm->getNewBlocks($homePage, $dataList[$homePage['key']]);
                    break;
                case 'banner':
                    $homePage['banners'] = $dataList[$homePage['key']];
                    break;
                case 'home_nav':
                    $homePage['home_navs'] = $dataList[$homePage['key']];
                    if (!isset($homePage['row_num'])) {
                        $homePage['row_num'] = 4;
                    }
                    break;
                case 'topic':
                    $homePage['topics'] = $dataList[$homePage['key']];
                    break;
                case 'video':
                    $homePage['video_url'] = Video::getUrl($homePage['video_url']);
                    break;
                case 'fxhb':
                    try {
                        $plugin = \Yii::$app->plugin->getPlugin($homePage['key']);
                        $homePage[$homePage['key']] = $plugin->getHomePage('api');
                    } catch (\Exception $exception) {
                    }
                    break;
                case 'cat':
                    $homePage['relation_id'] = intval($homePage['relation_id']);
                    break;
                default:
            }
            $newList[] = $homePage;
        }
        return $newList;
    }
}
