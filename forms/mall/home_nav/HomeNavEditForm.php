<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\home_nav;


use app\core\response\ApiCode;
use app\forms\PickLinkForm;
use app\models\HomeNav;
use app\models\Model;

class HomeNavEditForm extends Model
{
    public $name;
    public $sort;
    public $url;
    public $icon_url;
    public $open_type;
    public $status;
    public $id;
    public $params;
    public $sign;

    public function rules()
    {
        return [
            [['name', 'icon_url', 'status', 'sort', 'url'], 'required'],
            [['icon_url', 'name', 'url', 'sort', 'open_type', 'sign'], 'string'],
            [['id', 'status'], 'integer'],
            [['params'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '名称',
            'sort' => '排序',
            'url' => '导航链接',
            'icon_url' => '导航图标',
            'status' => '是否显示状态',
            'open_type' => '打开方式类型',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->id) {
                $homeNav = HomeNav::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

                if (!$homeNav) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '数据异常,该条数据不存在',
                    ];
                }
            } else {
                $homeNav = new HomeNav();
            }

            $homeNav->name = $this->name;
            $homeNav->mall_id = \Yii::$app->mall->id;
            $homeNav->icon_url = $this->icon_url;
            $homeNav->status = $this->status;
            $homeNav->open_type = $this->open_type ? $this->open_type : PickLinkForm::OPEN_TYPE_2;
            $homeNav->sort = $this->sort;
            $homeNav->params = \Yii::$app->serializer->encode($this->params ? $this->params : []);
            $homeNav->url = $this->url;
            $homeNav->sign = $this->sign ?: '';
            $res = $homeNav->save();

            if (!$res) {
                throw new \Exception('保存失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
