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
use yii\db\Exception;

class TemplateEditForm extends Model
{
    use HttpForm;
    public $ids;
    public $store_id;
    public $cat_id;

    public function rules()
    {
        return [
            [[ 'store_id'], 'required'],
        ];
    }


    public function getPage(){


        $store = CopyStore::findOne($this->store_id);


        $page = $this->getHomePage($store->store_id,$store->url,$store->ver);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => [$page],
            ]
        ];
    }


    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $store = CopyStore::findOne($this->store_id);

        if(!$store){
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '门店不存在',
            ];
        }
        try{
            $page = $this->getHomePage($store->store_id,$store->url,$store->ver);
            if(isset($page['navs'])){
                foreach ($page['navs'] as $t){

                    $form = new \app\plugins\diy\forms\mall\TemplateEditForm();
                    $temp = $this->setTemp($t['template']['data']);
                    $param = [
                        "name" => $t['template']['name'],
                        "data" => $temp,
                    ];
                    $form->attributes = $param;
                    $res = $form->save();
                    if(!isset($res['code']) || $res['code'] != 0){
                        throw new Exception("导入失败");
                    }
                }

            }

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $page,
            ]
        ];


    }




    public function setTemp($arr)
    {
        $attrs = [];
        foreach($arr as $item){
            if(!in_array($item['id'],["goods",'integral-mall','miaosha','booking','pintuan','store'])){
                $attrs[] = $item;
            }
        }
        $str = json_encode($attrs);
//        echo $str;die;
        $str = str_replace("\/","/",$str);
//        echo $str;die;
        $reg = '/((http|https):\/\/)+(\w+\.)+(\w+)[\w\/\.\-]*(jpg|gif|png)/';
        $matches = array();
        preg_match_all($reg, $str, $matches);

        $json_str = json_encode($arr);
        $json_str = str_replace("\/","/",$json_str);
        foreach ($matches[0] as $value) {
            $imaUrl = $this->replaceImageUrl($value);
            $json_str = str_replace($value,$imaUrl,$json_str);
        }
        return $json_str;
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


}
