<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/23
 * Time: 17:04
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\goods;


use app\forms\AttachmentUploadForm;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsWarehouse;
use app\models\Model;

class TaobaoCsvForm extends Model
{
    public $excel;
    public $zip;
    public $mch_id;

    public function rules()
    {
        return [
            [['excel'], 'file', 'extensions' => ['excel']],
            [['zip'], 'file', 'extensions' => ['zip']],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        set_time_limit(0);
        if (empty($_FILES) || !isset($_FILES['excel'])) {
            return [
                'code' => 1,
                'msg' => '请上传excel文件'
            ];
        }
        $fileName = $_FILES['excel']['name'];
        $tmpName = $_FILES['excel']['tmp_name'];
        $path = \Yii::$app->basePath . '/web/temp/';
        if (!is_dir($path)) {
            mkdir($path);
        }
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (($ext != 'xlsx') && ($ext != 'xls')) {
            return [
                'code' => 1,
                'msg' => '请上传excel文件'
            ];
        }
        $file = time() . \Yii::$app->mall->id . '.' . $ext;
        $uploadFile = $path . $file;
        $result = move_uploaded_file($tmpName, $uploadFile);
        $tbi = 'temp/image/tbi/' . date('Y') . '/' . date('m') . '/';
        if (!is_dir($tbi)) {
            mkdir($tbi, 0777, true);
        }
        // 物理路径
        $imagePath = \Yii::$app->basePath . '/web/' . $tbi;
        // 网络路径
        $hostInfo = \Yii::$app->request->hostInfo;
        $baseUrl = \Yii::$app->request->baseUrl;
        $tbiPath = $hostInfo . $baseUrl . '/' . $tbi;
        try {
            $this->getZipOriginalsize($_FILES['zip']['tmp_name'], $imagePath);
        } catch (\Exception $e) {
            unlink($uploadFile);
            return [
                'code' => 1,
                'msg' => $e->getMessage()
            ];
        }

        // 读取Excel文件
        $reader = \PHPExcel_IOFactory::createReader(($ext == 'xls' ? 'Excel5' : 'Excel2007'));
        $excel = $reader->load($uploadFile);
        $sheet = $excel->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnCount = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $row = 1;
        $colIndex = [];
        $arr = [];
        while ($row <= $highestRow) {
            $rowValue = array();
            $col = 0;
            while ($col < $highestColumnCount) {
                $rowValue[] = (string)$sheet->getCellByColumnAndRow($col, $row)->getValue();
                ++$col;
            }
            if (count($rowValue) == 0) {
                unlink($uploadFile);
                return [
                    'code' => 1,
                    'msg' => '上传文件内容不符合规范'
                ];
            } else {
                if ($row == 1) {
                } elseif ($row == 2) {
                    $colIndex = array_flip($rowValue);
                } elseif ($row == 3) {
                } else {
                    $newItem = [
                        'title' => $rowValue[$colIndex['title']],
                        'price' => $rowValue[$colIndex['price']],
                        'num' => $rowValue[$colIndex['num']],
                        'description' => $rowValue[$colIndex['description']],
                    ];
                    $picContents = $rowValue[$colIndex['picture']];
                    $allpics = explode(';', $picContents);
                    $pics = array();
                    $optionpics = array();

                    foreach ($allpics as $imgUrl) {
                        if (empty($imgUrl)) {
                            continue;
                        }

                        $picDetail = explode('|', $imgUrl);
                        $picDetail = explode(':', $picDetail[0]);
//                        $imgRootUrl = str_replace('http://', 'https://', $tbiPath . $picDetail[0] . '.png');
                        $imgUrl = $imagePath . $picDetail[0] . '.png';
                        if (@fopen($imgUrl, 'r')) {
                            $form = new AttachmentUploadForm();
                            $form->file = AttachmentUploadForm::getInstanceFromFile($imgUrl);
                            $res = $form->save();
                            if ($res['code'] == 0) {
                                $attachment = $res['data'];
                                $imgRootUrl = $attachment->url;
                            } else {
                                throw new \Exception($res['msg']);
                            }
                            if ($picDetail[1] == 1) {
                                $pics[] = $imgRootUrl;
                            }

                            if ($picDetail[1] == 2) {
                                $optionpics[$picDetail[0]] = $imgRootUrl;
                            }
                        }
                    }

                    $newItem['pics'] = $pics;
                    $res = $this->save($newItem);
                    if ($res) {
                        $arr[] = $res;
                    }
                }
            }
            ++$row;
        }
        $count = count($arr);
        unlink($uploadFile);
        remove_dir($imagePath);
        return [
            'code' => 0,
            'msg' => "共导入{$count}条数据"
        ];
    }

    // 获取字旁文件的内容
    private function getZipOriginalsize($filename, $path)
    {
        if (!file_exists($filename)) {
            throw new \Exception('文件不存在', 1);
        }

        $filename = iconv('utf-8', 'gb2312', $filename);
        $path = iconv('utf-8', 'gb2312', $path);
        $resource = zip_open($filename);

        while ($dir_resource = zip_read($resource)) {
            if (zip_entry_open($resource, $dir_resource)) {
                $file_name = $path . zip_entry_name($dir_resource);
                $file_path = substr($file_name, 0, strrpos($file_name, '/'));

                if (!is_dir($file_path)) {
                    mkdir($file_path);
                    chmod($file_path, 0777);
                }

                if (!is_dir($file_name)) {
                    $file_size = zip_entry_filesize($dir_resource);

                    if ($file_size < 1024 * 1024 * 10) {
                        $file_content = zip_entry_read($dir_resource, $file_size);
                        $ext = strrchr($file_name, '.');

                        if ($ext == '.png') {
                            file_put_contents($file_name, $file_content);
                        } else {
                            if ($ext == '.tbi') {
                                $file_name = substr($file_name, 0, strlen($file_name) - 4);
                                file_put_contents($file_name . '.png', $file_content);
                            }
                        }
                    }
                }

                zip_entry_close($dir_resource);
            }
        }

        zip_close($resource);
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
                'unit' => '件',
                'attr' => [],

                'goods_num' => $list['num'],
                'attrGroups' => [],
                'video_url' => '',
                'status' => 0,
                'use_attr' => 0,
                'member_price' => [],
                'cats' => [],
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
