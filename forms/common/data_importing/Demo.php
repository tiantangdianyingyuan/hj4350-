<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/8
 * Time: 11:12
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\data_importing;


use app\models\Model;

/**
 * Class Demo
 * @package app\forms\common\data_importing
 * 注：此为测试入口，模拟数据导入情况，非最终入口
 */
class Demo extends Model
{
    /**
     * @throws \Exception
     */
    public function importing()
    {
        // json文件：键值为v3表名（去除掉前缀的）
        $json = file_get_contents(__DIR__ . '/v3Video.json');
        $array = json_decode($json, true);
        foreach ($array as $key => $value) {
            if ($key == 'wechat_app') {
                // 注：类的命名最好以表名+Importing来进行；例如：导入视频表hjmall_video，则类命名为VideoImporting
                $model = new WechatAppImporting();
                $model->v3Data = $value;
                $model->mall = \Yii::$app->mall;
                $model->import();
            }
        }
    }
}
