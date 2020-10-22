<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/23
 * Time: 17:04
 * @copyright: ©2019 人人禾匠商城
 * @link: 
 */

namespace app\plugins\copy\forms\mall;


use app\core\response\ApiCode;
use app\helpers\CurlHelper;
use app\forms\mall\goods\GoodsEditForm;
use app\models\Model;
use app\plugins\copy\models\CopyStore;
use app\plugins\copy\models\CopyStoreGoods;

class GoodsAddForm extends Model
{
    use HttpForm;
    public $ids;
    public $store_id;
    public $cat_id;

    public function rules()
    {
        return [
            [['ids', 'store_id','cat_id'], 'required'],
        ];
    }

    public function copy()
    {

        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        set_time_limit(0);

        $store = CopyStore::findOne($this->store_id);


        $ids = explode(",",$this->ids);

        if(count($ids) > 20){
            return [
                'code' => 1,
                'msg' => "批量导入不能超过20个商品"
            ];

        }
        $count = 0;
        foreach($ids as $goods_id){
            $goodsDetail = $this->getGoodsDetail($store,$goods_id);
            if($store->ver == 4){
                $res = $this->HandleV4Goods($goodsDetail);
            }else{
                $res = $this->HandleV3Goods($goodsDetail);
            }

            if($res){
                $store_goods = CopyStoreGoods::find()->where([
                    "goods_id" => $goods_id,
                    "store_id" => $store->id,
                ])->one();
                if($store_goods){
                    $store_goods->is_copy =1;
                    $store_goods->save();
                }
                $count++;
            }
        }


        return [
            'code' => 0,
            'msg' => "共导入{$count}条数据"
        ];
    }

    /**
     * 替换内容url
     * @param $content
     * @return mixed
     */
    public function getContentImg($content)
    {

        $content    =    str_replace('\"','"',$content);
        $reg = '/<img (.*?)+src=[\'"](.*?)[\'"]/i';
        preg_match_all( $reg , $content , $results );
        return $results[2];
    }


    /**
     * 网络图片下载本地换取本地图片链接
     * @param $imagUrl
     */
    public function getImages($imagUrl)
    {

        $tbi = 'uploads/image/tbi/' . date('Y') . '/' . date('m') . '/';
        // 物理路径
        $imagePath = \Yii::$app->basePath . '/web/' . $tbi;
        if (!is_dir($imagePath)) {
            mkdir($imagePath,0777,true);
        }
        // 网络路径
        $hostInfo = \Yii::$app->request->hostInfo;
        $baseUrl = \Yii::$app->request->baseUrl;
        $tbiPath = $hostInfo . $baseUrl . '/' . $tbi;

        $picname = md5($imagUrl);
        $imgRootUrl = str_replace('http://', 'http://', $tbiPath . $picname . '.jpg');
        $imgUrl_local = $imagePath . $picname . '.jpg';

        if(!is_file($imgUrl_local)){
            $this->saveImage($imagUrl,$imgUrl_local);
        }

        return $imgRootUrl;

    }





    public function replaceImageUrl($imgUrl){

            $url = $this->getImages($imgUrl);
        return $url ;
    }
    /**
     * 从网上下载图片保存到服务器
     * @param $path
     * @param $image_name
     */
    public function saveImage($path, $image_name)
    {

        try{
            $img = file_get_contents("compress.zlib://".$path);
            $data = file_put_contents($image_name,$img);
        }catch (\Exception $e){

        }

//
//        $imgRootUrl = str_replace('https://', 'http://', $path);
//        $ch = curl_init($path);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
//        $img = curl_exec($ch);
//        curl_close($ch);
//        //$image_name就是要保存到什么路径,默认只写文件名的话保存到根目录
//        $fp = fopen($image_name, 'w');//保存的文件名称用的是链接里面的名称
//        fwrite($fp, $img);
//        fclose($fp);
    }



