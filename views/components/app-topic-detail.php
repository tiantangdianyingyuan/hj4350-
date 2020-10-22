<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/10/14
 * Time: 16:41
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<?php
$diyPath = \Yii::$app->viewPath . '/components/diy';
$currentDir = opendir($diyPath);
$components = [];
while (($file = readdir($currentDir)) !== false) {
    if (stripos($file, 'diy-') === 0) {
        $components[] = substr($file, 4, (stripos($file, '.php') - 4));
    }
}
closedir($currentDir);
foreach ($components as $component) {
    Yii::$app->loadViewComponent("diy-{$component}", $diyPath);
}
Yii::$app->loadViewComponent('app-hotspot');
Yii::$app->loadViewComponent('app-rich-text');
$baseUrl = Yii::$app->request->baseUrl;
?>
<style>
    .all-components {
        width: 422px;
        background: #fff;
    }

    .all-components .component-group {
        width: 422px;
        margin-bottom: 20px;
    }

    .all-components .component-group:last-child {
        margin-bottom: 0;
    }

    .all-components .component-group-name {
        height: 35px;
        line-height: 35px;
        background: #f7f7f7;
        padding: 0 20px;
        border-bottom: 1px solid #eeeeee;
    }

    .all-components .component-list {
        flex-wrap: wrap;
        border: 0 solid #eeeeee;
        border-width: 1px 0 0 1px;
    }

    .all-components .component-list .component-item {
        width: 140px;
        height: 100px;
        border: 0 solid #eeeeee;
        border-width: 0 1px 1px 0;
        text-align: center;
        padding: 15px 0 0;
        cursor: pointer;
    }

    .all-components .component-list .component-icon {
        width: 40px;
        height: 40px;
        /*border: 1px solid #eee;*/
    }

    .all-components .component-list .component-name {

    }

    .mobile-framework {
        width: 420px;
        padding: 22px 22px 22px 23px;
        background-color: #FFFFFF;
        border-radius: 16px;
    }

    .mobile-framework-header {
        height: 60px;
        line-height: 60px;
        background: #333;
        color: #fff;
        text-align: center;
        background: url('statics/img/mall/topic-head.png') no-repeat;
        border: 0 solid #eeeeee;
        border-width: 2px 2px 0 2px;
        width: 375px;
    }

    .mobile-framework-body {
        width: 375px;
        min-height: 500px;
        border: 1px solid #e2e2e2;
        background: #f5f7f9;
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
        height: 22px;
        line-height: 22px;
        width: 22px;
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

    .mobile-framework .active .diy-component-edit {
        display: block;
        padding-right: 20%;
        min-width: 650px;
    }

    .all-components {
        max-height: 625px;
        overflow-y: auto;
    }

    .bottom-menu {
        text-align: center;
        height: 54px;
        width: 100%;
    }

    .bottom-menu .el-card__body {
        padding-top: 10px;
    }

    .el-dialog {
        min-width: 800px;
    }
</style>
<template id="app-topic-detail" v-cloak>
    <div class="app-topic-detail">
        <div style="position: relative;margin-bottom: 10px;height: 621px;">
            <div class="all-components">
                <div class="component-group" v-for="group in allComponents">
                    <div class="component-list" flex="">
                        <template v-for="item in group.list">
                            <div class="component-item" @click="selectComponent(item)">
                                <img class="component-icon" :src="item.icon">
                                <div class="component-name">{{item.name}}</div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div style="width: 430px; overflow-y: auto; overflow-x: hidden;margin-top: 16px;height: 605px;">
                <div class="mobile-framework">
                    <div class="mobile-framework-header"></div>
                    <div class="mobile-framework-body">
                        <draggable v-model="components" :options="{filter:'.active',preventOnFilter:false}"
                                   v-if="components && components.length">
                            <div v-for="(component, index) in components" :key="component.key"
                                 @click="showComponentEdit(component,index)"
                                 :class="(component.active?'active':'')">
                                <div class="diy-component-options" v-if="component.active">
                                    <el-button type="primary"
                                               icon="el-icon-delete"
                                               @click.stop="deleteComponent(index)"
                                               style="left: -25px;top:0;"></el-button>
                                    <el-button type="primary"
                                               icon="el-icon-document-copy"
                                               @click.stop="copyComponent(index)"
                                               style="left: -25px;top:30px;"></el-button>
                                    <el-button v-if="index > 0 && components.length > 1"
                                               type="primary"
                                               icon="el-icon-arrow-up"
                                               @click.stop="moveUpComponent(index)"
                                               style="right: -25px;top:0;"></el-button>
                                    <el-button v-if="components.length > 1 && index < components.length-1"
                                               type="primary"
                                               icon="el-icon-arrow-down"
                                               @click.stop="moveDownComponent(index)"
                                               style="right: -25px;top:30px;"></el-button>
                                </div>
                                <?php foreach ($components as $component) : ?>
                                    <diy-<?= $component ?> v-if="component.id === '<?= $component ?>'"
                                                           :active="component.active"
                                                           v-model="component.data"></diy-<?= $component ?>>
                                <?php endforeach; ?>
                            </div>
                        </draggable>
                        <div v-else flex="main:center cross:center"
                             style="height: 200px;color: #adb1b8;text-align: center;">
                            <div>
                                <i class="el-icon-folder-opened"
                                   style="font-size: 32px;margin-bottom: 10px"></i>
                                <div>空空如也</div>
                                <div>请从上方组件库添加组件</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    Vue.component('app-topic-detail', {
        template: '#app-topic-detail',
        props: {
            value: Array,
        },
        data() {
            return {
                allComponents: [
                    {
                        list: [
                            {
                                id: 'rubik',
                                name: '图片广告',
                                icon: '<?=$baseUrl?>/statics/img/mall/diy/rubik.png',
                            },
                            {
                                id: 'video',
                                name: '视频',
                                icon: '<?=$baseUrl?>/statics/img/mall/diy/video.png',
                            },
                            {
                                id: 'image-text',
                                name: '图文详情',
                                icon: '<?=$baseUrl?>/statics/img/mall/diy/image-text.png',
                            },
                        ]
                    }
                ],
                components: this.value ? this.value : [],
            };
        },
        created() {
            if (!this.value) {
            } else {
                this.components = JSON.parse(JSON.stringify(this.value));
            }
        },
        watch: {
            components: {
                handler(v) {
                    let data = [];
                    for (let i in v) {
                        data.push({
                            id: v[i].id,
                            data: v[i].data
                        })
                    }
                    this.$emit('input', data);
                },
                immediate: true,
                deep: true,
            },
        },
        methods: {
            selectComponent(e) {
                if (e.single) {
                    for (let i in this.components) {
                        if (this.components[i].id === e.id) {
                            this.$message.error('该组件只允许添加一个。');
                            return;
                        }
                    }
                }
                let currentIndex = this.components.length;
                for (let i in this.components) {
                    if (this.components[i].active) {
                        currentIndex = i + 1;
                        break;
                    }
                }
                const component = {
                    id: e.id,
                    data: null,
                    active: false,
                    key: randomString(),
                };
                this.components.splice(currentIndex, 0, component);
            },
            showComponentEdit(component, index) {
                for (let i in this.components) {
                    if (i == index) {
                        this.components[i].active = true;
                    } else {
                        this.components[i].active = false;
                    }
                }
            },
            deleteComponent(index) {
                this.components.splice(index, 1);
            },
            copyComponent(index) {
                for (let i in this.allComponents) {
                    for (let j in this.allComponents[i].list) {

                        if (this.allComponents[i].list[j].id === this.components[index].id) {
                            if (this.allComponents[i].list[j].single) {
                                this.$message.error('该组件只允许添加一个。');
                                return;
                            }
                        }
                    }
                }
                let json = JSON.stringify(this.components[index]);
                let copy = JSON.parse(json);
                copy.active = false;
                copy.key = randomString();
                this.components.splice(index + 1, 0, copy);
            },
            moveUpComponent(index) {
                this.swapComponents(index, index - 1);
            },
            moveDownComponent(index) {
                this.swapComponents(index, index + 1);
            },
            swapComponents(index1, index2) {
                this.components[index2] = this.components.splice(index1, 1, this.components[index2])[0];
            },
        },
    });
</script>

