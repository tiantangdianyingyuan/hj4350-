<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/29 17:07
 */


namespace app\forms;


use app\forms\admin\mall\MallOverrunForm;
use app\forms\common\attachment\CommonAttachment;
use app\forms\common\CommonOption;
use app\models\Attachment;
use app\models\AttachmentStorage;
use app\models\Model;
use app\models\Option;
use app\models\User;
use app\models\UserIdentity;
use Grafika\Grafika;
use Grafika\ImageInterface;
use OSS\OssClient;
use Qcloud\Cos\Client;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use yii\web\UploadedFile;
use function GuzzleHttp\Psr7\mimetype_from_filename;

class AttachmentUploadForm extends Model
{
    /** @var UploadedFile */
    public $file;

    public $type;

    public $attachment_group_id;

    private $saveFileFolder;
    private $saveThumbFolder;
    private $saveFileName;

    /** @var AttachmentStorage $storage */
    private $storage;

    private $url;
    private $thumbUrl;

    protected $docExt = ['txt', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'csv', 'pdf', 'md'];
    protected $imageExt = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp',];
    protected $videoExt = ['mp4', 'ogg', 'm4a',];

    public function rules()
    {
        return [
            [['file'], 'file'],
            [['file'], 'validateExt'],
            [['attachment_group_id'], 'integer'],
            [['type'], 'string'],
        ];
    }

    public function validateExt($a, $p)
    {
        $supportExt = array_merge($this->docExt, $this->imageExt, $this->videoExt);
        if (!in_array($this->file->extension, $supportExt)) {
            $this->addError($a, '不支持的文件类型: ' . $this->file->extension);
        }

        $option = CommonOption::get(Option::NAME_OVERRUN, 0, 'admin', (new MallOverrunForm())->getDefault());
        if (in_array($this->file->extension, $this->imageExt)) {
            if (($option['is_img_overrun'] == 'false' || $option['is_img_overrun'] == false) && $this->file->size > ($option['img_overrun'] * 1024 * 1024)) {
                $this->addError($a, '图片大小超出限制,当前大小为: '
                    . (round($this->file->size / 1024 / 1024, 4)) . 'MB,最大限制为:'
                    . $option['img_overrun'] . 'MB');
            }
        }

        if (in_array($this->file->extension, $this->videoExt)) {
            if (($option['is_video_overrun'] == 'false' || $option['is_video_overrun'] == false) && $this->file->size > ($option['video_overrun'] * 1024 * 1024)) {
                $this->addError($a, '视频大小超出限制,当前大小为: '
                    . (round($this->file->size / 1024 / 1024, 4)) . 'MB,最大限制为:'
                    . $option['video_overrun'] . 'MB');
            }
        }
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }

        try {
            $mall = \Yii::$app->mall;
            $mallFolder = "mall{$mall->id}/";
        } catch (\Exception $e) {
            $mall = null;
            $mallFolder = '';
        }

        $dateFolder = date('Ymd');
        $this->saveFileFolder = '/uploads/' . $mallFolder . $dateFolder;
        $this->saveThumbFolder = '/uploads/thumbs/' . $mallFolder . $dateFolder;
        $this->saveFileName = md5_file($this->file->tempName) . '.' . $this->file->getExtension();
        $user = null;
        if (!\Yii::$app->user->isGuest) {
            $user = \Yii::$app->user->identity;
        }
        try {
            $this->storage = CommonAttachment::getCommon($user, $mall)->getAttachment();
        } catch (\Exception $exception) {
            return [
                'code' => 1,
                'msg' => $exception->getMessage(),
            ];
        }
        if (!$this->storage) {
            $this->saveToLocal();
        } else {
            switch ($this->storage->type) {
                case AttachmentStorage::STORAGE_TYPE_LOCAL:
                    $this->saveToLocal();
                    break;
                case AttachmentStorage::STORAGE_TYPE_ALIOSS:
                    $this->saveToAliOss();
                    break;
                case AttachmentStorage::STORAGE_TYPE_TXCOS:
                    $this->saveToTxCos();
                    break;
                case AttachmentStorage::STORAGE_TYPE_QINIU:
                    $this->saveToQiniu();
                    break;
                default:
                    throw new \Exception('未知的存储位置: type=' . $this->storage->type);
                    break;
            }
        }

        $attachment = new Attachment();
        $attachment->storage_id = $this->storage ? $this->storage->id : 0;
        $attachment->user_id = 0;
        $attachment->name = $this->file->name;
        $attachment->size = $this->file->size;

        if ($this->type === 'image') {
            $attachment->type = 1;
        } elseif ($this->type === 'video') {
            $attachment->type = 2;
        } else {
            if (in_array($this->file->extension, $this->imageExt)) {
                $attachment->type = 1;
            } elseif (in_array($this->file->extension, $this->videoExt)) {
                $attachment->type = 2;
            } elseif (in_array($this->file->extension, $this->docExt)) {
                $attachment->type = 3;
            } else {
                $attachment->type = 0;
            }
        }
        $attachment->is_delete = 0;
        $attachment->url = $this->url;
        $attachment->thumb_url = $this->thumbUrl;
        $attachment->attachment_group_id = $this->attachment_group_id ? $this->attachment_group_id : 0;

        if (!\Yii::$app->user->isGuest) {
            /** @var User $user */
            $user = \Yii::$app->user->identity;
            /** @var UserIdentity $userIdentity */
            $userIdentity = $user->identity;
            if ($userIdentity &&
                ($userIdentity->is_super_admin || $userIdentity->is_admin || $userIdentity->is_operator)) {
                $attachment->mall_id = $mall ? $mall->id : 0;
            } elseif (\Yii::$app->mchId && $mall) {
                $attachment->mall_id = $mall->id;
            } else {
                $attachment->mall_id = 0;
            }
            $attachment->mch_id = \Yii::$app->mchId ? \Yii::$app->mchId : 0;
        } else {
            $attachment->mall_id = 0;
        }

