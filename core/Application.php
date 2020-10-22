<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/7/10
 * Time: 16:28
 */

namespace app\core;


use app\core\offline\Cloud;
use app\core\currency\Currency;
use app\core\exceptions\ClassNotFoundException;
use app\core\express\ExpressFactory;
use app\core\kdOrder\KdOrder;
use app\core\newsms\Sms;
use app\core\payment\Payment;
use app\forms\common\CommonUser;
use app\forms\permission\branch\BaseBranch;
use app\forms\permission\branch\IndBranch;
use app\forms\permission\branch\OfflineBranch;
use app\forms\permission\branch\We7Branch;
use app\forms\permission\role\AdminRole;
use app\forms\permission\role\BaseRole;
use app\forms\permission\role\MchRole;
use app\forms\permission\role\OperatorRole;
use app\forms\permission\role\SuperAdminRole;
use app\handlers\HandlerBase;
use app\handlers\HandlerRegister;
use app\models\Mall;
use app\models\UserIdentity;
use luweiss\Wechat\Wechat;
use yii\base\Module;
use yii\queue\Queue;
use yii\redis\Connection;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Trait Application
 * @package app\core
 * @property \app\core\Plugin $plugin
 * @property Serializer $serializer
 * @property Mall $mall
 * @property integer $mchId
 * @property string $appPlatform
 * @property string $appVersion
 * @property Payment $payment
 * @property Cloud $cloud
 * @property Connection $redis
 * @property Queue $queue
 * @property Queue $queue3
 * @property Currency $currency
 * @property AppMessage $appMessage
 * @property Sms $sms
 * @property Wechat $wechat
 * @property $alipay
 * @property BaseRole $role
 * @property BaseBranch $branch
 */
trait Application
{
    private $mallId;
    protected $mall;
    protected $mchId;
    private $xAppPlatform;
    private $payment;
    private $xCloud;
    private $currency;
    private $kdOrder;
    private $appMessage;
    private $sms;
    private $wechat;
    private $alipay;
    private $role;
    private $branch;
    private $appVersion;
    private $expressTrack;

