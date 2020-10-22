<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\admin;


use app\controllers\behaviors\SuperAdminFilter;
use app\core\response\ApiCode;
use app\forms\admin\mall\FileForm;
use app\forms\admin\mall\MallOverrunForm;
use app\forms\common\attachment\CommonAttachment;
use app\forms\common\CommonOption;
use app\forms\common\UploadForm;
use app\jobs\RunQueueShJob;
use app\jobs\TestQueueServiceJob;
use app\models\AttachmentStorage;
use app\models\Option;
use yii\web\UploadedFile;

class SettingController extends AdminController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'superAdminFilter' => [
                'class' => SuperAdminFilter::class,
                'safeRoutes' => [
                    'admin/setting/small-routine',
                    'admin/setting/upload-file',
                    'admin/setting/attachment',
                    'admin/setting/attachment-create-storage',
                    'admin/setting/attachment-enable-storage',
                ]
            ],
        ]);
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $setting = \Yii::$app->request->post('setting');
                if (CommonOption::set(Option::NAME_IND_SETTING, $setting)) {
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'msg' => '保存成功。',
                    ];
                } else {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '保存失败。',
                    ];
                }
            } else {
                $setting = CommonOption::get(Option::NAME_IND_SETTING);
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'setting' => $setting,
                    ],
                ];
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionAttachment()
    {
        if (\Yii::$app->request->isAjax) {
            $user = \Yii::$app->user->identity;
            $common = CommonAttachment::getCommon($user);
            $list = $common->getAttachmentList();
            return $this->asJson([
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'storageTypes' => $common->getStorageType()
                ]
            ]);
        } else {
            return $this->render('attachment');
        }
    }

    public function actionAttachmentCreateStorage()
    {
        try {
            $user = \Yii::$app->user->identity;
            $common = CommonAttachment::getCommon($user);
            $data = \Yii::$app->request->post();
            $res = $common->attachmentCreateStorage($data);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function actionAttachmentEnableStorage($id)
    {
        $common = CommonAttachment::getCommon(\Yii::$app->user->identity);
        $common->attachmentEnableStorage($id);
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '操作成功。',
        ]);
    }

    public function actionOverrun()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->post()) {
                $form = new MallOverrunForm();
                $form->form = \Yii::$app->request->post('form');

                return $this->asJson($form->save());
            } else {
                $form = new MallOverrunForm();
                return $this->asJson($form->setting());
            }
        } else {
            return $this->render('overrun');
        }
    }

    public function actionQueueService($action = null, $id = null, $time = null)
    {
        if (\Yii::$app->request->isAjax) {
            if ($action == 'create') {
                try {
                    $time = time();
                    $job = new TestQueueServiceJob();
                    $job->time = $time;
                    $id = \Yii::$app->queue->delay(0)->push($job);
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => [
                            'id' => $id,
                            'time' => $time,
                        ],
                    ];
                } catch (\Exception $exception) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '队列服务测试失败：' . $exception->getMessage(),
                    ];
                }
            }
            if ($action == 'test') {
                $done = \Yii::$app->queue->isDone($id);
                if ($done) {
                    $job = new TestQueueServiceJob();
                    $job->time = intval($time);
                    if (!$job->valid()) {
                        return [
                            'code' => ApiCode::CODE_ERROR,
                            'msg' => '队列服务测试失败：任务似乎已经运行，但没有得到预期结果，请检查redis是否连接正常并且数据正常。',
                        ];
                    } else {
                        return [
                            'code' => ApiCode::CODE_SUCCESS,
                            'data' => [
                                'done' => true,
                            ],
                        ];
                    }
                } else {
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => [
                            'done' => false,
                        ],
                    ];
                }
            }
            if ($action == 'env') {
                $fs = [
                    'proc_open', 'proc_get_status', 'proc_close', 'proc_terminate', 'proc_nice',
                    'pcntl_fork', 'pcntl_waitpid', 'pcntl_wait', 'pcntl_signal', 'pcntl_signal_dispatch',
                    'pcntl_wifexited', 'pcntl_wifstopped', 'pcntl_wifsignaled', 'pcntl_wexitstatus',
                    'pcntl_wifcontinued', 'pcntl_wtermsig', 'pcntl_wstopsig', 'pcntl_exec', 'pcntl_alarm',
                    'pcntl_get_last_error', 'pcntl_errno', 'pcntl_strerror', 'pcntl_getpriority', 'pcntl_setpriority',
                    'pcntl_sigprocmask', 'pcntl_async_signals', 'pcntl_signal_get_handler',
                    // 'pcntl_sigwaitinfo', 'pcntl_sigtimedwait',
                ];
                $notExistsFs = [];
                foreach ($fs as $f) {
                    if (!function_exists($f)) $notExistsFs[] = $f;
                }
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'not_exists_fs' => $notExistsFs,
                    ],
                ];
            }
            if ($action == 'create-queue') {
                try {
                    $time = time();
                    $job = new RunQueueShJob();
                    $job->time = $time;
                    $id = \Yii::$app->queue->delay(0)->push($job);
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => [
                            'id' => $id,
                            'time' => $time,
                        ],
                    ];
                } catch (\Exception $exception) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '队列服务测试失败：' . $exception->getMessage(),
                    ];
                }
            }
            if ($action == 'test3') {
                $done = \Yii::$app->queue->isDone($id);
                if ($done) {
                    $job = new RunQueueShJob();
                    $job->time = intval($time);
                    if (!$job->valid()) {
                        return [
                            'code' => ApiCode::CODE_ERROR,
                            'msg' => '队列服务测试失败：任务似乎已经运行，但没有得到预期结果，请检查redis是否连接正常并且数据正常。',
                        ];
                    } else {
                        return [
                            'code' => ApiCode::CODE_SUCCESS,
                            'data' => [
                                'done' => true,
                            ],
                        ];
                    }
                } else {
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => [
                            'done' => false,
                        ],
                    ];
                }
            }
        } else {
            return $this->render('queue-service');
        }
    }

    public function actionSmallRoutine()
    {
        return $this->render('small-routine');
    }

    // 上传业务域名文件
    public function actionUploadFile($name = 'file')
    {
        $form = new FileForm();
        $form->file = UploadedFile::getInstanceByName($name);
        return $this->asJson($form->save());
    }

    public function actionUploadLogo($name = 'file')
    {
        $form = new UploadForm();
        $form->file = UploadedFile::getInstanceByName($name);
        return $this->asJson($form->save());
    }
}
