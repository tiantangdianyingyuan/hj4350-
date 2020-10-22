<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/9
 * Time: 16:46
 */

namespace app\forms\common\convert;

use app\models\Model;
use yii\base\BaseObject;
use yii\db\Connection;

/**
 * Class ConvertBase
 * @package app\forms\common\convert
 * @property Connection $v3Db
 */
class ConvertBase extends BaseObject
{
    private $v3Db;
    private $baseModel;

    public function getV3Db()
    {
        if (!$this->v3Db) {
            if (is_we7()) {
                $config = require __DIR__ . '/../../../../core/config/db.php';
                $this->v3Db = \Yii::createObject($config);
            } else {
                $config = require __DIR__ . '/../../../../core/config/db.php';
                $this->v3Db = \Yii::createObject($config);
            }
        }
        return $this->v3Db;
    }

    protected function convertTime($time = null)
    {
        if ($time === null) {
            $time = time();
        }
        return date('Y-m-d H:i:s', $time);
    }

    protected function getModelError($model)
    {
        if (!$this->baseModel) {
            $this->baseModel = new Model();
        }
        return $this->baseModel->getErrorMsg($model);
    }
}
