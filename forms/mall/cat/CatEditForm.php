<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\cat;


use app\core\response\ApiCode;
use app\models\GoodsCats;
use app\models\Model;

class CatEditForm extends Model
{
    public $parent_id;
    public $name;
    public $sort;
    public $pic_url;
    public $big_pic_url;
    public $advert_pic;
    public $advert_url;
    public $status;
    public $is_show;
    public $id;
    public $advert_params;
    public $advert_open_type;

    public function rules()
    {
        return [
            [['name', 'sort'], 'required'],
            [['parent_id', 'id', 'is_show'], 'integer'],
            [['advert_open_type'], 'string', 'max' => 65],
            [['advert_params'], 'trim'],
            [['pic_url', 'big_pic_url', 'advert_pic', 'advert_url', 'status'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'parent_id' => '父级分类ID',
            'name' => '分类名称',
            'sort' => '排序',
            'pic_url' => '分类图标',
            'big_pic_url' => '分类大图标',
            'advert_pic' => '广告图',
            'advert_url' => '跳转链接',
            'status' => '分类状态',
            'is_show' => '显示状态',
            'advert_open_type' => '打开方式',
            'advert_params' => '导航参数',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->id) {
                $cat = GoodsCats::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'id' => $this->id
                ]);

                if (!$cat) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '数据异常,该条数据不存在',
                    ];
                }
            } else {
                $cat = new GoodsCats();
                $cat->mall_id = \Yii::$app->mall->id;
                $cat->mch_id = \Yii::$app->user->identity->mch_id;
                $cat->parent_id = $this->parent_id ? $this->parent_id : 0;
            }

            $cat->name = $this->name;
            $cat->sort = $this->sort ? $this->sort : 100;
            $cat->pic_url = $this->pic_url;
            $cat->big_pic_url = $this->big_pic_url;
            $cat->advert_pic = $this->advert_pic;
            $cat->advert_url = $this->advert_url;
            $cat->status = $this->status;
            $cat->is_show = $this->is_show;
            $cat->advert_open_type = $this->advert_open_type;
            $cat->advert_params = \Yii::$app->serializer->encode($this->advert_params);
            $res = $cat->save();

            if ($res) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功',
                ];
            }

            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '保存失败',
            ];


        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
