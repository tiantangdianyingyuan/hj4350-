<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/15
 * Time: 18:00
 */

namespace app\forms\install;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\Option;
use yii\db\Connection;

/**
 * Class InstallForm
 * @package app\forms
 * @property Connection $db;
 */
class InstallForm extends Model
{
    private $db;
    private $tablePrefix = 'zjhj_bd_';
    private $dbErrorCode = [
        2002 => '无法连接数据库，请检查数据库服务器和端口是否正确。',
        1045 => '无法访问数据库，请检查数据库用户和密码是否正确。',
        1049 => '数据库不存在，请检查数据库名称是否正确。',
    ];
    private $redisErrorCode = [
        10060 => '无法连接Redis服务器，请检查Redis服务器或Redis端口是否正确。',
        0 => '无法访问Redis服务器，请检查Redis密码是否正确。',
    ];

    public $db_host;
    public $db_port;
    public $db_username;
    public $db_password;
    public $db_name;
    public $redis_host;
    public $redis_port;
    public $redis_password;
    public $admin_username;
    public $admin_password;

    public function rules()
    {
        return [
            [
                ['db_host', 'db_port', 'db_username', 'db_password', 'db_name', 'admin_username', 'admin_password', 'redis_host', 'redis_port',],
                'trim',
            ],
            [['redis_password'], 'string'],
            [
                ['db_host', 'db_port', 'db_username', 'db_password', 'db_name', 'admin_username', 'admin_password', 'redis_host', 'redis_port',],
                'required',
            ],
        ];
    }

    private function testAndSaveRedis()
    {
        $args = [
            'hostname' => $this->redis_host,
            'port' => $this->redis_port,
            'password' => $this->redis_password ? $this->redis_password : null,
            'connectionTimeout' => 10,
        ];
        try {
            $redis = new \yii\redis\Connection($args);
            $redis->ping();
        } catch (\Exception $exception) {
            if (isset($this->redisErrorCode[$exception->getCode()])) {
                throw new \Exception($this->redisErrorCode[$exception->getCode()]);
            }
            throw $exception;
        }
        $redisForm = new RedisSettingForm();
        $redisForm->attributes = [
            'host' => $this->redis_host,
            'port' => $this->redis_port,
            'password' => $this->redis_password,
        ];
        $result = $redisForm->saveSetting();
        if ($result['code'] !== ApiCode::CODE_SUCCESS) {
            throw new \Exception($result['msg']);
        }
    }

    private function saveConfig()
    {
        $content = <<<EOF
<?php

return [
    'host' => '{$this->db_host}',
    'port' => {$this->db_port},
    'dbname' => '{$this->db_name}',
    'username' => '{$this->db_username}',
    'password' => '{$this->db_password}',
    'tablePrefix' => '{$this->tablePrefix}',
];

EOF;
        if (!file_put_contents($this->getDbConfigFile(), $content)) {
            throw new \Exception('无法写入配置文件，请检查目录写入权限。');
        }
    }

    private function getDbConfigFile()
    {
        return \Yii::$app->basePath . '/config/db.php';
    }

    private function installLock()
    {
        $content = 'install at ' . date('Y-m-d H:i:s') . ' ' . time() . ', ' . \Yii::$app->request->hostInfo;
        file_put_contents(\Yii::$app->basePath . '/install.lock', base64_encode($content));
    }

    private function getDb()
    {
        if (!$this->db) {
            $this->db = new Connection([
                'dsn' => 'mysql:host='
                    . $this->db_host
                    . ';port='
                    . $this->db_port
                    . ';dbname='
                    . $this->db_name,
                'username' => $this->db_username,
                'password' => $this->db_password,
                'tablePrefix' => $this->tablePrefix,
                'charset' => 'utf8mb4',
            ]);
        }
        return $this->db;
    }

    public function install()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $res = $this->getDb()->createCommand('SHOW TABLES LIKE :keyword', [':keyword' => $this->tablePrefix . '%'])
                ->queryAll();
            if ($res) {
                throw new \Exception("已存在表前缀为`{$this->tablePrefix}`的数据表，无法安装。");
            }
            $this->testAndSaveRedis();
        } catch (\Exception $exception) {
            if (isset($this->dbErrorCode[$exception->getCode()])) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $this->dbErrorCode[$exception->getCode()],
                ];
            }
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'data' => [
                    'code' => $exception->getCode(),
                ],
            ];
        }
        $installSql = file_get_contents(__DIR__ . '/install.sql');
        $this->getDb()->createCommand($installSql)->execute();

        // 运行各个版本的升级脚本
        \Yii::$app->setDb($this->getDb());
        $versions = require \Yii::$app->basePath . '/versions.php';
        foreach ($versions as $v => $f) {
            if (version_compare($v, '4.0.0') > 0) {
                if ($f instanceof \Closure) {
                    $f();
                }
            }
        }

        try {
            $password = \Yii::$app->security->generatePasswordHash($this->admin_password);
            $authKey = \Yii::$app->security->generateRandomString();
            $accessToken = \Yii::$app->security->generateRandomString();
            $userSql = <<<EOF
INSERT INTO `{$this->tablePrefix}user`
 (`id`,`mall_id`,`mch_id`,`username`,`password`,`nickname`,`auth_key`,`access_token`,`mobile`)
VALUES (
1,
0,
0,
'{$this->admin_username}',
'{$password}',
'{$this->admin_username}',
'{$authKey}',
'{$accessToken}',
''
);
EOF;
            $this->getDb()->createCommand($userSql)->execute();

            $userIdentitySql = <<<EOF
INSERT INTO `{$this->tablePrefix}user_identity`
 (`user_id`,`is_super_admin`,`is_admin`)
VALUES (
1,
1,
0
);
EOF;
            $this->getDb()->createCommand($userIdentitySql)->execute();

            $adminInfoSql = <<<EOF
INSERT INTO `{$this->tablePrefix}admin_info`
 (`user_id`,`app_max_count`,`permissions`,`remark`,`we7_user_id`)
VALUES (
1,
0,
'[]',
'',
0
);
EOF;
            $this->getDb()->createCommand($adminInfoSql)->execute();

            $versionData = json_decode(file_get_contents(\Yii::$app->basePath . '/version.json'), true);
            $optionVersionValue = \Yii::$app->serializer->encode($versionData['version']);
            $optionVersionKey = Option::NAME_VERSION;
            $versionInfoSql = <<<EOF
INSERT INTO `{$this->tablePrefix}option`
 (`mall_id`,`mch_id`,`name`,`value`)
VALUES (
0,
0,
'{$optionVersionKey}',
'{$optionVersionValue}'
);
EOF;
            $this->getDb()->createCommand($versionInfoSql)->execute();
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '安装失败，' . $exception->getMessage(),
            ];
        }
        $this->saveConfig();
        $this->installLock();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '安装完成。',
        ];
    }
}