    public function HandleV4Goods($data){
        $data = $data['goods'];
        $attrGroups = $data['attr_groups'];


        if($data['use_attr'] == 1){
            foreach($attrGroups as &$group){
                foreach($group['attr_list'] as &$attr){
                    if(!empty($attr['pic_url'])){
                        $attr['pic_url'] = $this->replaceImageUrl($attr['pic_url']);
                    }
                }
            }

            if(count($data['attr']) > 0){

                foreach($data['attr'] as $k=>$item){
                    if(!empty($item['pic_url'])){
                        $data['attr'][$k]['pic_url'] = $this->replaceImageUrl($item['pic_url']);
                    }
                }
            }
        }


        //替换内容图片
        $description = $data['detail'];
        $contentImgs = $this->getContentImg($description);
        foreach($contentImgs as $img){
            if (empty($img)) {
                continue;
            }

            $imgRootUrl = $this->replaceImageUrl($img);
            $description =  str_replace($img,$imgRootUrl,$description);
        }

        if(count($data['pic_url'])){
            foreach($data['pic_url'] as $k=>$pic){
                $data['pic_url'][$k]['pic_url'] = $this->replaceImageUrl($pic['pic_url']);
            }
        }


        $params = [
            "attr" => empty($data['attr']) ? []:$data['attr'],
            "cats" => [$this->cat_id],
            "mchCats" => [],
            "cards" => [],
            "services" => [],
            "pic_url" => $data['pic_url'],
            "use_attr" => $data['use_attr'] ? 1:0,
            "goods_num" => $data['goods_num'],
            "status" => 0,
            "unit" => $data['unit'],
            "virtual_sales" => 0,
            "cover_pic" =>   $data['cover_pic'] ? $data['cover_pic'] : '',
            "sort" => 100,
            "accumulative" => 0,
            "confine_count" => -1,
            "confine_order_count" => -1,
            "forehead" => 0,
            "forehead_integral" => 0,
            "forehead_integral_type" => 1,
            "freight_id" => 0,
            "freight" => null,
            "give_integral" => 0,
            "give_integral_type" => 1,
            "individual_share" => 0,
            "is_level" => 1,
            "is_level_alone" => 0,
            "pieces" => 0,
            "share_type" => 0,
            "attr_setting_type" => 0,
            "video_url" => "",
            "is_quick_shop" => 0,
            "is_sell_well" => 0,
            "is_negotiable" => 0,
            "name" => $data['name'],
            "price" => $data['price'],
            "original_price" => $data['original_price'],
            "cost_price" => $data['price'],
            "detail" => $description ? $description : '-',
            "extra" => "",
            "app_share_title" => "333",
            "app_share_pic" => "",
            "is_default_services" => 1,
            "member_price" => [],
            "goods_no" => "",
            "goods_weight" => "",
            "select_attr_groups" => [],
            "goodsWarehouse_attrGroups" => [],
            "share_level_type" => 0,
            "shareLevelList" => [],
            "form" => null,
            "form_id" => 0,
            "attr_default_name" => "",
            "is_area_limit" => 0,
            "area_limit" => [["list" => []]],
            "is_vip_card_goods" => 0
        ];



        $t = \Yii::$app->db->beginTransaction();
        try {

            $form = new GoodsEditForm();
            $form->attributes = $params;
            $form->attrGroups = $attrGroups;
            $res = $form->save();

            if ($res['code'] == 1) {
                throw new \Exception($res['msg']);
            }
            $t->commit();
            return true;
        } catch (\Exception $exception) {
            dd($exception->getMessage());
            $t->rollBack();
            return false;
        }

    }


