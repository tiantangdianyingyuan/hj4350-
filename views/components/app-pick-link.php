<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .app-pick-link .el-checkbox + .el-checkbox {
        margin-left: 0;
    }

    .app-pick-link .checkbox-div-box {
        height: 350px;
        overflow: auto;
    }

    .app-pick-link .edit-img {
        width: 18px;
        height: 18px;
        display: inline-block;
        margin-left: 10px;
        cursor: pointer;
    }

    .app-pick-link .el-dialog {
        width: 800px;
    }

    .app-pick-link .el-checkbox {
        margin-right: 0;
        height: 32px;
    }

    .app-pick-link .el-checkbox__input {
        margin-top: 4px;
    }
</style>

<template id="app-pick-link">
    <div>
        <el-dialog class="app-pick-link" :title="title ? title : '选择链接'"
                   :visible.sync="dialogFormVisible"
                   @opened="dialogOpened"
                   :close-on-click-modal="false"
                   append-to-body>
            <el-form v-loading="loading" :rules="form_rules" size="small" @submit.native.prevent label-width="60px">
                <div flex="main:justify">
                    <div style="min-width: 330px">
                        <el-card class="box-card" shadow="never">
                            <el-tabs v-model="activeName" @tab-click="handleClick">
                                <el-tab-pane label="基础" name="base">
                                    <div class="checkbox-div-box">
                                        <el-checkbox-group v-model="checkedCities">
                                            <div flex="dir:left cross:center" v-for="(item, key) in options.base"
                                                 :key="item.id">
                                                <el-checkbox flex="dir:left cross:center" @change="selectChecked(item,key)"
                                                             :label="item.value">
                                                    <div flex="cross:center">
                                                        <img style="width: 18px;height: 18px;margin-right: 5px"
                                                             :src="item.icon">
                                                        {{item.name}}
                                                    </div>
                                                </el-checkbox>
                                                <img class="edit-img"
                                                     v-if="item.params"
                                                     @click="pickLinkEdit(item)"
                                                     src="statics/img/mall/icon_pick_link_edit.png">
                                            </div>
                                        </el-checkbox-group>
                                    </div>
                                </el-tab-pane>
                                <el-tab-pane label="订单" name="order">
                                    <div class="checkbox-div-box">
                                        <el-checkbox-group v-model="checkedCities">
                                            <div flex="dir:left cross:center" v-for="(item, key) in options.order"
                                                 :key="item.id">
                                                <el-checkbox flex="dir:left cross:center" @change="selectChecked(item,key)"
                                                             :label="item.value">
                                                    <div flex="cross:center" style="margin-top: 1px">
                                                        <img style="width: 18px;height: 18px;margin-right: 5px"
                                                             :src="item.icon">
                                                        {{item.name}}
                                                    </div>
                                                </el-checkbox>
                                                <img class="edit-img"
                                                     v-if="item.params"
                                                     @click="pickLinkEdit(item)"
                                                     src="statics/img/mall/icon_pick_link_edit.png">
                                            </div>
                                        </el-checkbox-group>
                                    </div>
                                </el-tab-pane>
                                <el-tab-pane label="营销" name="share">
                                    <div class="checkbox-div-box">
                                        <el-checkbox-group v-model="checkedCities">
                                            <div flex="dir:left cross:center" v-for="(item, key) in options.marketing"
                                                 :key="item.id">
                                                <el-checkbox flex="dir:left cross:center" @change="selectChecked(item,key)"
                                                             :label="item.value">
                                                    <div flex="cross:center" style="margin-top: 1px">
                                                        <img style="width: 18px;height: 18px;margin-right: 5px"
                                                             :src="item.icon">
                                                        {{item.name}}
                                                    </div>
                                                </el-checkbox>
                                                <img class="edit-img"
                                                     v-if="item.params"
                                                     @click="pickLinkEdit(item)"
                                                     src="statics/img/mall/icon_pick_link_edit.png">
                                            </div>
                                        </el-checkbox-group>
                                    </div>
                                </el-tab-pane>
                                <el-tab-pane label="插件" name="plugin">
                                    <div class="checkbox-div-box">
                                        <el-checkbox-group v-model="checkedCities">
                                            <div flex="dir:left cross:center" v-for="(item, key) in options.plugin"
                                                 :key="item.id">
                                                <el-checkbox flex="dir:left cross:center" @change="selectChecked(item,key)"
                                                             :label="item.value">
                                                    <div flex="cross:center" style="margin-top: 1px">
                                                        <img style="width: 18px;height: 18px;margin-right: 5px"
                                                             :src="item.icon">
                                                        {{item.name}}
                                                    </div>
                                                </el-checkbox>
                                                <img class="edit-img"
                                                     v-if="item.params"
                                                     @click="pickLinkEdit(item)"
                                                     src="statics/img/mall/icon_pick_link_edit.png">
                                            </div>
                                        </el-checkbox-group>
                                    </div>
                                </el-tab-pane>
                                <el-tab-pane label="微页面" name="diy" v-if="options.diy">
                                    <div style="padding: 0 20px 5px;">
                                        <el-input v-model="keyword" @keyup.enter.native="search" placeholder="输入微页面页面标题进行搜索"></el-input>
                                    </div>
                                    <div class="checkbox-div-box">
                                        <el-checkbox-group v-model="checkedCities">
                                            <div flex="dir:left cross:center" v-show="item.show == true" v-for="(item, key) in options.diy"
                                                 :key="item.id">
                                                <el-checkbox flex="dir:left cross:center" @change="selectChecked(item,key)"
                                                             :label="item.value">
                                                    <div flex="cross:center" style="margin-top: 1px">
                                                        <img style="width: 18px;height: 18px;margin-right: 5px"
                                                             :src="item.icon">
                                                        {{item.name}}
                                                    </div>
                                                </el-checkbox>
                                                <img class="edit-img"
                                                     v-if="item.params"
                                                     @click="pickLinkEdit(item)"
                                                     src="statics/img/mall/icon_pick_link_edit.png">
                                            </div>
                                        </el-checkbox-group>
                                    </div>
                                </el-tab-pane>
                            </el-tabs>
                        </el-card>
                    </div>
                    <div v-if="currentCheckedItem.params && currentCheckedItem.params.length > 0 || currentCheckedItem.remark">
                        <el-card shadow="never" style="width: 420px">
                            <div slot="header">
                                <span>{{currentCheckedItem.name}}</span>
                            </div>
                            <el-form @submit.native.prevent label-width="130px">
                                <el-form-item v-if="!item.is_show && item.is_show != false" style="margin-bottom: 0" v-for="item in currentCheckedItem.params"
                                              :key="item.key" :prop="item.is_required ? 'key_name' : ''">
                                    <template slot='label'>
                                        <span>{{item.key}}</span>
                                        <el-tooltip v-if="item.desc" effect="dark" :content="item.desc"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </template>
                                    <el-input size="small" :type="item.data_type ? item.data_type : ''"
                                              v-model="item.value"
                                              :placeholder="item.desc">
                                    </el-input>
                                    <span v-if="item.page_url">
                                        所需数据请到“<el-button type="text" @click="$navigate({r:item.page_url}, true)">
                                            {{item.page_url_text}}
                                        </el-button>”查看</span>
                                </el-form-item>
                            </el-form>
                        </el-card>
                        <div style="margin: 15px 20px 10px" v-if="currentCheckedItem.remark">
                            <div style="color: #ff4544;">{{currentCheckedItem.remark}}</div>
