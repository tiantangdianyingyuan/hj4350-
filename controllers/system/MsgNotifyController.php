<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/16 16:11
 */

namespace app\controllers\system;

use app\controllers\Controller;
use app\models\CityService;
use app\models\Mall;
use app\models\Model;
use app\models\OrderDetailExpress;

class MsgNotifyController extends Controller
{
    public function init()
    {
        parent::init();
        $this->enableCsrfValidation = false;
    }

    public function actionCityService()
    {
        // 微信第一次配置时需校验
        if (isset($_GET["echostr"]) && $_GET["echostr"]) {
            return $_GET['echostr'];
        }

        \Yii::warning('同城配送接口回调');
        // php获取json数据方式 file_get_contents('php://input')
        // yii 框架方式
        $json = \Yii::$app->request->rawBody;
        $data = json_decode($json, true);
        \Yii::warning($data);

        $this->updateExpress($data);
        return "success";
    }

    // 更新快递小哥信息
    private function updateExpress($data)
    {
        try {
            $express = OrderDetailExpress::find()->andWhere(['shop_order_id' => $data['shop_order_id']])->one();
            if ($express) {
                // 骑手接单
                if ($data['order_status'] == 102) {
                    $express->city_name = $data['agent']['name'];
                    $express->city_mobile = $data['agent']['phone'];
                }
                $cityInfo = json_decode($express->city_info, true);
                $cityInfo[$data['order_status']] = $data;
                $express->city_info = json_encode($cityInfo, JSON_UNESCAPED_UNICODE);
                $express->status = $data['order_status'];
                $res = $express->save();
                if (!$res) {
                    throw new \Exception((new Model)->getErrorMsg($express));
                }
            }
        } catch (\Exception $exception) {
            \Yii::error('同城配送回调出错');
            \Yii::error($exception);
        }
    }

    public function actionSf()
    {
        \Yii::warning('顺丰接口回调');
        // php获取json数据方式 file_get_contents('php://input')
        // yii 框架方式
        $data = \Yii::$app->request->post();
        \Yii::warning($data);
        $this->updateSfExpress($data);
        $responseData = [
            'error_code' => 0,
            'error_msg' => 'success',
        ];
        \Yii::$app->response->data = $responseData;
    }

    // 更新快递小哥信息
    private function updateSfExpress($data)
    {
        try {
            $express = OrderDetailExpress::find()->andWhere(['shop_order_id' => $data['shop_order_id']])->one();
            if (!$express) {
                throw new \Exception('顺风同城未找到记录');
            }
            $mall = Mall::findOne($express->mall_id);
            if (!$mall) {
                throw new \Exception('未查询到id=' . $express->mall_id . '的商城。 ');
            }
            \Yii::$app->setMall($mall);

            $server = CityService::findOne($express->city_service_id);
            if (!$server) {
                throw new \Exception('配送配置不存在');
            }

            // 骑手接单
            if ($data['order_status'] == 10) {
                $express->city_name = $data['operator_name'];
                $express->city_mobile = $data['operator_phone'];
            }
            $cityInfo = json_decode($express->city_info, true);
            $cityInfo[$this->transCodeBySf($data['order_status'])] = $data;
            $express->city_info = json_encode($cityInfo, JSON_UNESCAPED_UNICODE);
            $express->status = $this->transCodeBySf($data['order_status']);
            $res = $express->save();
            if (!$res) {
                throw new \Exception((new Model)->getErrorMsg($express));
            }

        } catch (\Exception $exception) {
            \Yii::error('同城配送回调出错');
            \Yii::error($exception);
            exit;
        }
    }

    private function transCodeBySf($code)
    {
        switch ($code) {
            case '10':
                return 102;
            case '12':
                return 202;
            case '15':
                return 301;
            case '17':
                return 302;
            default:
                return $code;
        }
    }

    public function actionSs()
    {
        \Yii::warning('闪送接口回调');
        $data = file_get_contents('php://input');
        \Yii::warning($data);
        $data = json_decode($data, true);
        \Yii::warning($data);
        $this->updateSsExpress($data);
    }

