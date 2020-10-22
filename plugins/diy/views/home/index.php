<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

$_currentPluginBaseUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl(Yii::$app->plugin->currentPlugin->getName());
Yii::$app->loadViewComponent('diy/diy-bg');
?>
<script>
    const _currentPluginBaseUrl = '<?=$_currentPluginBaseUrl?>';
</script>
<?php
$diyPath = \Yii::$app->viewPath . '/components/diy';
$currentDir = opendir($diyPath);
$mallComponents = [];
while (($file = readdir($currentDir)) !== false) {
    if (stripos($file, 'diy-') === 0) {
        $mallComponents[] = substr($file, 4, (stripos($file, '.php') - 4));
    }
}
closedir($currentDir);
foreach ($mallComponents as $component) {
    Yii::$app->loadViewComponent("diy-{$component}", $diyPath);
}
$path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'template';
$currentDir = opendir($path);
$diyComponents = [];
while (($file = readdir($currentDir)) !== false) {
    if (stripos($file, 'diy-') === 0) {
        $temp = substr($file, 4, (stripos($file, '.php') - 4));
        if (!in_array($temp, $mallComponents)) {
            $diyComponents[] = $temp;
        }
    }
}
closedir($currentDir);
foreach ($diyComponents as $component) {
    Yii::$app->loadViewComponent("diy-{$component}", $path);
}
$components = array_merge($diyComponents, $mallComponents);
Yii::$app->loadViewComponent('app-hotspot');
Yii::$app->loadViewComponent('app-rich-text');
Yii::$app->loadViewComponent('app-radio');
?>

