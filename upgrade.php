<?php
error_reporting(E_ALL & ~E_NOTICE);
defined('IN_IA') or exit('Access Denied');
require __DIR__ . '/vendor/jdorn/sql-formatter/lib/SqlFormatter.php';
ini_set('max_execution_time', 1800);

/***
 * 记录日志
 * @param $content
 * @return bool
 */
function upgrade_log($content)
{
    $logFile = __DIR__ . '/upgrade.log';
    $handle = fopen($logFile, 'a');
    if (!$handle) {
        return false;
    }
    $nowDateTime = date('Y-m-d H:i:s');
    $string = <<<EOF
{$nowDateTime}: 
--------->
{$content}
<---------


EOF;
    $result = fwrite($handle, $string);
    fclose($handle);
    return $result ? true : false;
}

/**
 * 执行SQL
 * @param string $sql 要运行的SQL
 * @param bool $split 自动拆分SQL
 * @param bool $continueOnError 遇到错误继续执行
 * @throws Exception
 */
function sql_execute($sql, $split = true, $continueOnError = true)
{
    if ($split) {
        $list = SqlFormatter::splitQuery($sql);
    } else {
        $list = [$sql];
    }
    foreach ($list as $item) {
        try {
            set_error_handler(function ($errno, $errstr) {
                upgrade_log($errstr);
            });
            pdo_run($item);
            restore_error_handler();
        } catch (Exception $exception) {
            upgrade_log($exception->getMessage());
            if (!$continueOnError) {
                throw $exception;
            }
        }
    }
}

/**
 * 检查数据表是否存在
 * @param $tableName
 * @return bool
 */
function sql_table_exists($tableName)
{
    $sql = "SHOW TABLES LIKE '{$tableName}';";
    $result = pdo_fetch($sql);
    if (is_array($result) && count($result)) {
        return true;
    } else {
        return false;
    }
}

$dbConfigFile = __DIR__ . '/config/db.php';
if (!file_exists($dbConfigFile)) {
    file_put_contents($dbConfigFile, <<<EOF
<?php
function getWe7DbConfig()
{
    require __DIR__ . '/../../../data/config.php';
    if (!empty(\$config['db']['master'])) {
        \$we7DbConfig = \$config['db']['master'];
    } else {
        \$we7DbConfig = \$config['db'];
    }
    return [
        'host' => \$we7DbConfig['host'],
        'port' => \$we7DbConfig['port'],
        'dbname' => \$we7DbConfig['database'],
        'username' => \$we7DbConfig['username'],
        'password' => \$we7DbConfig['password'],
    ];
}

\$we7DbConfig = getWe7DbConfig();
return [
    'host' => \$we7DbConfig['host'],
    'port' => \$we7DbConfig['port'],
    'dbname' => \$we7DbConfig['dbname'],
    'username' => \$we7DbConfig['username'],
    'password' => \$we7DbConfig['password'],
    'tablePrefix' => 'zjhj_bd_',
];

EOF
    );
}

$optionTableName = 'zjhj_bd_option';

try {
    if (sql_table_exists($optionTableName)) {
        $result = pdo_fetch("SELECT * FROM `{$optionTableName}`  WHERE `name`='version' ORDER BY `id` DESC LIMIT 1;");
        if (is_array($result) && count($result)) {
            $currentVersion = isset($result['value']) ? json_decode($result['value']) : null;
        } else {
            $currentVersion = null;
        }
    } else {
        $currentVersion = null;
    }
} catch (Exception $exception) {
    $currentVersion = null;
}
$isNewInstall = $currentVersion ? false : true;
$currentVersion = $currentVersion ? $currentVersion : '0.0.0';
$lastVersion = $currentVersion;

// 运行各个版本的升级脚本
$versions = require __DIR__ . '/versions.php';
foreach ($versions as $v => $f) {
    $lastVersion = $v;
    if (version_compare($v, $currentVersion) > 0) {
        if ($f instanceof \Closure) {
            $f();
        }
    }
}

if (sql_table_exists($optionTableName)) { // 结束，更新数据库标记的版本号
    $nowDateTime = date('Y-m-d H:i:s');
    if ($isNewInstall) {
        $updateVersionSql = <<<EOF
INSERT INTO `{$optionTableName}` (`mall_id`, `mch_id`, `group`, `name`, `value`, `created_at`, `updated_at`) VALUES
(0,	0,	'',	'version',	'\"{$lastVersion}\"',	'{$nowDateTime}',	'{$nowDateTime}');
EOF;
    } else {
        $updateVersionSql = <<<EOF
UPDATE `{$optionTableName}` SET
`value` = '\"{$lastVersion}\"',
`updated_at` = '{$nowDateTime}'
WHERE `name` = 'version';
EOF;
    }
    sql_execute($updateVersionSql);
}
