<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/29
 * Time: 10:16
 */

namespace app\core\newsms;


use GuzzleHttp\Exception\ClientException;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\Message;
use yii\base\Component;

class ModuleSms extends Component
{

    public $gateways;

    /** @var EasySms $easySms */
    private $easySms;

    public function init()
    {
        parent::init();

        $config = [
            // HTTP 请求的超时时间（秒）
            'timeout' => 5.0,
            // 默认发送配置
            'default' => [
                // 网关调用策略，默认：顺序调用
                'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,
                // 默认可用的发送网关
                'gateways' => ['aliyun',],
            ],
            // 可用的网关配置
            'gateways' => [],
        ];
        if ($this->gateways && is_array($this->gateways)) {
            foreach ($this->gateways as $name => $gateway) {
                $config['gateways'][$name] = $gateway;
            }
        }
        $this->easySms = new EasySms($config);
    }

    /**
     * @param $mobile
     * @param Message $message
     * @throws GatewayErrorException
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
     */
    public function send($mobile, $message)
    {
        try {
            $this->easySms->send($mobile, $message);
        } catch (NoGatewayAvailableException $exception) {
            $es = $exception->getExceptions();
            foreach ($es as $e) {
                /** @var GatewayErrorException $e */
                if (isset($e->raw) && isset($e->raw['Code']) && $e->raw['Code'] == 'isv.MOBILE_NUMBER_ILLEGAL') {
                    throw new GatewayErrorException("无效的号码 {$mobile}", $e->getCode(), $e->raw);
                }
                if ($e instanceof ClientException) {
                    $result = json_decode($e->getResponse()->getBody()->getContents(), true);
                    if ($result && is_array($result) && isset($result['Message']) && isset($result['Code'])) {
                        if ($result['Code'] == 'InvalidAccessKeyId.NotFound') {
                            throw new GatewayErrorException("无效的AccessKeyId", $e->getCode(), $result);
                        }
                        if ($result['Code'] == 'SignatureDoesNotMatch') {
                            throw new GatewayErrorException(
                                "Signature不匹配，请检查AccessKeySecret是否正确",
                                $e->getCode(),
                                $result
                            );
                        }
                        throw new GatewayErrorException($result['Message'], $e->getCode(), $result);
                    }
                }
                throw $e;
            }
        }
    }
}