<style>
    .mobile-box {
        width: 400px;
        height: calc(800px - 20px);
        padding: 35px 11px;
        background-color: #fff;
        border-radius: 30px;
        background-size: cover;
        position: relative;
        font-size: .85rem;
        float: left;
        margin-right: 1rem;
    }

    .mobile-box .show-box {
        height: calc(667px - 20px);;
        width: 375px;
        overflow: auto;
        font-size: 12px;
        overflow-x: hidden;
    }

    .show-box::-webkit-scrollbar { /*滚动条整体样式*/
        width: 1px; /*高宽分别对应横竖滚动条的尺寸*/
    }

    .account-box > div {
        background-color: #fff;
        border-radius: 4px;
        padding: 8px 0;
        height: 100%;
    }


    .order-bar-box > div {
        background-color: #fff;
        border-radius: 8px;
        height: 100%;
    }


    .mobile-menus-box > div {
        background-color: #fff;
        border-radius: 8px;
        height: 100%;
    }


    .menus-box .menu-item {
        cursor: move;
        background-color: #fff;
        margin: 5px 0;
    }

    .head-bar {
        width: 378px;
        height: 64px;
        position: relative;
        background: url('statics/img/mall/home_block/head.png') center no-repeat;
    }

    .head-bar div {
        position: absolute;
        text-align: center;
        width: 378px;
        font-size: 20px;
        font-weight: 600;
        height: 64px;
        line-height: 88px;
    }

    .head-bar img {
        width: 378px;
        height: 64px;
    }

    .form-body {
        width: 100%;
        height: calc(800px - 20px);
        overflow-y: scroll;
        background: #ffffff;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }
</style>
<style>
    .mobile-framework {
        width: 375px;
        height: 100%;
    }

    .mobile-framework-body {
        min-height: 645px;
        border: 1px solid #e2e2e2;
    }

    .mobile-framework .diy-component-preview {
        cursor: pointer;
        position: relative;
        zoom: 0.5;
        -moz-transform: scale(0.5);
        -moz-transform-origin: top left;
        font-size: 28px;
    }

    @-moz-document url-prefix() {
        .mobile-framework .diy-component-preview {
            cursor: pointer;
            position: relative;
            -moz-transform: scale(0.5);
            -moz-transform-origin: top left;
            font-size: 28px;
            width: 200% !important;
            height: 100%;
            margin-bottom: auto;
        }
        .mobile-framework .active .diy-component-preview {
            border: 2px dashed #409EFF;
            left: -2px;
            right: -2px;
            width: calc(200% + 4px) !important;
        }
    }

    .mobile-framework .diy-component-preview:hover {
        box-shadow: inset 0 0 10000px rgba(0, 0, 0, .03);
    }

    .mobile-framework .diy-component-edit {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 465px;
        right: 0;
        background: #fff;
        padding: 20px;
        display: none;
        overflow: auto;
    }

    .diy-component-options {
        position: relative;
    }

    .diy-component-options .el-button {
        height: 25px;
        line-height: 25px;
        width: 25px;
        padding: 0;
        text-align: center;
        border: none;
        border-radius: 0;
        position: absolute;
        margin-left: 0;
    }

    .mobile-framework .active .diy-component-preview {
        border: 2px dashed #409EFF;
        left: -2px;
        right: -2px;
        width: calc(100% + 4px);
    }

    .mobile-framework div, span {
        -webkit-touch-callout: none; /* iOS Safari */
        -webkit-user-select: none; /* Chrome/Safari/Opera */
        -khtml-user-select: none; /* Konqueror */
        -moz-user-select: none; /* Firefox */
        -ms-user-select: none; /* Internet Explorer/Edge */
        user-select: none; /* Non-prefixed version, currently */
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>DIY首页</span>
        </div>
        <el-form size="small" label-width="150px">
            <div style="display: flex;">
                <div class="mobile-box" v-loading="loading">
                    <div class="head-bar" flex="main:center cross:center">
                        <div>{{templateName ? templateName : '商城首页'}}</div>
                    </div>
                    <div class="show-box">
                        <div v-if="components.length == 0" style="text-align: center;margin-top: 70px">
                            <img src="<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/images/icon_empty.png"
                                 height="40px" width="40px">
                            <div style="color: #999999;padding-top: 10px;font-size: 14px">暂未装修首页</div>
                        </div>
                        <div v-else class="mobile-framework">
                            <div id="mobile-framework-body" class="mobile-framework-body"
                                 :style="'background-color:'+ bg.backgroundColor+';background-image:url('+bg.backgroundPicUrl+');background-size:'+bg.backgroundWidth+'% '+bg.backgroundHeight+'%;background-repeat:'+repeat+';background-position:'+position">

                                <div v-for="(component, index) in components" :key="component.key">
                                    <?php foreach ($components as $component) : ?>
                                        <diy-<?= $component ?> v-if="component.id === '<?= $component ?>'"
                                                               :active="component.active"
                                                               v-model="component.data"
                                        ></diy-<?= $component ?>>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-body">
                    <div style="padding: 30px 50px">
                        <el-button type="primary" @click="navTemplate" plain size="small">装修首页</el-button>
                    </div>
                </div>
            </div>
        </el-form>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,

                allComponents: [],
                overrun: null,
                templateName: '',

                bg: {
                    showImg: false,
                    backgroundColor: '#f5f7f9',
                    backgroundPicUrl: '',
                    position: 5,
                    mode: 1,
                    backgroundHeight: 100,
                    backgroundWidth: 100,
                },
                bgSetting: {
                    showImg: false,
                    backgroundColor: '#f5f7f9',
                    backgroundPicUrl: '',
                    position: 5,
                    mode: 1,
                    backgroundHeight: 100,
                    backgroundWidth: 100,
                    positionText: 'center center',
                    repeatText: 'no-repeat',
                },
                components: [],
                position: 'center center',
                repeat: 'no-repeat',
            };
        },
        methods: {
            navTemplate() {
                this.$navigate({
                    r: 'plugin/diy/mall/template/edit',
                    has_home: 1,
                }, true);
            },
            getHome() {
                this.loading = true;
                this.$request({
                    params: {
                        r: 'plugin/diy/mall/home/index',
                    },
                }).then(response => {
                    this.loading = false;
                    if (response.data.code === 0) {
                        let {allComponents, overrun, name, data} = response.data.data
                        this.allComponents = allComponents;
                        this.overrun = overrun;
                        this.templateName = name;
                        const components = JSON.parse(data);

                        for (let i in components) {
                            components[i].active = false;
                            components[i].key = randomString();
                            console.log(components[i])
                            if (components[i].id == 'background') {
                                this.bg = components[i].data;
                                this.bgSetting = this.bg;
                                this.position = this.bg.positionText;
                                this.repeat = this.bg.repeatText;
                            }
                        }
                        this.components = components;
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
        },
        mounted: function () {
            this.getHome();
        }
    });
</script>
