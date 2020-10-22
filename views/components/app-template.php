<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/3
 * Time: 9:30
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .app-template .dialog {
        height: 500px;
        overflow: auto;
    }

    .app-template .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 10px;
    }

    .app-template .export-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 2;
    }

    .app-template .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 10px;
    }

    .app-template .form-button {
        margin: 0;
    }

    .app-template .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .app-template .button-item {
        padding: 9px 25px;
    }
</style>
<template id="app-template">
    <div class="app-template" v-cloak>
        <el-card style="border:0" v-loading="cardLoading" shadow="never"
                 body-style="background-color: #f3f3f3;padding: 0 0;position: relative;">
            <el-form size="small" class="export-btn" :inline="true">
                <el-form-item>
                    <template v-if="showOneKey">
                        <el-button type="primary" size="small"
                                   @click="getTemplate">一键添加{{labelTitle}}
                        </el-button>
                    </template>
                </el-form-item>
            </el-form>
            <el-form @submit.native.prevent size="small" label-width="300px">
                <el-tabs v-model="activeName" @tab-click="handleClick">
                    <el-tab-pane v-for="(item, index) in list" v-if="item.list.length > 0" :key="index"
                                 :label="item.name" :name="item.key">
                        <el-row class="form-body">
                            <div style="margin: 0 20px 20px;background-color: #F4F4F5;padding: 10px 15px;color: #909399;display: inline-block;font-size: 15px">
                                温馨提示：获取前请先确认您已获得{{labelTitle}}的使用权限，并且{{labelTitle}}中没有任何数据。获取后请不要到小程序后台
                                删除相应的{{labelTitle}}，否则会影响{{labelTitle}}正常使用。
                            </div>
                            <slot name="after_remind"></slot>
                            <el-col :span="24">
                                <template v-if="item.list.length">
                                    <el-form-item v-for="(tpl, index) in item.list" :key="index" :label="tpl.name">
                                        <el-input style="width: 30%" v-model.trim="tpl[tpl.tpl_name]"></el-input>
                                        <el-button size="small" @click="openDialog(tpl.img_url)">查看{{labelTitle}}示例
                                        </el-button>
                                        <el-button size="small" @click="test(tpl[tpl.tpl_name])" v-if="false">发送测试
                                        </el-button>
                                    </el-form-item>
                                </template>
                            </el-col>
                        </el-row>
                    </el-tab-pane>
                </el-tabs>
                <el-card shadow="never" style="margin-bottom: 20px;" v-if="false">
                    <div slot="header">
                        <span>测试用户配置(最多选择三个)</span>
                    </div>
                    <div>
                        <el-tag v-for="(item, index) in selectUsers"
                                style="margin: 0 5px"
                                :key="index"
                                closable
                                @close="deleteUser(index)">
                            {{item.nickname}}
                        </el-tag>
                        <el-button v-if="selectUsers.length < 3" size="small" type="primary" @click="getUsers">添加用户
                        </el-button>
                    </div>
                </el-card>
                <el-button class="button-item" :loading="btnLoading" type="primary" @click="store" size="small">保存
                </el-button>
                <el-button class="button-item" :loading="btnLoading" type="primary" @click="testQrcode" size="small">
                    生成测试二维码
                </el-button>
            </el-form>
            <el-dialog :title="labelTitle+ `格式`" :visible.sync="dialogVisible">
                <div class="dialog">
                    <img style="width: 100%;" :src="dialogImgUrl">
                </div>
            </el-dialog>
            <el-dialog title="用户列表" :visible.sync="dialogUsersVisible">
                <template>
                    <el-input style="width: 260px;margin-bottom: 20px;" size="small"
                              v-model="ruleForm.keyword"
                              clearable
                              @clear="getUsers"
                              @keyup.enter.native="getUsers"
                              placeholder="输入用户ID、昵称搜索">
                        <template slot="append">
                            <el-button @click="getUsers">搜索</el-button>
                        </template>
                    </el-input>
                    <el-table
                            v-loading="tableLoading"
                            ref="multipleTable"
                            :data="users"
                            tooltip-effect="dark"
                            style="width: 100%"
                            @selection-change="handleSelectionChange">
                        <el-table-column
                                type="selection"
                                width="60">
                        </el-table-column>
                        <el-table-column
                                prop="id"
                                label="ID"
                                width="80">
                        </el-table-column>
                        <el-table-column
                                prop="nickname"
                                label="昵称"
                                width="120">
                        </el-table-column>
                        <el-table-column
                                label="FormId"
                                prop="oneFormId.form_id">
                        </el-table-column>
                    </el-table>
                </template>
                <div style="text-align: right;margin-top: 20px;">
                    <el-button :loading="btnLoading" type="primary" @click="selectUserSubmit" size="small">添加
                    </el-button>
                </div>
            </el-dialog>
            <el-dialog :title="`发送`+ labelTitle" :visible.sync="progressVisible">
                <div style="margin: 10px 0;">
                    总数{{selectUsers.length}}条,失败{{progressErrorCount}}条。
                </div>
                <el-progress :text-inside="true" :stroke-width="18" :percentage="progressCount"></el-progress>
                <div style="text-align: right;margin-top: 20px;">
                    <el-button type="success" :loading="progressBtnLoading" @click="sendConfirm" size="small">确定
                    </el-button>
                </div>
                <div style="margin-top: 20px;" v-for="(item,index) in progressErrors" :key="index">
                    用户: {{item.nickname}}(ID:{{item.id}}):{{item.value}}
                </div>

            </el-dialog>
            <el-dialog :title="labelTitle +`测试二维码`" :visible.sync="templateVisible">
                <div style="text-align: center">
                    <div style="margin-bottom: 10px">温馨提示：请先保存好{{labelTitle}}之后在进行测试！</div>
                    <div v-if="qrcode">
                        <img :src="qrcode" alt="" width="430px" height="430px">
                    </div>
                    <div v-loading="!qrcode"></div>
                </div>
            </el-dialog>
        </el-card>
    </div>
