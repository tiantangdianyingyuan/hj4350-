<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/26
 * Time: 17:30
 */

namespace app\core;


use yii\base\Component;

class AppMessage extends Component
{
    const EVENT_APP_MESSAGE_REQUEST = 'event_app_message_request';
    const EVENT_TEMPLATE_TEST = 'event_template_test'; // 模板消息测试

    private $dataList;

    public function push($key, $data)
    {
        if (!$this->dataList) {
            $this->dataList = [];
        }
        $this->dataList[$key] = $data;
        return true;
    }

    public function getList()
    {
        return $this->dataList ? $this->dataList : null;
    }
}
