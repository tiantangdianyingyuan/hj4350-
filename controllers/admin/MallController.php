<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/15 9:42
 */


namespace app\controllers\admin;


use app\core\response\ApiCode;
use app\forms\admin\mall\MallCopyrightForm;
use app\forms\admin\mall\MallCreateForm;
use app\forms\admin\mall\MallForm;
use app\forms\admin\mall\MallRemovalForm;
use app\forms\admin\mall\MallUpdateForm;
use app\forms\common\CommonOption;
use app\forms\common\CommonUser;
use app\models\Mall;
use app\core\Pagination;
use app\models\Option;
use app\models\Order;
use app\models\User;
use app\models\UserInfo;
use yii\db\Query;

class MallController extends AdminController
{
    public function actionIndex($keyword = null, $is_recycle = 0)
    {
        if (\Yii::$app->request->isAjax) {
            $query = Mall::find()->where([
                'is_recycle' => $is_recycle,
                'is_delete' => 0,
            ]);
            /** @var User $user */
            $user = \Yii::$app->user->identity;
            if ($user->identity->is_super_admin != 1) {
                $query->andWhere(['user_id' => $user->id,]);
            }

            // TODO 不知有何作用
            $userId = \Yii::$app->request->get('user_id');
            if ($userId) {
                $query->andWhere(['user_id' => $userId]);
            }
            $keyword = trim($keyword);
            if ($keyword !== null && $keyword !== '') {
                $userIds = User::find()->where(['like', 'username', $keyword])->select('id');
                $query->andWhere([
                    'OR',
                    ['LIKE', 'name', $keyword,],
                    ['user_id' => $userIds]
                ]);
            }

            $count = $query->count();
            $pagination = new Pagination(['totalCount' => $count,]);
            $list = $query
                ->with(['user' => function ($query) {
                    /** @var Query $query */
                    $query->select('id,username,nickname,mobile');
                }])
                ->orderBy('id DESC')
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->asArray()
                ->all();

            foreach ($list as $key => $item) {
                $copyright = CommonOption::get(Option::NAME_COPYRIGHT, $item['id'], Option::GROUP_APP);
                $list[$key]['copyright'] = $copyright;
                $list[$key]['count_data'] = $this->getMallCountData($item['id']);
                if ($item['expired_at'] == '0000-00-00 00:00:00') {
                    $list[$key]['expired_at_text'] = '永久';
                } elseif (strtotime($item['expired_at']) < time()) {
                    $list[$key]['expired_at_text'] = '已过期';
                } else {
                    $list[$key]['expired_at_text'] = $item['expired_at'];
                }
            }
            $adminInfo = CommonUser::getAdminInfo();
            $permission = \Yii::$app->role->permission;
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination,
                    'admin_info' => $adminInfo,
                    'showCopyright' => is_array($permission) ? in_array('copyright', $permission) : $permission
                ],
            ];
        } else {
            return $this->render('index');
        }
    }

    private function getMallCountData($mallId)
    {
        $cacheKey = 'SIMPLE_MALL_DATA_OF_' . $mallId;
        $cacheDuration = 600;
        $data = \Yii::$app->cache->get($cacheKey);
        if ($data) {
            return $data;
        }
        $userCount = User::find()->alias('u')
            ->innerJoin(['ui' => UserInfo::tableName()], 'u.id=ui.user_id')
            ->where([
                'u.mall_id' => $mallId,
                'u.is_delete' => 0,
            ])->count();
        $orderCount = Order::find()->where([
            'mall_id' => $mallId,
            'is_delete' => 0,
        ])->count();
        $data = [
            'user_count' => intval($userCount ? $userCount : 0),
            'order_count' => intval($orderCount ? $orderCount : 0),
        ];
        \Yii::$app->cache->set($cacheKey, $data, $cacheDuration);
        return $data;
    }

    public function actionCreate()
    {
        $form = new MallCreateForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }


    // 加入回收站|移出回收站
    public function actionUpdate()
    {
        $form = new MallUpdateForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    // 进入商城
    public function actionEntry($id)
    {
        return \Yii::$app->createForm('app\forms\admin\mall\MallEntryForm')->entry($id);
    }

    // 迁移小程序
    public function actionRemoval()
    {
        $form = new MallRemovalForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->save());
    }

    // 商城禁用
    public function actionDisable()
    {
        $form = new MallForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->disable());
    }

    // 商城回收站删除
    public function actionDelete()
    {
        $form = new MallForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->delete());
    }

    /**
     * 设置版权
     */
    public function actionSetCopyright()
    {
        $form = new MallCopyrightForm();
        $form->attributes = \Yii::$app->request->post();
        return $form->saveCopyright();
    }
}