    // 更新快递小哥信息
    private function updateSsExpress($data)
    {
        try {
            $express = OrderDetailExpress::find()->andWhere(['shop_order_id' => $data['orderNo']])->one();
            if (!$express) {
                throw new \Exception('闪送同城未找到记录');
            }
            $mall = Mall::findOne($express->mall_id);
            if (!$mall) {
                throw new \Exception('未查询到id=' . $express->mall_id . '的商城。 ');
            }
            \Yii::$app->setMall($mall);

            $server = CityService::findOne($express->city_service_id);
            if (!$server) {
                throw new \Exception('闪送配送配置不存在');
            }

            // 骑手接单
            if ($data['status'] == 30) {
                $express->city_name = $data['courier']['name'];
                $express->city_mobile = $data['courier']['mobile'];
            }
            $cityInfo = json_decode($express->city_info, true);
            $cityInfo[$this->transCodeBySs($data['status'])] = $data;
            $express->city_info = json_encode($cityInfo, JSON_UNESCAPED_UNICODE);
            $express->status = $this->transCodeBySs($data['status']);
            $res = $express->save();
            if (!$res) {
                throw new \Exception((new Model)->getErrorMsg($express));
            }
        } catch (\Exception $exception) {
            \Yii::error('闪送同城配送回调出错');
            \Yii::error($exception);
            exit;
        }
    }

    private function transCodeBySs($code)
    {
        switch ($code) {
            case '30':
                return 102;
            case '40':
                return 202;
            case '50':
                return 302;
            default:
                return $code;
        }
    }

    // 达达接口回调
    public function actionDadaCityService()
    {
        \Yii::warning('达达接口回调');
        $json = \Yii::$app->request->rawBody;
        $data = json_decode($json, true);
        \Yii::warning($data);

        $this->updateDadaExpress($data);
        return "success";
    }

    private function updateDadaExpress($data)
    {
        try {
            $express = OrderDetailExpress::find()->andWhere(['shop_order_id' => $data['order_id']])->one();
            if (!$express) {
                throw new \Exception('达达订单物流不存在');
            }
            $mall = Mall::findOne($express->mall_id);
            if (!$mall) {
                throw new \Exception('未查询到id=' . $express->mall_id . '的商城。 ');
            }
            \Yii::$app->setMall($mall);

            $server = CityService::findOne($express->city_service_id);
            if (!$server) {
                throw new \Exception('配送配置不存在');
            }

            // 骑手接单
            if ($data['order_status'] == 2) {
                $express->city_name = $data['dm_name'];
                $express->city_mobile = $data['dm_mobile'];
            }
            $cityInfo = json_decode($express->city_info, true);
            $cityInfo[$this->transCodeByDada($data['order_status'])] = $data;
            $express->city_info = json_encode($cityInfo, JSON_UNESCAPED_UNICODE);
            $express->status = $this->transCodeByDada($data['order_status']);
            $res = $express->save();
            if (!$res) {
                throw new \Exception((new Model)->getErrorMsg($express));
            }

        } catch (\Exception $exception) {
            \Yii::error('达达配送回调出错');
            \Yii::error($exception);
            exit;
        }
    }

    private function transCodeByDada($code)
    {
        switch ($code) {
            case '2':
                return 102;
            case '3':
                return 202;
            case '4':
                return 302;
            default:
                return $code;
        }
    }

    // 美团接口回调
    public function actionMtCityService()
    {
        \Yii::warning('美团接口回调');
        $json = \Yii::$app->request->rawBody;
        $data = json_decode($json, true);
        \Yii::warning($data);

        // $this->updateMtExpress($data);
        return "success";
    }

    private function updateMtExpress($data)
    {
        try {
            $express = OrderDetailExpress::find()->andWhere(['shop_order_id' => $data['order_id']])->one();
            if (!$express) {
                throw new \Exception('美团订单物流不存在');
            }
            $mall = Mall::findOne($express->mall_id);
            if (!$mall) {
                throw new \Exception('未查询到id=' . $express->mall_id . '的商城。 ');
            }
            \Yii::$app->setMall($mall);

            $server = CityService::findOne($express->city_service_id);
            if (!$server) {
                throw new \Exception('配送配置不存在');
            }

            // 骑手接单
            if ($data['order_status'] == 2) {
                $express->city_name = $data['dm_name'];
                $express->city_mobile = $data['dm_mobile'];
            }
            $cityInfo = json_decode($express->city_info, true);
            $cityInfo[$this->transCodeByMt($data['order_status'])] = $data;
            $express->city_info = json_encode($cityInfo, JSON_UNESCAPED_UNICODE);
            $express->status = $this->transCodeByMt($data['order_status']);
            $res = $express->save();
            if (!$res) {
                throw new \Exception((new Model)->getErrorMsg($express));
            }

        } catch (\Exception $exception) {
            \Yii::error('美团配送回调出错');
            \Yii::error($exception);
            exit;
        }
    }

    private function transCodeByMt($code)
    {
        switch ($code) {
            case '20':
                return 102;
            case '30':
                return 202;
            case '50':
                return 302;
            default:
                return $code;
        }
    }
}