<!--                            <div v-if="currentCheckedItem.page_url_text && currentCheckedItem.page_url">-->
<!--                                <el-button type="text" @click="$navigate({r:currentCheckedItem.page_url}, true)">-->
<!--                                    {{currentCheckedItem.page_url_text}}-->
<!--                                </el-button>-->
<!--                            </div>-->
                        </div>
                        <div v-if="item.pic_url" v-for="item in currentCheckedItem.params">
                            <div style="margin: 15px 0 10px">示例:</div>
                            <img style="width: 400px;" :src="item.pic_url" alt="">
                        </div>
                    </div>
                </div>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="confirm">确 定</el-button>
            </div>
        </el-dialog>
        <div @click="dialogFormVisible = !dialogFormVisible" style="display: inline-block">
            <slot></slot>
        </div>
    </div>
</template>
<script>
    Vue.component('app-pick-link', {
        template: '#app-pick-link',
        props: {
            mallId: {
                default: null,
            },
            title: String,
            type: {
                type: String,
                default: 'single',// single|单个,multiple|多个
            },
            params: Object,
            ignore: String, // navigate|导航底栏
        },
        data() {
            return {
                dialogFormVisible: false,
                loading: true,
                options: [],
                activeName: 'base',
                keyword: '',
                currentCheckedItem: {},//当前选择链接
                checkedList: [],// 全部选中链接
                checkedCities: [],
                form_rules: {
                    key_name: [
                        {required: true, message: '请输入名称', trigger: 'change'},
                    ],
                },
            };
        },
        created() {
        },
        methods: {
            dialogOpened() {
                this.currentCheckedItem = {};
                this.checkedList = [];
                this.checkedCities = [];
                if (this.options.length == 0) {
                    this.loadList({})
                }
            },
            // clearSearch() {
            //     let that = this;
            //     that.options.diy.forEach(function(row){
            //         row.show = true;
            //         that.$forceUpdate();
            //     })
            // },

            search() {
                let that = this;
                that.options.diy.forEach(function(row){
                    row.show = false;
                    if(row.name.indexOf(that.keyword) > -1) {
                        row.show = true;
                    }
                    that.$forceUpdate();
                })
            },

            loadList() {
                this.loading = true;
                const params = {
                    r: 'mall/link/index',
                    type: this.type,
                    ignore: this.ignore
                };
                if (this.mallId) {
                    params.mall_id = this.mallId;
                }
                request({
                    params: params,
                }).then(e => {
                    if (e.data.code === 0) {
                        this.options = e.data.data.list;
                        if (typeof this.options.diy !== 'undefined') {
                            this.options.diy.forEach(function(row){
                                row.show = true;
                            })
                        }
                        this.loading = false;
                    } else {
                        this.$message.error(e.data.msg);
                        this.dialogFormVisible = false;
                    }
                }).catch();
            },
            confirm(formName) {
                let self = this;
                let sign = true;
                self.checkedList.forEach(function (cItem, cIndex) {
                    if (cItem.params) {
                        let params = '';
                        // 拼接路由参数
                        cItem.params.forEach(function (pItem, pIndex) {
                            if (!pItem.value && pItem.is_required === true) {
                                sign = false;
                                self.$message.error(cItem.name + '->' + pItem.desc)
                            }
                            if (pItem['key'] === 'tel') {
                                let sentinel = /(^1\d{10}$)|(^([0-9]{3,4}-)?\d{7,8}$)|(^400[0-9]{7}$)|(^800[0-9]{7}$)|(^(400)-(\d{3})-(\d{4})(.)(\d{1,4})$)|(^(400)-(\d{3})-(\d{4}$))/.test(pItem.value);
                                if (!sentinel) {
                                    sign = false;
                                    self.$message.error('请填写有效的联系电话或手机');
                                }
                            }
                            let value = pItem['value'];
                            if (pItem['key'] === 'url') {
                                value = encodeURIComponent(value);
                            }
                            params += pItem['key'] + '=' + value + '&';
                        });
                        params = params.substr(0, params.length - 1);

                        // 拼接路由、参数
                        cItem.new_link_url = cItem['value'] + '?' + params;
                    } else {
                        cItem.new_link_url = cItem['value'];
                    }
                });
                if (!sign) {
                    return;
                }
                self.$emit('selected', self.checkedList, self.params);
                self.dialogFormVisible = false;
            },
            handleClick(tab, event) {
                console.log(tab, event);
            },
            selectChecked(item, index) {
                let self = this;
                let newItem = JSON.parse(JSON.stringify(item));
                self.currentCheckedItem = newItem;

                // 如果是单选 只能勾选一个
                if (self.type === 'single') {
                    self.checkedCities = [];
                    self.checkedCities.push(item.value)
                }

                let sign = true;
                self.checkedList.forEach(function (cItem, cIndex) {
                    if (cItem.id === item.id) {
                        self.checkedList.splice(cIndex, 1);
                        self.currentCheckedItem = {};
                        sign = false;
                    }
                });
                if (sign) {
                    // 如果是单选 只能勾选一个
                    if (self.type === 'single') {
                        self.checkedList = [];
                        self.checkedList.push(newItem);
                    } else if (self.type === 'multiple') {
                        self.checkedList.push(newItem);
                    } else {
                        console.log('pickLink 组件参数type错误：请检查')
                    }
                }
            },
            pickLinkEdit(item) {
                let self = this;
                let sign = true;
                self.checkedList.forEach(function (cItem, cIndex) {
                    if (cItem.id === item.id) {
                        self.currentCheckedItem = cItem;
                        sign = false;
                    }
                });
                if (sign) {
                    self.currentCheckedItem = item;
                }
            }
        },
    });
</script>
