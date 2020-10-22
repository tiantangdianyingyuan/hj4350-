<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/25
 * Time: 9:49
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
$pluginUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl();
$mallUrl = Yii::$app->request->hostInfo
    . Yii::$app->request->baseUrl
    . '/statics/img/app';
Yii::$app->loadViewComponent('app-dialog-select')
?>
<style>
    /* ------------------预览------------------- */
    .diy-topic .diy-component-preview {
        width: 100%;
    }

    .diy-topic .diy-component-preview .diy-topic-normal {
        width: 100%;
        padding: 10px;
        background: #fff;
    }

    .diy-topic .diy-component-preview .cat-list {
        width: 100%;
        overflow-x: auto;
        background: #fff;
    }

    .diy-topic .diy-component-preview .cat-list .cat-item {
        height: 80px;
        padding: 0 10px;
        text-align: center;
        max-width: 100%;
        white-space: nowrap;
        margin: 0 20px;
    }

    .diy-topic .diy-component-preview .cat-list .cat-item.active {
        border-bottom: 4px #ff4544 solid;
    }

    .diy-topic .diy-component-preview .topic-list {
    }

    .diy-topic .diy-component-preview .topic-list .topic-item {
        padding: 24px;
        margin-top: 12px;
        background: #fff;
    }

    .diy-topic .diy-component-preview .topic-list .topic-item:first-child {
        margin-top: 0;
    }

    .diy-topic .diy-component-preview .topic-list .topic-item .browse {
        font-size: 24px;
        color: #888;
        margin-top: 12px;
    }

    .diy-topic .diy-component-preview .topic-list .topic-item .topic-pic {
        background: #eee;
    }


    /* ------------------设置------------------- */
    .diy-topic .diy-component-edit .diy-topic-label {
        width: 82px;
    }

    .diy-topic .topic-edit-options {
        position: relative;
        overflow: visible;
    }

    .diy-topic .topic-edit-options .delete {
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
</style>
<template id="diy-topic">
    <div class="diy-topic">
        <div class="diy-component-preview">
            <div class="diy-topic-normal" flex="dir:left cross:center" v-if="data.style == 'normal'">
                <template v-if="data.count==1">
                    <app-image :src="data.logo_1" mode="scaleToFill"
                               width="104px" height="32px" style="margin-right: 20px;"></app-image>
                </template>
                <template v-else>
                    <app-image :src="data.logo_2" mode="scaleToFill"
                               width="104px" height="50px" style="margin-right: 20px;"></app-image>
                </template>
                <div :style="cStyle">
                    <div flex="dir:left cross:center" v-for="topic in cTopic">
                        <app-image :src="data.icon" mode="scaleToFill"
                                   width="54px" height="28px" style="margin-right: 10px;"></app-image>
                        <div>{{topic.title}}</div>
                    </div>
                </div>
            </div>
            <div class="diy-topic-list" v-if="data.style == 'list'">
                <template v-if="data.cat_show">
                    <div class="cat-list" flex="dir:left">
                        <div class="cat-item" flex="main:center cross:center" v-for="(cat, index) in cList"
                             :class="index == cat_index ? 'active' : ''" @click="selectCat(index)">
                            <div>{{cat.name}}</div>
                        </div>
                    </div>
                </template>
                <div class="topic-list">
                    <template v-for="topic in cTopic">
                        <div class="topic-item" v-if="topic.layout == 0" flex="dir:left box:last">
                            <div flex="dir:top box:last">
                                <div class="topic-title">
                                    <app-ellipsis :line="2">{{topic.title}}</app-ellipsis>
                                </div>
                                <div class="browse">{{topic.read_count}}人浏览</div>
                            </div>
                            <app-image class="topic-pic" :src="topic.cover_pic" width="268px"
                                       height="202px" mode="scaleToFill">

                            </app-image>
                        </div>
                        <div class="topic-item" v-else flex="dir:top">
                            <div class="topic-title">
                                <app-ellipsis :line="2">{{topic.title}}</app-ellipsis>
                            </div>
                            <app-image class="topic-pic" :src="topic.cover_pic" width="702px"
                                       height="350px" mode="scaleToFill">

                            </app-image>
                            <div class="browse">{{topic.read_count}}人浏览</div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="专题样式">
                    <app-radio v-model="data.style" label="normal">简易模式</app-radio>
                    <app-radio v-model="data.style" label="list">列表模式</app-radio>
                </el-form-item>
                <template v-if="data.style == 'normal'">
                    <el-form-item label="显示行数">
                        <el-select size="small" v-model="data.count">
                            <el-option :value="1"></el-option>
                            <el-option :value="2"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="logo(1行)">
                        <app-attachment title="选择图片" :multiple="false" :max="1" type="image"
                                        v-model="data.logo_1">
                            <el-tooltip class="item" effect="dark"
                                        content="建议尺寸104*32"
                                        placement="top">
                                <el-button size="mini">选择图片</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-gallery :url="data.logo_1" :show-delete="true"
                                     @deleted="deletePic('logo_1')"></app-gallery>
                    </el-form-item>
                    <el-form-item label="logo(2行)">
                        <app-attachment title="选择图片" :multiple="false" :max="1" type="image"
                                        v-model="data.logo_2">
                            <el-tooltip class="item" effect="dark"
                                        content="建议尺寸104*50"
                                        placement="top">
                                <el-button size="mini">选择图片</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-gallery :url="data.logo_2" :show-delete="true"
                                     @deleted="deletePic('logo_2')"></app-gallery>
                    </el-form-item>
                    <el-form-item label="专题标签">
                        <app-attachment title="选择图片" :multiple="false" :max="1" type="image"
                                        v-model="data.icon">
                            <el-tooltip class="item" effect="dark"
                                        content="建议尺寸54*28"
                                        placement="top">
                                <el-button size="mini">选择图片</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-gallery :url="data.icon" :show-delete="true"
                                     @deleted="deletePic('icon')"></app-gallery>
                    </el-form-item>
                </template>
                <template v-else>
                    <el-form-item label="是否显示分类">
                        <el-switch v-model="data.cat_show"></el-switch>
                    </el-form-item>
                    <el-form-item label="分类列表" v-if="data.cat_show">
                        <el-card shadow="never" class="topic-edit-options" style="margin-bottom: 24px" v-for="(cat, catIndex) in data.list"
                                 :key="catIndex">
                            <div @click="selectCat(catIndex)">
                                <div flex="box:first">
                                    <div class="diy-topic-label">专题分类</div>
                                    <div>{{cat.cat_name}}</div>
                                </div>
                                <div flex="box:first">
                                    <div class="diy-topic-label">菜单名称</div>
                                    <div>
                                        <el-input v-model="cat.name" size="small"></el-input>
                                    </div>
                                </div>
                                <div flex="box:first">
                                    <div class="diy-topic-label">自定义专题</div>
                                    <div>
                                        <el-switch v-model="cat.custom"></el-switch>
                                    </div>
                                </div>
                                <div flex="box:first" v-if="!cat.custom">
                                    <div class="diy-topic-label">专题数量</div>
                                    <div>
                                        <el-input v-model="cat.number" size="small"></el-input>
                                    </div>
                                </div>
                                <div flex="box:first" v-else>
                                    <div class="diy-topic-label">专题列表</div>
                                    <div>
                                        <app-dialog-select url="mall/topic/index" :multiple="true"
                                                           @selected="selectTopic" :extra-search="{type: cat.cat_id}"
                                                           title="选择专题" list-key="title">
                                            <el-button size="mini">添加专题</el-button>
                                        </app-dialog-select>
                                        <app-gallery :list="cat.children" url-key="cover_pic" :show-delete="true"
                                                     @deleted="deleteTopic" width="100px" height="100px"></app-gallery>
                                    </div>
                                </div>
                            </div>
                            <el-button class="delete" @click="topicCatDelete(catIndex)" type="primary" icon="el-icon-delete"
                                style="top: 0;right: -26px;"></el-button>
                        </el-card>
                        <app-dialog-select url="mall/topic-type/index" :multiple="true" @selected="selectCatList"
                                           title="选择分类">
                            <el-button size="mini">添加分类</el-button>
                        </app-dialog-select>
                    </el-form-item>
                    <el-form-item label="专题列表" v-else>
                        <app-dialog-select url="mall/topic/index" :multiple="true" @selected="selectTopic"
                                           title="选择专题" list-key="title">
                            <el-button size="mini">添加专题</el-button>
                        </app-dialog-select>
                        <app-gallery :list="data.topic_list" url-key="cover_pic" :show-delete="true"
                                     @deleted="deleteTopic" width="100px" height="100px"></app-gallery>
                    </el-form-item>
                </template>
            </el-form>
        </div>
    </div>
</template>
<script>
    const _mallUrl = '<?= $mallUrl ?>';
    Vue.component('diy-topic', {
        template: '#diy-topic',
        props: {
            value: Object
        },
        data() {
            return {
                data: {
                    style: 'normal',
                    count: 1,
                    logo_1: _mallUrl + '/topic/icon-topic-1.png',
                    logo_2: _mallUrl + '/topic/icon-topic-2.png',
                    icon: _mallUrl + '/topic/icon-topic-r.png',
                    list: [],
                    cat_show: false,
                    topic_list: []
                },
                defaultData: {},
                cat_index: 0,
                topicShow: false,
                catShow: false,
            }
        },
        created() {
            let data = JSON.parse(JSON.stringify(this.data));
            this.defaultData = data;
            if (!this.value) {
                this.$emit('input', data)
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            },
        },
        computed: {
            cList() {
                if (this.data.list && this.data.list.length > 0 && this.data.cat_show) {
                    return this.data.list;
                } else {
                    let catList = {
                        cat_id: 0,
                        cat_name: '分类名称',
                        name: '分类名称',
                        children: [],
                        custom: 0,
                        number: 0
                    };
                    return [catList, catList]
                }
            },
            cTopic() {
                if (this.data.list && this.data.list.length > 0
                    && this.data.list[this.cat_index].children && this.data.list[this.cat_index].children.length > 0
                    && this.data.cat_show) {
                    return this.data.list[this.cat_index].children;
                } else if (this.data.topic_list && this.data.topic_list.length > 0 && !this.data.cat_show) {
                    return this.data.topic_list;
                } else {
                    let topic = {
                        title: '这是一条专题示例',
                        cover_pic: '',
                        read_count: '999',
                        layout: 0,
                        id: 0
                    };
                    let topic_1 = JSON.parse(JSON.stringify(topic));
                    topic_1.layout = 1;
                    return [topic, topic_1];
                }
            },
            cStyle() {
                if (this.data.style === 'normal') {
                    if (this.data.count === 1) {
                        return 'height: 32px;overflow-y: hidden;line-height: 32px'
                    }
                }
            },
        },
        methods: {
            topicCatDelete(index) {
                this.data.list.splice(index, 1);
            },

            deletePic(param) {
                this.data[param] = this.defaultData[param];
            },
            deleteTopic(item, index) {
                if (this.data.cat_show) {
                    this.data.list[this.cat_index].children.splice(index, 1)
                } else {
                    this.data.topic_list.splice(index, 1)
                }
            },
            selectCat(index) {
                this.cat_index = index
                this.catShow = false;
            },
            selectTopic(list) {
                let topic_list = [];
                for (let i in list) {
                    topic_list.push({
                        title: list[i].title,
                        cover_pic: list[i].cover_pic,
                        read_count: list[i].read_count,
                        layout: list[i].layout,
                        id: list[i].id
                    });
                }
                topic_list = JSON.parse(JSON.stringify(topic_list));
                this.data.topic_list = this.data.topic_list.concat(topic_list);
                if (this.data.list && this.data.list.length > 0) {
                    this.data.list[this.cat_index].children = this.data.list[this.cat_index].children.concat(topic_list);
                }
                this.topicShow = false;
            },
            selectCatList(list) {
                for (let i in list) {
                    this.data.list.push({
                        cat_id: list[i].id,
                        cat_name: list[i].name,
                        name: list[i].name,
                        children: [],
                        custom: 0,
                        number: 30
                    });
                }
            }
        }
    });
</script>