        if ($attachment->save()) {
            $attachment->thumb_url = $attachment->thumb_url ? $attachment->thumb_url : $attachment->url;
            return [
                'code' => 0,
                'data' => $attachment,
            ];
        } else {
            return $this->getErrorResponse($attachment);
        }
    }

    private function saveToLocal()
    {
        $baseWebPath = \Yii::$app->basePath . '/web';
        $baseWebUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl;
        $saveFile = $baseWebPath . $this->saveFileFolder . '/' . $this->saveFileName;
        $saveThumbFile = $baseWebPath . $this->saveThumbFolder . '/' . $this->saveFileName;
        if (!is_dir($baseWebPath . $this->saveFileFolder)) {
            if (!make_dir($baseWebPath . $this->saveFileFolder)) {
                throw new \Exception('上传失败，无法创建文件夹`'
                    . $baseWebPath
                    . $this->saveFileFolder
                    . '`，请检查目录写入权限。');
            }
        }
        if (!is_dir($baseWebPath . $this->saveThumbFolder)) {
            if (!make_dir($baseWebPath . $this->saveThumbFolder)) {
                throw new \Exception('上传失败，无法创建文件夹`'
                    . $baseWebPath
                    . $this->saveThumbFolder
                    . '`，请检查目录写入权限。');
            }
        }
        if (!$this->file->saveAs($saveFile)) {
            if (!copy($this->file->tempName, $saveFile)) {
                throw new \Exception('文件保存失败，请检查目录写入权限。');
            }
        }
        $this->url = $baseWebUrl . $this->saveFileFolder . '/' . $this->saveFileName;
        try {
            $editor = Grafika::createEditor(get_supported_image_lib());
            /** @var ImageInterface $image */
            $editor->open($image, $saveFile);
            $editor->resizeFit($image, 200, 200);
            $editor->save($image, $saveThumbFile);
            $this->thumbUrl = $baseWebUrl . $this->saveThumbFolder . '/' . $this->saveFileName;
        } catch (\Exception $e) {
            $this->thumbUrl = '';
        }
    }

    public function saveToAliOss()
    {
        $config = \Yii::$app->serializer->decode($this->storage->config);
        $isCName = (!empty($config['is_cname']) && $config['is_cname'] == 1) ? true : false;
        $client = new OssClient($config['access_key'], $config['secret_key'], $config['domain'], $isCName);

        $object = trim($this->saveFileFolder . '/' . $this->saveFileName, '/');
        $client->uploadFile($config['bucket'], $object, $this->file->tempName);
        if (!$isCName) {
            $endpointNameStart = mb_stripos($config['domain'], '://') + 3;
            $urlPrefix = mb_substr($config['domain'], 0, $endpointNameStart)
                . $config['bucket']
                . '.'
                . mb_substr($config['domain'], $endpointNameStart);
        } else {
            $urlPrefix = $config['domain'];
        }
        $this->url = $urlPrefix . $this->saveFileFolder . '/' . $this->saveFileName;
        if (in_array($this->file->extension, $this->imageExt) && isset($config['style_api']) && $config['style_api']) {
            $this->url = $this->url . $config['style_api'];
        }
        $this->thumbUrl = $this->url;
    }

    public function saveToTxCos()
    {
        $config = \Yii::$app->serializer->decode($this->storage->config);
        $client = new Client([
            'region' => $config['region'],
            'credentials' => [
                'secretId' => $config['secret_id'],
                'secretKey' => $config['secret_key'],
            ],
        ]);

        $key = trim($this->saveFileFolder . '/' . $this->saveFileName, '/');
        /** @var \Guzzle\Service\Resource\Model $result */
        $result = $client->putObject([
            'Bucket' => $config['bucket'],
            'Key' => $key,
            'Body' => fopen($this->file->tempName, 'rb'),
        ]);
        if (!empty($config['domain'])) {
            $this->url = trim($config['domain'], ' /') . '/' . $key;
        } else {
            $this->url = urldecode($result->get('ObjectURL'));
        }
        $this->thumbUrl = $this->url;
    }

    public function saveToQiniu()
    {
        $config = \Yii::$app->serializer->decode($this->storage->config);
        $uploadManager = new UploadManager();
        $auth = new Auth($config['access_key'], $config['secret_key']);
        $token = $auth->uploadToken($config['bucket']);

        $key = trim($this->saveFileFolder . '/' . $this->saveFileName, '/');
        list($result, $error) = $uploadManager->putFile(
            $token,
            $key,
            $this->file->tempName
        );
        $this->url = $config['domain'] . '/' . $result['key'];
        if (in_array($this->file->extension, $this->imageExt) && isset($config['style_api']) && $config['style_api']) {
            $this->url = $this->url . $config['style_api'];
        }
        $this->thumbUrl = $this->url;
    }

    public static function getInstanceFromFile($localFilePath)
    {
        if (!is_string($localFilePath)) {
            throw new \Exception('文件名称不是字符串。');
        }
        if (!file_exists($localFilePath)) {
            throw new \Exception('文件`' . $localFilePath . '`不存在。');
        }
        $localFilePath = str_replace('\\', '/', $localFilePath);
        $pathInfo = pathinfo($localFilePath);
        $name = $pathInfo['basename'];
        $size = filesize($localFilePath);
        $type = mimetype_from_filename($localFilePath);
        return new UploadedFile([
            'name' => $name,
            'type' => $type,
            'tempName' => $localFilePath,
            'error' => 0,
            'size' => $size,
        ]);
    }
}
