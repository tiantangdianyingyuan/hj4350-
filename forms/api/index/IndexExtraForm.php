<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/16
 * Time: 13:53
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\index;


use app\core\response\ApiCode;
use app\forms\api\home_page\HomeCatForm;
use app\forms\api\home_page\HomeCouponForm;
use app\models\Model;
use app\plugins\diy\Plugin;
use yii\helpers\ArrayHelper;

class IndexExtraForm extends Model
{
    public $type; // 类型 diy|mall
    public $key; // 组件标示 block|banner|cats|topic...
    public $page_id; // 自定义页面id 默认为0--首页
    public $index; // 组件所在数组下标
    public $nav_index; // 导航栏下标
    public $longitude;
    public $latitude;

    public function rules()
    {
        return [
            [['type', 'key', 'page_id', 'index'], 'required'],
            [['type', 'key'], 'trim'],
            [['type', 'key'], 'string'],
            ['type', 'in', 'range' => ['mall', 'diy']],
            [['page_id', 'index', 'nav_index'], 'integer'],
            [['longitude', 'latitude'], 'number'],
            ['nav_index', 'default', 'value' => 0]
        ];
    }

    public function getData()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $form = new NewIndexForm();
        $form->page_id = $this->page_id;
        $res = $form->getData();
        $homePages = $res['home_pages'];
        try {
            if ($this->type == 'mall') {
                $data = $this->handleData($homePages);
            } else {
                try {
                    /* @var Plugin $plugin */
                    $plugin = \Yii::$app->plugin->getPlugin('diy');
                    $array = $this->attributes;
                    $array['homePages'] = $homePages;
                    $data = $plugin->getIndexExtra($array);
                } catch (\Exception $exception) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => $exception->getMessage()
                    ];
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
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
     * @param $homePages
     * @return array|\yii\db\ActiveRecord[]|null
     * @throws \Exception
     */
    public function handleData($homePages)
    {
        if (!isset($homePages[$this->index]) || $homePages[$this->index]['key'] != $this->key) {
            throw new \Exception('参数值错误，请刷新重试');
        }

        $data = null;
        $homePage = $homePages[$this->index];
        switch ($this->key) {
            case 'cat':
                $homeCatForm = new HomeCatForm();
                $isAllCat = $homePage['relation_id'] == 0;
                $data = $homeCatForm->getCatGoods($homePage['relation_id'], $isAllCat);
                break;
            case 'coupon':
                $homeCouponForm = new HomeCouponForm();
                $data = $homeCouponForm->getCouponList();
                break;
            default:
                $plugin = \Yii::$app->plugin->getPlugin($this->key);
                $data = $plugin->getHomePage('api');
        }
        return $data;
    }
}
