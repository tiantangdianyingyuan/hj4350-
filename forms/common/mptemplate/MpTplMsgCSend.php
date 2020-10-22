<?php

namespace app\forms\common\mptemplate;

class MpTplMsgCSend extends MpTplMsgSend
{
    public $template_id;
    public $app_id;
    public $app_secret;
    public $admin_open_list;

    /**
     * @param MpTplMsgSend $mp
     * @return array
     */
    public function getInfo(MpTplMsgSend $mp)
    {
        $method = $mp->method;
        $templateMsg = $mp->model->$method([
            'template_id' => $this->template_id,
            'app_id' => '',
        ], $mp->params);
        $templateMsg['app_id'] = $this->app_id;
        $templateMsg['app_secret'] = $this->app_secret;
        $array = [];
        foreach ($this->admin_open_list as $item) {
            array_push($array, ['open_id' => \Yii::$app->serializer->decode($item)->open_id]);
        }
        $templateMsg['admin_open_list'] = $array;
        return $templateMsg;
    }
}