    protected function setInitParams()
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');
        return $this;
    }


    /**
     * Load .env file
     *
     * @return self
     */
    protected function loadDotEnv()
    {
        try {
            $dotenv = new \Dotenv\Dotenv(dirname(__DIR__));
            $dotenv->load();
        } catch (\Dotenv\Exception\InvalidPathException $ex) {
        }
        return $this;
    }

    /**
     * Define some constants
     *
     * @return self
     */
    protected function defineConstants()
    {
        define_once('IN_IA', true);
        define_once('APP_PLATFORM_WXAPP', 'wxapp');
        define_once('APP_PLATFORM_ALIAPP', 'aliapp');
        define_once('APP_PLATFORM_BDAPP', 'bdapp');
        define_once('APP_PLATFORM_TTAPP', 'ttapp');
        $this->defineEnvConstants(['YII_DEBUG', 'YII_ENV']);
        return $this;
    }

    /**
     * Define some constants via `env()`
     *
     * @param array $names
     * @return self
     */
    protected function defineEnvConstants($names = [])
    {
        foreach ($names as $name) {
            if ((!defined($name)) && ($value = env($name))) {
                define($name, $value);
            }
        }
        return $this;
    }

    /**
     * Enable JSON response if controller returns Array or Object
     */
    protected function enableObjectResponse()
    {
        $this->response->on(
            Response::EVENT_BEFORE_SEND,
            function ($event) {
                /** @var \yii\web\Response $response */
                $response = $event->sender;
                if (is_array($response->data) || is_object($response->data)) {
                    $response->format = \yii\web\Response::FORMAT_JSON;
                }
            }
        );
        return $this;
    }

    /**
     * Enable full error reporting if using debug mode
     *
     * @return self
     */
    protected function enableErrorReporting()
    {
        if (YII_DEBUG) {
            error_reporting(E_ALL);
        } else {
            error_reporting(E_ALL ^ E_NOTICE);
        }
        return $this;
    }

    /**
     * 重写runAction方法使之可运行插件代码
     *
     * @param string $route
     * @param array $params
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidRouteException
     * @throws \Exception
     */
    public function runAction($route, $params = [])
    {
        bcscale(2);//配置BC函数小数精度

        $route = ltrim($route, '/');
        $pattern = '/^plugin\/.*/';
        preg_match($pattern, $route, $matches);
        if ($matches) {
            $originRoute = $matches[0];
            $originRouteArray = mb_split('/', $originRoute);

            $pluginId = !empty($originRouteArray[1]) ? $originRouteArray[1] : null;
            if (!$pluginId) {
                throw new NotFoundHttpException();
            }
            if (!$this->plugin->getInstalledPlugin($pluginId)) {
                throw new NotFoundHttpException();
            }
            $controllerId = 'index';
            $controllerClass = "app\\plugins\\{$pluginId}\\controllers\\IndexController";
            $actionId = 'index';
            $appendNamespace = '';
            for ($i = 2; $i < count($originRouteArray); $i++) {
                $controllerId = !empty($originRouteArray[$i]) ? $originRouteArray[$i] : 'index';
                $controllerName = preg_replace_callback('/\-./', function ($e) {
                    return ucfirst(trim($e[0], '-'));
                }, $controllerId);
                $controllerName = ucfirst($controllerName);
                $controllerName .= 'Controller';
                $controllerClass = "app\\plugins\\{$pluginId}\\controllers\\{$appendNamespace}{$controllerName}";
                $actionId = !empty($originRouteArray[$i + 1]) ? $originRouteArray[$i + 1] : 'index';
                if (class_exists($controllerClass)) {
                    break;
                }
                $appendNamespace .= $originRouteArray[$i] . '\\';
            }

            try {
                /** @var Controller $controller */
                $controller = \Yii::createObject($controllerClass, [$controllerId, $this]);
                $module = new Module($pluginId, $this);
                $controller->module = $module;
                $this->controller = $controller;
                \Yii::$app->plugin->setCurrentPlugin(\Yii::$app->plugin->getPlugin($pluginId));
                return $controller->runAction($actionId, $params);
            } catch (\ReflectionException $e) {
                throw new NotFoundHttpException(\Yii::t('yii', 'Page not found.'), 0, $e);
            }
        }
        return parent::runAction($route, $params);
    }

    /**
     * @return $this
     */
    protected function loadAppHandler()
    {
        $register = new HandlerRegister();
        $HandlerClasses = $register->getHandlers();
        foreach ($HandlerClasses as $HandlerClass) {
            $handler = new $HandlerClass();
            if ($handler instanceof HandlerBase) {
                /** @var HandlerBase $handler */
                $handler->register();
            }
        }
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function loadPluginsHandler()
    {
        if (!\Yii::$app->db->username) {
            return $this;
        }
        $corePluginTableName = \Yii::$app->db->tablePrefix . 'core_plugin';
        if (!table_exists($corePluginTableName)) {
            return $this;
        }
        $corePlugins = \Yii::$app->plugin->list;
        foreach ($corePlugins as $corePlugin) {
            try {
                $plugin = \Yii::$app->plugin->getPlugin($corePlugin->name);
                $plugin->handler();
            } catch (ClassNotFoundException $exception) {
                continue;
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function loadAppLogger()
    {
        return $this;
    }


    public function getMallId()
    {
        return $this->mallId;
    }

    public function setMallId($mallId)
    {
        $this->mallId = $mallId;
    }

    /**
     * @return Mall
     * @throws \Exception
     */
    public function getMall()
    {
        if (!$this->mall || !$this->mall->id) {
            throw new \Exception('mall is Null');
        }
        return $this->mall;
    }


    /**
     * @param Mall $mall
     */
    public function setMall(Mall $mall)
    {
        $this->mall = $mall;
    }

    /**
     * @return mixed
     */
    public function getMchId()
    {
        return $this->mchId ?: 0;
    }

    /**
     * @param $mchId
     */
    public function setMchId($mchId)
    {
        $this->mchId = $mchId;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getAppPlatform()
    {
        if ($this->xAppPlatform) {
            return $this->xAppPlatform;
        }
        if (!empty(\Yii::$app->request->headers['x-app-platform'])) {
            $this->xAppPlatform = \Yii::$app->request->headers['x-app-platform'];
            if ($this->xAppPlatform == 'wx') {
                $this->xAppPlatform = 'wxapp';
            }
        }
        if (!$this->xAppPlatform) {
            $this->xAppPlatform = 'webapp';
        }
        return $this->xAppPlatform;
    }

    public function setAppPlatform($xAppPlatform)
    {
        $this->xAppPlatform = $xAppPlatform;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        if ($this->payment) {
            return $this->payment;
        }
        $this->payment = new Payment();
        return $this->payment;
    }

    /**
     * @return Cloud
     */
    public function getCloud()
    {
        if ($this->xCloud) {
            return $this->xCloud;
        }
        $this->xCloud = new Cloud();
        return $this->xCloud;
    }

    public function getCurrency()
    {
        if ($this->currency) {
            return $this->currency;
        }
        $this->currency = new Currency();
        return $this->currency;
    }

    public function getExpressTrack()
    {
        if ($this->expressTrack) {
            return $this->expressTrack;
        }
        $this->expressTrack = new ExpressFactory();
        return $this->expressTrack;
    }

    public function getKdOrder()
    {
        if ($this->kdOrder) {
            return $this->kdOrder;
        }
        $this->kdOrder = new KdOrder();
        return $this->kdOrder;
    }

    private $loadedViewComponents = [];

    /**
     * 加载vue组件
     * @param string $component 组件id
     * @param string $basePath 文件目录，默认/views/components
     * @param bool $singleLoad 只加载一次
     */
    public function loadViewComponent($component, $basePath = null, $singleLoad = true)
    {
        if (!$basePath) {
            $basePath = \Yii::$app->viewPath . '/components';
        }
        $file = "{$basePath}/{$component}.php";
        if (isset($this->loadedViewComponents[$file]) && $singleLoad) {
            return;
        }
        $this->loadedViewComponents[$file] = true;
        echo $this->getView()->renderFile($file) . "\n";
    }

    /**
     * @return AppMessage
     */
    public function getAppMessage()
    {
        if (!$this->appMessage) {
            $this->appMessage = new AppMessage();
        }
        return $this->appMessage;
    }

    public function getSms()
    {
        if (!$this->sms) {
            $this->sms = new Sms();
        }
        return $this->sms;
    }

    public function getWechat()
    {
        if (!$this->wechat) {
            /** @var \app\plugins\wxapp\Plugin $plugin */
            $plugin = $this->plugin->getPlugin('wxapp');
            $this->wechat = $plugin->getWechat();
        }
        return $this->wechat;
    }

    public function getAlipay()
    {
        throw new \Exception('尚未支持此功能。');
    }

    /**
     * @return BaseRole
     * @throws \Exception
     * 获取登录用户的角色
     */
    public function getRole()
    {
        if (!$this->role) {
            if (\Yii::$app->user->isGuest) {
                throw new \Exception('用户未登录');
            }
            /* @var UserIdentity $userIdentity */
            $userIdentity = CommonUser::getUserIdentity();
            $config = [
                'userIdentity' => $userIdentity,
                'user' => \Yii::$app->user->identity,
                'mall' => \Yii::$app->mall
            ];
            if ($userIdentity->is_super_admin == 1) {
                // 总管理员
                $this->role = new SuperAdminRole($config);
            } elseif ($userIdentity->is_admin == 1) {
                // 子管理员
                $this->role = new AdminRole($config);
            } elseif ($userIdentity->is_operator == 1) {
                // 员工
                $this->role = new OperatorRole($config);
            } elseif (\Yii::$app->user->identity->mch_id > 0) {
                // 商户
                $this->role = new MchRole($config);
            } else {
                throw new \Exception('未知用户权限');
            }
        }
        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    // 获取登录商城的分支版本
    public function getBranch()
    {
        if (!$this->branch) {
            if (is_we7()) {
                $this->branch = new We7Branch();
            } elseif (is_we7_offline()) {
                $this->branch = new OfflineBranch();
            } else {
                $this->branch = new IndBranch();
            }
        }
        return $this->branch;
    }

    public function createForm($class)
    {
        if (!is_string($class)) {
            throw new \Exception("{$class}不是有效的Class");
        }
        return new $class();
    }

    public function getAppVersion()
    {
        if ($this->appVersion) {
            return $this->appVersion;
        }
        if (!empty(\Yii::$app->request->headers['x-app-version'])) {
            $this->appVersion = \Yii::$app->request->headers['x-app-version'];
        }
        if (!$this->appVersion) {
            $this->appVersion = '4.0.0';
        }
        return $this->appVersion;
    }

    public function setAppVersion($appVersion)
    {
        $this->appVersion = $appVersion;
    }
}
