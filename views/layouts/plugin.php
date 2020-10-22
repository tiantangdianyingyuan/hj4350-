<?php
/**
 * @var \yii\web\View $this
 * @var string $content
 */
$isAdmin = false;
$isSuperAdmin = false;
if (!Yii::$app->user->isGuest) {
    /** @var \app\models\User $user */
    $user = Yii::$app->user->identity;
    if ($user->identity && $user->identity->is_super_admin == 1) {
        $isAdmin = true;
        $isSuperAdmin = true;
    }
    if ($user->identity && $user->identity->is_admin == 1) {
        $isAdmin = true;
    }
}
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
    <title><?= \Yii::$app->plugin->currentPlugin->getDisplayName() ? \Yii::$app->plugin->currentPlugin->getDisplayName() : '商城' ?></title>
    <link rel="stylesheet" href="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/element-ui@2.12.0/lib/theme-chalk/index.css">
    <link rel="stylesheet" href="<?= Yii::$app->request->baseUrl ?>/statics/css/flex.css">
    <link rel="stylesheet" href="<?= Yii::$app->request->baseUrl ?>/statics/css/common.css">
    <link href="//at.alicdn.com/t/font_353057_1wttvyksgsj.css" rel="stylesheet">
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/jquery@3.3.1/dist/jquery.min.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vue@2.6.10/dist/vue.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/element-ui@2.12.0/lib/index.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/qs@6.5.2/dist/qs.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/axios@0.18.0/dist/axios.min.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vue-line-clamp@1.2.4/dist/vue-line-clamp.umd.js"></script>
    <script>
        let _layout = null;
        let _aside = null;
        const _csrf = '<?=Yii::$app->request->csrfToken?>';
        const _scriptUrl = '<?=Yii::$app->request->scriptUrl?>';
        const _baseUrl = '<?= \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl ?>';
        const _pluginName = '<?= \Yii::$app->plugin->currentPlugin->getName()?>';
        let _isWe7 = <?=is_we7() ? 'true' : 'false'?>;
        let _isInd = <?=is_we7() ? 'false' : 'true'?>;
        let _isAdmin = <?=$isAdmin ? 'true' : 'false'?>;
        let _isSuperAdmin = <?=$isSuperAdmin ? 'true' : 'false'?>;
    </script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/js/common.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/js/dayjs.min.js"></script>
    <style>
        html, body {
            height: 100%;
            padding: 0;
            margin: 0;
        }

        #app {
            height: 100%;
        }

        .el-header {
            padding: 0;
        }

        .left-menu {
            width: 200px;
            height: 100%;
            position: fixed;
            del-top: 60px;
            left: 0;
            overflow: auto;
            z-index: 5;
        }

        #_aside .el-menu {
            border-right: none;
        }

        #_aside .el-submenu .el-menu-item {
            min-width: 0px;
        }

        #_aside .aside-logo {
            background: #464d54;
            color: #f2f2f2;
            cursor: pointer;
            font-weight: bold;
            text-align: center;
            padding: 0 15px;
        }

        #_aside .aside-logo:hover {
            background: #30353a;
            color: #fff;
        }

        #_aside .aside-logo div {
            background: rgba(0, 0, 0, 0.15);
            padding: 6px 6px;
            width: 100%;
            border-radius: 3px;
            margin: 10px 0;
        }

        #_aside .aside-logo img {
            height: calc(40px + 2px);
            width: calc(40px + 2px);
            border-radius: 50%;
            border: 2px solid #ffffff;
            display: block;
            margin-top: 10px;
        }

        .el-container {
            height: 100%;
        }


        [v-cloak] {
            display: none !important;
        }

        .el-dialog {
            min-width: 600px;
        }

        input,textarea,select {
            appearance: none;
            outline: none !important;
            box-shadow: none;
        }

        /* https://github.com/ElemeFE/element/pull/15359 */
        .el-input .el-input__count .el-input__count-inner {
            background: #FFF;
            display: inline-block;
            padding: 0 5px;
            line-height: normal;
        }
    </style>

