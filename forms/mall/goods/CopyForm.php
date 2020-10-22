<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/23
 * Time: 11:10
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\goods;


use app\core\response\ApiCode;
use app\models\Model;

class CopyForm extends Model
{
    public $url;

    public function rules()
    {
        return [
            [['url'], 'trim'],
            [['url'], 'string'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if (!$this->url) {
                throw new \Exception('链接不能为空');
            }
            $detail = [];
            if (strpos($this->url, 'taobao.com') || strpos($this->url, 'tmall.com')) {
                $arr = explode('?', $this->url);
                if (count($arr) < 2) {
                    throw new \Exception('链接错误');
                }
                $id = $this->pregSubstr('/(\?id=|&id=)/', '/&/', $this->url);
                $detail = $this->taobao($id[0]);
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'detail' => $detail,
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * @param $start
     * @param $end
     * @param $str
     * @return array
     * 正则截取函数
     */
    private function pregSubstr($start, $end, $str) // 正则截取函数
    {
        $temp = preg_split($start, $str);
        $result = [];
        foreach ($temp as $index => $value) {
            if ($index == 0) {
                continue;
            }
            $content = preg_split($end, $value);
            array_push($result, $content[0]);
        }
        return $result;
    }

    public function taobao($id)
    {
        $res = \Yii::$app->cloud->collect->collect($id);
        $coding = mb_detect_encoding($res, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5', 'ISO-8859-1'));
        $html = mb_convert_encoding($res, 'UTF-8', $coding);
        $html = json_decode($html, true);
        return $this->data($html);
    }

    private function data($data)
    {
        if ($data['ret'][0] != 'SUCCESS::调用成功') {
            throw new \Exception($data['ret'][0]);
        }
        $info = $data['data']['itemInfoModel'];
        $detail = [
            'name' => $info['title'],
            'detail' => isset($data['data']['descInfo']) ? $data['data']['descInfo'] : ""
        ];
        $detail = $this->getPic($detail, $info);
        $detail = $this->getPrice($detail, $data['data']);
        $detail = $this->getAttr($detail, $data['data']['skuModel']);
        return $detail;
    }

    // 根据info获取商品缩略图和商品图片
    private function getPic($detail, $info)
    {
        $picList = $info['picsPath'];
        if (!$picList || empty($picList)) {
            return $detail;
        }
        $detail['cover_pic'] = $picList[0];
        foreach ($picList as $pic) {
            $detail['pic_url'][] = [
                'pic_url' => $pic
            ];
        }
        return $detail;
    }

    // 根据info解析出商品售价和原价
    private function getPrice($detail, $info)
    {
        $apiStack = $info['apiStack'];
        if (empty($apiStack)) {
            return $detail;
        }
        $proDetail = json_decode($apiStack[0]['value'], true);
        $proData = $proDetail['data'];
        $proDetailInfo = $proData['itemInfoModel'];
        foreach ($proDetailInfo['priceUnits'] as $index => $priceUnits) {
            $originalPrice = $priceUnits['price'];
            $originalPriceArr = explode('-', $originalPrice);
            $detail['original_price'] = $originalPriceArr[0];
            if ($index == 0) {
                $detail['price'] = $originalPriceArr[0];
            }
        }
        $detail['virtual_sales'] = isset($proDetailInfo['totalSoldQuantity']) ? $proDetailInfo['totalSoldQuantity'] : 0;
        $detail['goods_stock'] = $proDetailInfo['quantity'];
        $detail['skuModel'] = $proData['skuModel'];
        return $detail;
    }

    // 获取规格相关
    private function getAttr($detail, $skuModel)
    {
        if (!isset($skuModel['skuProps'])) {
            return $detail;
        }
        $skusList = [];
        if (isset($detail['skuModel']['skus'])) {
            $skusList = $detail['skuModel']['skus'];
        }
        $attrGroupList = [];
        foreach ($skuModel['skuProps'] as $index => $value) {
            $attrGroupList[$index]['attr_group_name'] = $value['propName'];
            $attrGroupList[$index]['attr_list'] = [];
            foreach ($value['values'] as $key => $item) {
                $attrGroupList[$index]['attr_list'][$key]['attr_name'] = $item['name'];
                $attrGroupList[$index]['attr_list'][$key]['attr_group_name'] = $value['propName'];
            }
        }
        $detail['attr_groups'] = $attrGroupList;
        $checkedAttrList = $this->getAttrList($skuModel['skuProps'], 0);
        $ppathIdmap = $skuModel['ppathIdmap'];
        $detail['goods_num'] = 0;
        foreach ($checkedAttrList as $index => $value) {
            $num = 0;
            $price = 0;
            if (isset($ppathIdmap[$value['dataId']]) && isset($skusList[$ppathIdmap[$value['dataId']]])) {
                $num = $skusList[$ppathIdmap[$value['dataId']]]['quantity'];
                $price = $skusList[$ppathIdmap[$value['dataId']]]['priceUnits'][0]['price'];
            }
            $checkedAttrList[$index]['stock'] = $num;
            $checkedAttrList[$index]['price'] = $price;
            $checkedAttrList[$index]['no'] = '';
            $checkedAttrList[$index]['weight'] = '';
            $detail['goods_num'] += $num;
        }
        $detail['attr'] = $checkedAttrList;
        return $detail;
    }

    private function getAttrList($list, $level, $attrList = [], $n = '', $newList = [])
    {
        if (isset($list[$level]['values'])) {
            foreach ($list[$level]['values'] as $key => $item) {
                $a = [];
                $newAttrList = $attrList;
                $dataId = $n;
                $dataId .= $list[$level]['propId'] . ':' . $item['valueId'];
                $a['attr_group_name'] = $list[$level]['propName'];
                $a['attr_id'] = null;
                $a['attr_name'] = $item['name'];
                array_push($newAttrList, $a);
                if ($level < count($list) - 1) {
                    $newList = $this->getAttrList($list, $level + 1, $newAttrList, $dataId . ';', $newList);
                } else {
                    $newList[] = [
                        'attr_list' => $newAttrList,
                        'dataId' => $dataId,
                        'pic_url' => isset($item['imgUrl']) ? $item['imgUrl'] : ""
                    ];
                }
            }
        }
        return $newList;
    }
}
