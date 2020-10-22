<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/22
 * Time: 10:01
 */

namespace app\forms\admin\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Option;
use yii\helpers\HtmlPurifier;

class MallCopyrightForm extends MallForm
{
    public $description;
    public $link_url;
    public $pic_url;
    public $type;
    public $mobile;
    public $link;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['description', 'link_url', 'pic_url', 'mobile', 'type'], 'trim'],
            [['description', 'link_url', 'pic_url', 'mobile', 'type'], function ($attribute, $params) {
                $this->$attribute = HtmlPurifier::process($this->$attribute);
            }],
            [['link'], 'safe']
        ]);
    }

    public function saveCopyright()
    {
        try {
            $permission = \Yii::$app->role->permission;
            $showCopyright = is_array($permission) ? in_array('copyright', $permission) : $permission;
            if (!$showCopyright) {
                throw new \Exception('无权限修改');
            }
            $mall = $this->getMall();
            $data = $this->getAttributes(['description', 'link_url', 'pic_url', 'mobile', 'type', 'link']);
            $res = CommonOption::set(Option::NAME_COPYRIGHT, $data, $mall->id, Option::GROUP_APP);
            if (!$res) {
                throw new \Exception('保存失败');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功。',
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }
}
