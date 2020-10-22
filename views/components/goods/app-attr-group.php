<?php
Yii::$app->loadViewComponent('goods/app-attr-template');
?>
<style>
    .app-attr-group {
        border: 1px solid #EBEEF5;
        padding: 10px;
    }

    .app-attr-group .bg {
        height: 44px;
        padding: 0 10px;
        background: #f8f8f8;
    }

    .app-attr-group .del-img {
        height: 20px;
        width: 20px;
        cursor: pointer;
    }

    .app-attr-group .img-box {
        position: relative;
        height: 100px;
        width: 100px;
        margin-top: 8px;
        border: 1px solid #EBEEF5;
    }

    .app-attr-group .attr-jt {
        background: #ffffff;
        width: 6px;
        height: 6px;
        border-top: 1px solid rgb(235, 238, 245);
        border-right: 1px solid #EBEEF5;
        transform: rotate(-45deg);
        -o-transform: rotate(-45deg);
        -webkit-transform: rotate(-45deg);
        -moz-transform: rotate(-45deg);
        -ms-transform: rotate(-45deg);
        position: absolute;
        top: -5px;
    }

    .app-attr-group .close {
        position: absolute;
        top: -4px;
        right: -4px;
        font-size: 16px;
        cursor: pointer;
    }

    .app-attr-group .attr-list {
        display: inline-block;
        margin-right: 10px;
        margin-bottom: 10px;
        position: relative;
        cursor: move;
    }
</style>
<template id="app-attr-group">
    <div class="app-attr-group">
        <div v-for="(group, i) in value" :key="i" style="margin-bottom: 16px">
            <div flex="dir:left cross:center" class="bg">
                <span>规格名:</span>
                <el-input type="text" v-model.trim="group.attr_group_name" size="mini" @input="makeAttrGroup"
                          style="width:94px;margin:0 16px"></el-input>
                <el-checkbox v-if="i === 0" v-model="attrPic">规格图片</el-checkbox>
                <div @click="deleteAttrGroup(i)" style="margin-left: auto;line-height: 1">
                    <el-image class="del-img" src="statics/img/mall/order/del.png"></el-image>
                </div>
            </div>
            <div flex="dir:left" style="padding:0 10px;margin-top: 16px">
                <div class="box-grow-0">规格值：</div>
                <draggable :options="{draggable:'.attr-list'}" @end="makeAttrGroup"
                           flex="dir:left" style="flex-wrap: wrap" v-model="group.attr_list">
                    <div class="attr-list" v-for="(attr, j) in group.attr_list" :key="j">
                        <el-input style="width:152px" size="mini" type="text" @input="makeAttrGroup"
                                  v-model.trim="attr.attr_name"></el-input>
                        <i class="el-icon-error close" @click="deleteAttr(i,j)"></i>
                        <div flex="cross:center main:center" class="img-box" v-if="i===0 && attrPic">
                            <div class="attr-jt"></div>
                            <div v-if="attr.pic_url" @mouseenter="attrPicEnter(j)">
                                <el-image :src="attr.pic_url" style="height:88px;width:88px"></el-image>
                            </div>
                            <app-attachment v-else v-model="attr.pic_url" :multiple="false" :max="1">
                                <el-tooltip class="item" effect="dark" content="建议尺寸:750 * 750" placement="top">
                                    <el-button type="text">+添加图片</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <i v-if="attrPicStatus === j && attr.pic_url" class="el-icon-error close"
                               @click="attr.pic_url = ''"></i>
                            <div v-if="attrPicStatus === j && attr.pic_url" style="position: absolute">
                                <app-attachment v-model="attr.pic_url" :multiple="false" :max="1">
                                    <el-button type="primary">替换</el-button>
                                </app-attachment>
                            </div>
                        </div>
                    </div>
                    <div slot="footer">
                        <el-button type="text" @click="addAttr(i)">添加规格值</el-button>
                    </div>
                </draggable>
            </div>
            <div v-if="i === 0" style="margin-left: 65px;color:#c9c9c9">
                仅支持为第一组规格设置规格图片，买家选择不同规格会看到对应规格图片，建议尺寸：80 * 80像素
            </div>
        </div>
        <div v-if="isAddAttrGroups" flex="dir:left cross:center" class="bg">
            <app-attr-template v-model="value" :attr-group-max-count="attrGroupMaxCount" @submit="makeAttrGroup">
                <el-button>选择规格模板</el-button>
            </app-attr-template>
            <el-button @click="addAttrGroup" style="margin-left: 12px">添加规格项目</el-button>
            <span style="padding-left: 14px;color:#c9c9c9">注：规格名最多添加5个</span>
        </div>
    </div>
</template>
<script>
    Vue.component('app-attr-group', {
        template: '#app-attr-group',
        props: {
            value: {
                type: Array,
                default: function () {
                    return [];
                }
            }
        },
        data() {
            return {
                attrGroupMaxCount: 5, //可添加最大规格组
                attrPic: null, //规格图片
                attrPicStatus: -1, //move
            }
        },
        computed: {
            //添加规格组按钮是否显示
            isAddAttrGroups() {
                return this.value.length < this.attrGroupMaxCount;
            },
        },
        watch: {
            'value'(newVal, oldVal) {
                if (newVal[0] && this.attrPic === null) {
                    this.attrPic = newVal[0]['attr_list'].some(item => {
                        return item.pic_url !== '';
                    });
                }
            },
            'attrPic'(newVal, oldVal) {
                if (!newVal) {
                    this.value[0]['attr_list'].forEach((v) => {
                        v.pic_url = '';
                    })
                }
            },
        },
        methods: {
            makeAttrGroup() {
                this.$emit('select', this.value);
            },

            attrPicEnter(value) {
                if (this.attrPicStatus == value) {
                    return;
                }
                this.attrPicStatus = value;
            },
            // 添加规格组
            addAttrGroup() {
                this.value.push({
                    attr_group_id: this.value.length + 1,
                    attr_group_name: '',
                    attr_list: [],
                });
            },

            // 删除规格组
            deleteAttrGroup(index) {
                this.value.splice(index, 1);
                console.log(this.value);
                this.makeAttrGroup();
            },

            // 删除规格
            deleteAttr(i, j) {
                this.value[i].attr_list.splice(j, 1);
                this.makeAttrGroup();
            },

            // 添加规格
            addAttr(i) {
                this.value[i].attr_list.push({
                    attr_id: this.getLastAttrId() + 1,
                    attr_name: '',
                    pic_url: '',
                });
                this.makeAttrGroup();
            },

            // 获取最后一个规格ID
            getLastAttrId() {
                let id = 1;
                this.value.forEach((attrGroupItem, attrGroupIndex) => {
                    attrGroupItem.attr_list.forEach(() => {
                        id += 1;
                    });
                });
                return id;
            },
        }
    });
</script>