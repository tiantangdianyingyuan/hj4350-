<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/5/16
 * Time: 10:57
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\goods;


use app\core\response\ApiCode;
use app\forms\common\collect\Collect;
use app\models\Model;
use app\models\ModelActiveRecord;

class GoodsCollect extends Model
{
    public $url;
    public $cat_ids;
    public $system_cat_ids;
    public $goods_status;
    public $is_download;

    public function rules()
    {
        return [
            [['cat_ids', 'goods_status', 'url'], 'required'],
            [['url'], 'trim'],
            [['url'], 'string'],
            [['system_cat_ids'], 'safe'],
            ['is_download', 'integer'],
            ['is_download', 'default', 'value' => 1],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        set_time_limit(0);
        // 关闭日志存储
        ModelActiveRecord::$log = false;
        $t = \Yii::$app->db->beginTransaction();
        try {
            if (\Yii::$app->user->identity->mch_id > 0 && (!$this->system_cat_ids || empty($this->system_cat_ids))) {
                throw new \Exception('系统分类不能为空');
            }
            Collect::$is_download = $this->is_download;
            $attributes = Collect::getData($this->url);
            $preg = "/[\'=]|\\\|\"|\|/";
            $attributes['attr'] = array_map(function ($v) use ($preg) {
                $v['attr_list'] = array_map(function ($v1) use ($preg) {
                    $v1['attr_group_name'] = preg_replace($preg, "", $v1['attr_group_name']);
                    $v1['attr_name'] = preg_replace($preg, "", $v1['attr_name']);
                    return $v1;
                }, $v['attr_list']);
                return $v;
            }, $attributes['attr']);
            $attributes['attrGroups'] = array_map(function ($v) use ($preg) {
                $v['attr_group_name'] = preg_replace($preg, "", $v['attr_group_name']);
                $v['attr_list'] = array_map(function ($v1) use ($preg) {
                    $v1['attr_name'] = preg_replace($preg, "", $v1['attr_name']);
                    return $v1;
                }, $v['attr_list']);
                return $v;
            }, $attributes['attrGroups']);
            if ($attributes['goods_num'] > 9999999) {
                $attributes['attr'] = array_map(function ($v) {
                    $v['stock'] = 0;
                    return $v;
                }, $attributes['attr']);
                $attributes['goods_num'] = 0;
            }
            $attributes['type'] = 'goods';
            $form = new GoodsEditForm();
            $form->attributes = $attributes;
            $form->status = $this->goods_status;
            $form->cats = $this->cat_ids;
            $form->mchCats = $this->system_cat_ids ? $this->system_cat_ids : [];
            $form->mch_id = \Yii::$app->user->identity->mch_id;
            $form->is_level = 1;
            $res = $form->save();
            if ($res['code'] == 1) {
                throw new \Exception($res['msg']);
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '采集成功，请到商品管理查看'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }
}
