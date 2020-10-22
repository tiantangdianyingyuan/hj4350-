<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/14 11:33
 */


namespace app\controllers\common;


use app\controllers\Controller;
use app\core\response\ApiCode;
use app\forms\attachment\GroupUpdateForm;
use app\forms\AttachmentUploadForm;
use app\models\Attachment;
use app\models\AttachmentGroup;
use app\models\Mall;
use app\models\Model;
use app\plugins\mch\models\Mch;
use yii\web\UploadedFile;

class AttachmentController extends Controller
{


    private $xMall;
    private $mchId;

    /**
     * @return null|Mall
     */
    protected function getMall()
    {
        if ($this->xMall) {
            return $this->xMall;
        }
        $id = \Yii::$app->getSessionMallId();
        if (!$id) {
            return null;
        }
        $mall = Mall::findOne(['id' => $id]);
        if (!$mall) {
            return null;
        }
        $this->xMall = $mall;
        return $this->xMall;
    }

    protected function getMchId()
    {
        if ($this->mchId) {
            return $this->mchId;
        }
        $mchId = !\Yii::$app->user->isGuest ? \Yii::$app->user->identity->mch_id : null;
        $this->mchId = $mchId ? $mchId : null;
        return $this->mchId;
    }

    public function actionList($page = 0, $attachment_group_id = null, $type = 'image', $is_recycle = null, $keyword = null)
    {
        $typeMap = [
            'other' => 0,
            'image' => 1,
            'video' => 2,
            'doc' => 3,
        ];
        $mall = $this->getMall();
        if (!$mall) {
            return $this->asJson([
                'code' => ApiCode::CODE_ERROR,
                'data' => 'Mall为空，请刷新页面后重试。'
            ]);
        }
        $query = Attachment::find()->where([
            'mall_id' => $mall->id,
            'is_delete' => 0,
            'type' => $typeMap[$type],
            'mch_id' => $this->getMchId() ? $this->getMchId() : 0,
        ]);

        !is_null($is_recycle) && $query->andWhere(['is_recycle' => $is_recycle]);
        !is_null($keyword) && $query->keyword($keyword, ['like', 'name', $keyword]);
        $attachment_group_id && $query->andWhere(['attachment_group_id' => $attachment_group_id]);

        $list = $query
            ->orderBy('id DESC')
            ->page($pagination)
            ->asArray()
            ->all();

        foreach ($list as &$item) {
            $item['thumb_url'] = $item['thumb_url'] ? $item['thumb_url'] : $item['url'];
        }
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ],
        ]);
    }

    public function actionRename()
    {
        $mall = $this->getMall();
        if ($mall) {
            \Yii::$app->setMall($mall);
        }
        $post = \Yii::$app->request->post();

        $attachment = Attachment::findOne([
            'mall_id' => $mall->id,
            'is_delete' => 0,
            'id' => $post['id'],
            'mch_id' => $this->getMchId() ? $this->getMchId() : 0,
        ]);
        if (!$attachment) {
            throw new \Exception('数据为空');
        }
        $attachment->name = $post['name'];
        $attachment->save();
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功'
        ]);
    }

    public function actionDelete()
    {
        $mall = $this->getMall();
        if (!$mall) {
            return $this->asJson([
                'code' => ApiCode::CODE_ERROR,
                'data' => 'Mall为空，请刷新页面后重试。'
            ]);
        }

        $ids = \Yii::$app->request->post('ids');
        if (!is_array($ids)) {
            return $this->asJson([
                'code' => ApiCode::CODE_ERROR,
                'msg' => '提交数据格式错误。',
            ]);
        }
        switch (\Yii::$app->request->post('type')) {
            case 1:
                $edit = ['is_recycle' => 1];
                break;
            case 2:
                $edit = ['is_recycle' => 0];
                break;
            case 3:
                $edit = ['is_delete' => 1];
                break;
            default:
                $edit = [];
                break;
        }
        Attachment::updateAll($edit, [
            'id' => $ids,
            'mall_id' => $mall->id,
            'mch_id' => $this->getMchId() ? $this->getMchId() : 0,
        ]);
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '操作成功。',
        ]);
    }

    public function actionUpload($name = 'file', $attachment_group_id = null)
    {
        $mall = $this->getMall();
        if ($mall) {
            \Yii::$app->setMall($mall);
        }
        \Yii::$app->setMchId($this->getMchId());

        $form = new AttachmentUploadForm();
        $form->file = UploadedFile::getInstanceByName($name);
        $form->attachment_group_id = $attachment_group_id;
        return $this->asJson($form->save());
    }

    public function actionMove()
    {
        $mall = $this->getMall();
        if (!$mall) {
            return $this->asJson([
                'code' => ApiCode::CODE_ERROR,
                'data' => 'Mall为空，请刷新页面后重试。'
            ]);
        }

        $ids = \Yii::$app->request->post('ids');
        $attachmentGroupId = \Yii::$app->request->post('attachment_group_id');

        $attachmentGroup = AttachmentGroup::findOne([
            'id' => $attachmentGroupId,
            'mall_id' => $mall->id,
            'is_delete' => 0,
            'mch_id' => $this->getMchId() ? $this->getMchId() : 0,
        ]);
        if (!$attachmentGroup) {
            return $this->asJson([
                'code' => ApiCode::CODE_ERROR,
                'data' => '分组不存在，请刷新页面后重试。',
            ]);
        }
        Attachment::updateAll(['attachment_group_id' => $attachmentGroup->id,], [
            'id' => $ids,
            'mall_id' => $mall->id,
        ]);
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '操作成功。',
        ]);
    }

    public function actionGroupList($type = null, $is_recycle = null)
    {
        $mall = $this->getMall();
        if (!$mall) {
            return $this->asJson([
                'code' => 0,
                'data' => [
                    'no_mall' => true,
                    'list' => [],
                ],
            ]);
        }
        $query = AttachmentGroup::find()->where([
            'mall_id' => $mall->id,
            'is_delete' => 0,
            'mch_id' => $this->getMchId() ? $this->getMchId() : 0,
        ]);

        is_null($type) || $query->andWhere(['type' => $type === 'video' ? 1 : 0]);
        is_null($is_recycle) || $query->andWhere(['is_recycle' => $is_recycle]);

        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $query->all(),
            ],
        ]);
    }

    public function actionGroupUpdate()
    {
        $mall = $this->getMall();
        if (!$mall) {
            return $this->asJson([
                'code' => ApiCode::CODE_ERROR,
                'data' => 'Mall为空，请刷新页面后重试。'
            ]);
        }

        $form = new GroupUpdateForm();
        $form->attributes = \Yii::$app->request->post();
        $form->mall_id = $mall->id;
        $form->type = \Yii::$app->request->post('type') == 'video' ? 1 : 0;
        $form->mch_id = $this->getMchId() ? $this->getMchId() : 0;
        return $this->asJson($form->save());
    }

    public function actionGroupDelete()
    {
        $mall = $this->getMall();
        if (!$mall) {
            return $this->asJson([
                'code' => ApiCode::CODE_ERROR,
                'data' => 'Mall为空，请刷新页面后重试。'
            ]);
        }
        $model = AttachmentGroup::findOne([
            'id' => \Yii::$app->request->post('id'),
            'mall_id' => $mall->id,
            'is_delete' => 0,
            'mch_id' => $this->getMchId() ? $this->getMchId() : 0,
        ]);
        if (!$model) {
            return $this->asJson([
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '分组已删除。',
            ]);
        }

        switch (\Yii::$app->request->post('type')) {
            case 1:
                $edit = ['is_recycle' => 1];
                break;
            case 2:
                $edit = ['is_recycle' => 0];
                break;
            case 3:
                $edit = ['is_delete' => 1];
                break;
            default:
                throw new \Exception('TYPE 错误');
                break;
        }
        $model->attributes = $edit;
        if (!$model->save()) {
            return $this->asJson((new Model())->getErrorResponse($model));
        }

        Attachment::updateAll($edit, [
            'attachment_group_id' => $model->id,
            'mall_id' => $mall->id,
            'mch_id' => $this->getMchId() ? $this->getMchId() : 0,
        ]);
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '操作成功。',
        ]);
    }
}