</head>
<body>
<?php $this->beginBody() ?>
<div id="_layout"></div>
<?= $this->renderFile('@app/views/components/index.php') ?>
<div class="el-container">
    <div id="_aside" class="el-aside" style="width: 200px">
        <div class="aside-logo" @click="$navigate({r:'mall/index/index'})" flex="dir:top main:center cross:center">
            <template v-if="mall">
                <img v-if="mall.mall_logo_pic" :src="mall.mall_logo_pic" alt=""/>
                <div flex="main:center cross:center">{{mall.name}}</div>
            </template>
        </div>
        <el-menu
                class="left-menu"
                v-loading="leftMenuLoading"
                :unique-opened="true"
                :default-active="defaultRoute"
                background-color="#545c64"
                text-color="#fff"
                active-text-color="#ffd04b"
                @open="handleOpen"
                @close="handleClose">
            <template v-if="leftMenus" v-for="leftMenu in leftMenus">
                <el-submenu v-if="leftMenu.children && leftMenu.children.length" :index="leftMenu.id"
                            :key="leftMenu.id">
                    <template slot="title">
                        <i class="el-icon-location"></i><span>{{leftMenu.name}}</span>
                    </template>
                    <el-menu-item-group>
                        <template v-if="leftMenu.children" v-for="cItem1 in leftMenu.children">

                            <el-submenu v-if="cItem1.children" :index="cItem1.id">
                                <template slot="title">{{cItem1.name}}</template>
                                <el-menu-item
                                        v-for="cItem2 in cItem1.children"
                                        :key="cItem2.id"
                                        :index="cItem2.id"
                                        @click="openUrl(cItem2)">
                                    {{cItem2.name}}
                                </el-menu-item>
                            </el-submenu>

                            <el-menu-item @click="openUrl(cItem1)" v-else :index="cItem1.id">
                                {{cItem1.name}}
                            </el-menu-item>
                        </template>
                </el-submenu>
                <el-menu-item v-else @click="openUrl(leftMenu)" :index="leftMenu.id">
                    <i class="el-icon-menu"></i>
                    <span slot="title">{{leftMenu.name}}</span>
                </el-menu-item>
            </template>
        </el-menu>
    </div>
    <div class="el-container is-vertical">
        <?php Yii::$app->loadViewComponent('mall-header', __DIR__); ?>
        <div id="_header">
            <mall-header></mall-header>
        </div>
        <main class="el-main" style="background: #f3f3f3">
            <?= $content ?>
        </main>
    </div>
</div>
<script>
    _layout = new Vue({
        el: '#_layout',
    });
    const _menuCacheKey = '_OPENED_PLUGIN_MENU_ID_OF_' + _pluginName;
    const _openedMenuKey = '_OPENED_PLUGIN_MENU_ID_OF_' + _pluginName;
    _aside = new Vue({
        el: '#_aside',
        data() {
            return {
                mall: null,
                leftMenuLoading: false,
                leftMenus: {},
                defaultRoute: '2',
            };
        },
        created() {
            setInterval(() => {
                this.$request({
                    params: {
                        r: 'keep-alive/index',
                    },
                }).then(e => {
                }).catch(e => {
                });
            }, 1000 * 60 * 5);
        },
        methods: {
            handleOpen() {
            },
            handleClose() {
            },
            getMenus() {
                let data = localStorage.getItem(_menuCacheKey);
                if (data) {
                    try {
                        data = JSON.parse(data);
                    } catch (e) {
                        data = false;
                    }
                }
                if (data && data.menus) {
                    this.leftMenus = data.menus;
                } else {
                    this.leftMenuLoading = true;
                }
                let openedMenuId = localStorage.getItem(_openedMenuKey);
                if (openedMenuId) {
                    this.defaultRoute = openedMenuId;
                }
                let self = this;
                request({
                    params: {
                        r: 'mall/menus/plugin',
                        name: _pluginName,
                    },
                    method: 'post',
                    data: {
                        route: getQuery('r')
                    }
                }).then(e => {
                    localStorage.setItem(_menuCacheKey, JSON.stringify(e.data.data));
                    self.leftMenuLoading = false;
                    self.leftMenus = e.data.data.menus;
                    self.defaultRoute = e.data.data.currentRouteInfo.id;
                }).catch(e => {
                    console.log(e);
                });
            },
            openUrl(item) {
                localStorage.setItem(_openedMenuKey, item.id);
                let args = {
                    r: item.route
                };
                if (item.params) {
                    for (let i in item.params) {
                        args[i] = item.params[i];
                    }
                }
                navigateTo(args);
            }
        },
        mounted: function () {
            this.getMenus();
        }
    });
    new Vue({el: '#_header'});
</script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