    public function HandleV3Goods($data)
    {

        $attrGroups = [];
        if($data['use_attr'] == 1){
            $v3Attr_Groups = $data['attr_group_list'];
            $group_attr_list = [];
            foreach($v3Attr_Groups as $k => $g_attr){
                foreach($g_attr['attr_list'] as $j => $attr){
                    $key = $attr['attr_id'];
                    $group_attr_list[$key] = [
                        "k" => $k,
                        "j" => $j,
                        "attr_group_id" => $g_attr['attr_group_id'],
                        "attr_group_name" => $g_attr['attr_group_name'],
                    ];
                }
            }
            $v3AttrList = json_decode($data['attr'],true);
            foreach($v3AttrList as &$v3attr){
                foreach ($v3attr['attr_list'] as &$items){
                    if(!empty($v3attr['pic'])){
                        $v3attr['pic'] = $this->replaceImageUrl($v3attr['pic']);
                    }
                    if(isset($group_attr_list[$items['attr_id']])){

                        $group = $group_attr_list[$items['attr_id']];
                        $items["attr_group_id"] = $group["attr_group_id"];
                        $items["attr_group_name"] = $group["attr_group_name"];
                        if(!empty($v3attr['pic']) && empty($v3Attr_Groups[$group["k"]]["attr_list"][$group["j"]]["pic_url"] )){
                            $v3Attr_Groups[$group["k"]]["attr_list"][$group["j"]]["pic_url"] = $v3attr['pic'];
                        }
                    }
                    $v3attr['stock'] = $v3attr['num'];
                    $v3attr['pic_url'] = $v3attr['pic'];
                    $v3attr['weight'] = "0";
                    $v3attr['shareLevelList'] = [];
                    $v3attr['member_price'] = [];

                }
            }
            $attrGroups = $v3Attr_Groups;
        }

        $allpics = $data['pic_list'];
        $pics = array();

        foreach ($allpics as $imgUrl) {
            if (empty($imgUrl)) {
                continue;
            }
            $imgRootUrl = $this->replaceImageUrl($imgUrl['pic_url']);
            $pics[] = $imgRootUrl;
        }

        //替换内容图片
        $description = $data['detail'];
        $contentImgs = $this->getContentImg($description);
        foreach($contentImgs as $img){
            if (empty($img)) {
                continue;
            }

            $imgRootUrl = $this->replaceImageUrl($img);
            $description =  str_replace($img,$imgRootUrl,$description);
        }
        $picList = [];
        foreach ($pics as $item) {
            $picList[] = [
                'pic_url' => $item
            ];
        }



        $params = [
            "attr" => empty($v3AttrList) ? []:$v3AttrList,
            "cats" => [$this->cat_id],
            "mchCats" => [],
            "cards" => [],
            "services" => [],
            "pic_url" => $picList,
            "use_attr" => $data['use_attr'] ? 1:0,
            "goods_num" => $data['num'],
            "status" => 0,
            "unit" => $data['unit'],
            "virtual_sales" => 0,
            "cover_pic" =>  count($pics) >= 1 ? $pics[0] : '',
            "sort" => 100,
            "accumulative" => 0,
            "confine_count" => -1,
            "confine_order_count" => -1,
            "forehead" => 0,
            "forehead_integral" => 0,
            "forehead_integral_type" => 1,
            "freight_id" => 0,
            "freight" => null,
            "give_integral" => 0,
            "give_integral_type" => 1,
            "individual_share" => 0,
            "is_level" => 1,
            "is_level_alone" => 0,
            "pieces" => 0,
            "share_type" => 0,
            "attr_setting_type" => 0,
            "video_url" => "",
            "is_quick_shop" => 0,
            "is_sell_well" => 0,
            "is_negotiable" => 0,
            "name" => $data['name'],
            "price" => $data['price'],
            "original_price" => $data['price'],
            "cost_price" => $data['price'],
            "detail" => $description ? $description : '-',
            "extra" => "",
            "app_share_title" => "333",
            "app_share_pic" => "",
            "is_default_services" => 1,
            "member_price" => [],
            "goods_no" => "",
            "goods_weight" => "",
            "select_attr_groups" => [],
            "goodsWarehouse_attrGroups" => [],
            "share_level_type" => 0,
            "shareLevelList" => [],
            "form" => null,
            "form_id" => 0,
            "attr_default_name" => "",
            "is_area_limit" => 0,
            "area_limit" => [["list" => []]],
            "is_vip_card_goods" => 0
        ];



        $t = \Yii::$app->db->beginTransaction();
        try {

            $form = new GoodsEditForm();
            $form->attributes = $params;
            $form->attrGroups = $attrGroups;
            $res = $form->save();

            if ($res['code'] == 1) {
                throw new \Exception($res['msg']);
            }
            $t->commit();
            return true;
        } catch (\Exception $exception) {
            dd($exception->getMessage());
            $t->rollBack();
            return false;
        }

    }