</template>
<script>
    Vue.component('app-template', {
        template: '#app-template',
        props: {
            url: String, // 获取信息的地址
            submitUrl: String, // 表单提交的地址
            addUrl: String, // 一键添加的地址
            oneKey: {  //是否显示一键获取模板
                type: Boolean,
                default: true
            },
            sign: String,
        },
        computed: {
            labelTitle: function () {
                let arr = ['aliapp', 'bdapp', 'ttapp'];
                if (arr.indexOf(this.sign) === -1 && arr.indexOf(this.activeName) === -1) {
                    return '订阅消息';
                } else {
                    return '模板消息';
                }
            }
        },
        data() {
            return {
                list: [],
                activeName: 'store',
                btnLoading: false,
                cardLoading: false,
                dialogVisible: false,
                dialogImgUrl: '',

                users: [],
                dialogUsersVisible: false,
                ruleForm: {},
                tableLoading: false,
                waitSelectUsers: [],
                selectUsers: [],

                progressVisible: false,
                progressErrors: [],
                progressCount: 0,
                progressErrorCount: 0, //发送失败数
                progressSendCount: 0, //发送总条数
                progressBtnLoading: false,

                platform: '',

                showOneKey:'',
                templateVisible: false,
                qrcode: '',
            };
        },
        methods: {
            test(tpl_id) {
                let self = this;
                if (self.selectUsers.length == 0) {
                    self.$message.warning('请先添加测试用户');
                    return;
                }
                self.progressBtnLoading = true;
                self.progressVisible = true;
                let count = 100;
                let usersCount = self.selectUsers.length;
                let progressItem = (count / usersCount).toFixed(0);
                self.selectUsers.forEach(function (item, index) {
                    request({
                        params: {
                            r: 'mall/template-msg/test-send'
                        },
                        method: 'post',
                        data: {
                            tpl_id: tpl_id,
                            user_id: item.id,
                        }
                    }).then(e => {
                        self.progressBtnLoading = false;
                        self.progressSendCount += 1;// 发送总数
                        if (e.data.code == 0) {
                            self.progressCount = self.progressCount + parseInt(progressItem);
                            if (e.data.data.template_record && e.data.data.template_record.status === 0) {
                                self.progressErrors.push({
                                    id: item.id,
                                    nickname: item.nickname,
                                    value: e.data.data.template_record.error
                                });
                                self.progressErrorCount += 1; //发送失败总数
                            }
                        } else {
                            self.$message.error(e.data.msg);
                        }

                        if (self.progressSendCount == usersCount) {
                            self.progressCount = 100;
                        }
                    }).catch(e => {

                    });
                });
            },
            // 发送完成确认
            sendConfirm() {
                this.btnLoading = true;
                window.location.reload();
            },
            store() {
                let self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: this.submitUrl
                    },
                    method: 'post',
                    data: {
                        list: self.list,
                    }
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                    self.btnLoading = false;
                });

            },
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: this.url,
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.list = e.data.data.list;
                        if (self.list.length > 0) {
                            self.activeName = self.list[0].key
                        }
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            }
            ,
            handleClick(tab, event) {
                if (['wxapp','bdapp','aliapp','ttapp'].indexOf(tab.name) >= 0) {
                    if (['wxapp','bdapp'].indexOf(tab.name) >= 0) {
                        this.showOneKey = true;
                    } else {
                        this.showOneKey = false;
                    }
                    this.platform = tab.name;
                    this.getTestUser();
                } else {
                    this.showOneKey = this.oneKey;
                }
            },
            openDialog(imgUrl) {
                this.dialogVisible = true;
                this.dialogImgUrl = imgUrl;
            },
            getTemplate() {
                request({
                    params: {
                        r: this.addUrl,
                        add: true,
                        platform:this.platform,
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            getUsers() {
                let self = this;
                self.dialogUsersVisible = true;
                self.tableLoading = true;
                request({
                    params: {
                        r: 'mall/template-msg/users',
                        is_all: 0,
                        keyword: self.ruleForm.keyword,
                        platform: this.platform
                    },
                    method: 'get',
                }).then(e => {
                    self.tableLoading = false;
                    if (e.data.code == 0) {
                        self.users = e.data.data.users;
                        self.isChecked();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                });
            },
            // 多选
            handleSelectionChange(val) {
                var self = this;
                self.waitSelectUsers = val;
            },
            selectUserSubmit() {
                let self = this;
                self.btnLoading = true;
                self.waitSelectUsers.forEach(function (item, index) {
                    if (self.selectUsers.length > 0) {
                        try {
                            // 循环查询、如果是重复用户则不添加
                            self.selectUsers.forEach(function (item2, index2) {
                                if (item2.id == item.id) {
                                    throw new Error('finish')
                                }
                            });
                            if (self.selectUsers.length < 3) {
                                self.selectUsers.push(item)
                            }
                        } catch (e) {
                            if (e.message != 'finish') throw e;
                        }
                    } else {
                        if (self.selectUsers.length < 3) {
                            self.selectUsers.push(item)
                        }
                    }
                });
                self.saveTestUser();
            },
            deleteUser(index) {
                this.selectUsers.splice(index, 1)
                this.saveTestUser();
            },
            saveTestUser() {
                request({
                    params: {
                        r: 'mall/template-msg/add-test-user',
                    },
                    method: 'post',
                    data: {
                        user: this.selectUsers,
                        platform: this.platform,
                    }
                }).then(e => {
                    this.dialogUsersVisible = false;
                    this.btnLoading = false;
                    if (e.data.code == 0) {
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            getTestUser() {
                request({
                    params: {
                        r: 'mall/template-msg/test-user',
                        platform: this.platform,
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        this.selectUsers = e.data.data.users;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            isChecked() {
                let self = this;
                self.users.forEach(function (item, index) {
                    self.selectUsers.forEach(function (item2, index2) {
                        if (item.id === item2.id) {
                            self.$nextTick(() => {
                                self.$refs.multipleTable.toggleRowSelection(self.users[index], true);
                            });
                        }
                    })
                })
            },
            getPlatform() {
                let vars = getQuery('r').split("/");
                let p = vars[1];
                this.showOneKey = this.oneKey;
                if (['wxapp','bdapp','aliapp','ttapp'].indexOf(p) >= 0) {
                    this.platform = p;
                } else {
                    this.platform = 'wxapp';
                }
            },
            testQrcode() {
                this.templateVisible = true;
                if (this.qrcode) {
                    return ;
                }
                this.$request({
                    params: {
                        r: 'mall/template-msg/qrcode',
                        platform: this.platform,
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        this.qrcode = e.data.data
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            }
        },
        mounted: function () {
            this.getPlatform();
            this.getDetail();
            this.getTestUser();
        },
    });
</script>
