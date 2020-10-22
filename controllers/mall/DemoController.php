<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/29 15:55
 */


namespace app\controllers\mall;


use app\core\Pagination;

class DemoController extends MallController
{
    public function actionIndex($page = 1)
    {
        if (\Yii::$app->request->isAjax) {
            $count = 1000;
            $pagination = new Pagination(['totalCount' => $count,]);
            $list = [];
            for ($i = $pagination->offset; $i < ($pagination->offset + $pagination->limit); $i++) {
                $list[] = [
                    'id' => $i,
                    'name' => '名称：' . $i,
                    'pic' => 'http://wx2.sinaimg.cn/mw690/9612d709gy1fm96udpb5ij20dw0dwdh7.jpg',
                    'data1' => '数据项1数据项1数据项1数据项1数据项1数据项1数据项1数据项1：' . $i,
                    'data2' => '数据项2数据项2数据项2数据项2数据项2：' . $i,
                    'status1' => $i % 2,
                    'status2' => $i % 3,
                ];
            }
            return $this->asJson([
                'code' => 0,
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination,
                    'ofs' => $pagination->offset,
                    'lim' => $pagination->limit,
                ],
            ]);
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
    }
}
