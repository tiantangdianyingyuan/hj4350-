<?php


namespace app\forms\mall\plugin;


use app\core\response\ApiCode;

class PluginCatListForm extends PluginCatBaseForm
{

    public function search()
    {
        $this->baseSearch();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'cats' => $this->cats,
                'other_plugins' => $this->otherPlugins,
            ],
        ];
    }
}