    public function addGoods()
    {
        $attrGroups = [
            [
                "attr_group_id" => 1,
                "attr_group_name" => "颜色",
                "attr_list" => [[
                    "attr_id" => 2,
                    "attr_name" => "白",
                    "pic_url" => ""
                ],
                    [
                        "attr_id" => 3,
                        "attr_name" => "黑",
                        "pic_url" => ""
                    ]
                ]
            ],
            [
                "attr_group_id" => 2,
                "attr_group_name" => "大小",
                "attr_list" => [[
                    "attr_id" => 4,
                    "attr_name" => "大",
                    "pic_url" => ""
                ],
                    [
                        "attr_id" => 5,
                        "attr_name" => "小",
                        "pic_url" => ""
                    ]]
            ]];

        $params = [
            "attr" => [
                [
                    "attr_list" =>
                        [
                            [
                                "attr_group_id" => 1,
                                "attr_group_name" => "颜色",
                                "attr_id" => 2,
                                "attr_name" => "白"
                            ],
                            [
                                "attr_group_id" => 2,
                                "attr_group_name" => "大小",
                                "attr_id" => 4,
                                "attr_name" => "大"
                            ]
                        ],
                    "stock" => "2",
                    "price" => "2",
                    "no" => "500",
                    "weight" => "2",
                    "pic_url" => "",
                    "shareLevelList" => [],
                    "member_price" => []
                ],
                [
                    "attr_list" =>
                        [
                            [
                                "attr_group_id" => 1,
                                "attr_group_name" => "颜色",
                                "attr_id" => 3,
                                "attr_name" => "黑"
                            ],
                            [
                                "attr_group_id" => 2,
                                "attr_group_name" => "大小",
                                "attr_id" => 4,
                                "attr_name" => "大"
                            ]
                        ],
                    "stock" => "2",
                    "price" => "2",
                    "no" => "500",
                    "weight" => "2",
                    "pic_url" => "",
                    "shareLevelList" => [],
                    "member_price" => []
                ],
                [
                    "attr_list" => [
                        [
                            "attr_group_id" => 1,
                            "attr_group_name" => "颜色",
                            "attr_id" => 2,
                            "attr_name" => "白"
                        ],
                        [
                            "attr_group_id" => 2,
                            "attr_group_name" => "大小",
                            "attr_id" => 5,
                            "attr_name" => "小"
                        ]
                    ],
                    "stock" => "2",
                    "price" => "2",
                    "no" => "500",
                    "weight" => "2",
                    "pic_url" => "",
                    "shareLevelList" => [],
                    "member_price" => []
                ],
                [
                    "attr_list" => [
                        [
                            "attr_group_id" => 1,
                            "attr_group_name" => "颜色",
                            "attr_id" => 3,
                            "attr_name" => "黑"
                        ],
                        [
                            "attr_group_id" => 2,
                            "attr_group_name" => "大小",
                            "attr_id" => 5,
                            "attr_name" => "小"
                        ]
                    ],
                    "stock" => "2",
                    "price" => "2",
                    "no" => "500",
                    "weight" => "2",
                    "pic_url" => "",
                    "shareLevelList" => [],
                    "member_price" => []
                ]
            ],
            "cats" => ["15"],
            "mchCats" => [],
            "cards" => [],
            "services" => [],
            "pic_url" => [
                [
                    "id" => "55",
                    "pic_url" => "6c62ad22c8f739cd7fdb6c.jpg"
                ]
            ],
            "use_attr" => 1,
            "goods_num" => "3",
            "status" => 0,
            "unit" => "件",
            "virtual_sales" => 0,
            "cover_pic" => "",
            "sort" => 100,
            "accumulative" => 0,
            "confine_count" => -1,
            "confine_order_count" => -1,
            "forehead" => 0,
            "forehead_integral" => 0,
            "forehead_integral_type" => 1,
            "freight_id" => 0,
            "freight" => null,
            "give_integral" => 0,
            "give_integral_type" => 1,
            "individual_share" => 0,
            "is_level" => 1,
            "is_level_alone" => 0,
            "pieces" => 0,
            "share_type" => 0,
            "attr_setting_type" => 0,
            "video_url" => "",
            "is_quick_shop" => 0,
            "is_sell_well" => 0,
            "is_negotiable" => 0,
            "name" => "测试添加商品s",
            "price" => "44",
            "original_price" => "4",
            "cost_price" => "44",
            "detail" => "<p>44</p>",
            "extra" => "",
            "app_share_title" => "333",
            "app_share_pic" => "",
            "is_default_services" => 1,
            "member_price" => [],
            "goods_no" => "",
            "goods_weight" => "",
            "select_attr_groups" => [],
            "goodsWarehouse_attrGroups" => [],
            "share_level_type" => 0,
            "shareLevelList" => [],
            "form" => null,
            "form_id" => 0,
            "attr_default_name" => "",
            "is_area_limit" => 0,
            "area_limit" => [["list" => []]],
            "is_vip_card_goods" => 0
        ];
        $form = new GoodsEditForm();
        $form->attributes = $params;
        $form->attrGroups = $attrGroups;
        return  $form->save();
    }


    private function save($list = [])
    {
        if (count($list) == 0) {
            return false;
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $picList = [];
            foreach ($list['pics'] as $item) {
                $picList[] = [
                    'pic_url' => $item
                ];
            }
            $form = new GoodsEditForm();
            $form->attributes = [
                'name' => $list['title'],
                'price' => $list['price'],
                'original_price' => $list['price'],
                'cost_price' => $list['price'],
                'detail' => $list['description'] ? $list['description'] : '-',
                'cover_pic' => count($list['pics']) >= 1 ? $list['pics'][0] : '',
                'pic_url' => $picList,
                'unit' => $list['unit'],
                'attr' => [],

                'goods_num' => $list['num'],
                'attrGroups' => [],
                'video_url' => '',
                'status' => 0,
                'use_attr' => 0,
                'member_price' => [],
                'cats' => $list['cats'],
                'mchCats' => []
            ];
            $res = $form->save();

            if ($res['code'] == 1) {
                throw new \Exception($res['msg']);
            }
            $t->commit();
            return true;
        } catch (\Exception $exception) {
            $t->rollBack();
            return false;
        }
    }
}
