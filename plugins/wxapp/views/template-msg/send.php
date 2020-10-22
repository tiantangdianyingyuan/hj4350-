<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .tag-title {
        margin: 15px 0 15px 40px;
        border: 1px solid;
    }

    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin: 20px 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 0;
    }

    .select-template-box {
        display: flex;
        flex-wrap: wrap
    }

    .select-template-box .el-radio__label {
        display: none;
    }

    .remove {
        color: #F56E6E;
        font-size: 22px;
    }

    .label {
        display: inline-block;
        width: 80px;
        text-align: right;
        margin-right: 10px;
    }

    .fields-input {
        width: 85%;
    }
</style>

<div id="app" v-cloak>
    <el-card v-loading="cardLoading" style="border:0" shadow="never"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>群发订阅消息</span>
            </div>
        </div>
        <el-form :model="ruleForm" ref="ruleForm" :rules="rules" size="small" label-width="120px">
            <div class="form-body">
                <el-row>
                    <el-form-item>
                        <el-col :span="14">
                            <el-tag style='border:0;width: 49%;min-width: 380px;margin-right: 10px;margin-bottom: 10px'
                                    type="danger">注意：群发订阅消息有被封号的风险，请谨慎使用！
                            </el-tag>
                            <el-tag style='border:0;width: 49%;min-width: 380px;' type="danger">
                                注意：订阅消息只发送给7天内在小程序内点击过的活跃用户
                            </el-tag>
                        </el-col>
                    </el-form-item>
                    <el-form-item label="发送对象">
                        <el-col :span="14">
                            <el-button :disabled="ruleForm.is_all" type="primary" @click="getUsers">添加用户</el-button>
                            <el-checkbox style="margin-left: 15px;" @change="selectAllUsers"
                                         v-model="ruleForm.is_all">所有用户
                            </el-checkbox>
                        </el-col>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-col :span="12">
                        <el-form-item>
                            <el-tag v-if="ruleForm.is_all === false"
                                    style="margin-right: 3px;margin-top: 5px;"
                                    closable
                                    v-for="(item, index) in selectUsers"
                                    :key="index"
                                    @close="deleteUser(index)">
                                {{item.nickname}}
                            </el-tag>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row>
                    <el-form-item label="选择模板">
                        <el-col :span="22">
                            <div v-if="templateList.length > 0"
                                 v-for="(item, index) in templateList"
                                 :key="index"
                                 style="margin-bottom: 30px;max-width: 900px"
                                 flex="box:last">
                                <div>
                                    <div class="select-template-box" style="float: left;margin-right: 10px;margin-top: 10px;">
                                        <el-radio v-model="ruleForm.current_index" :label="index"></el-radio>
                                    </div>
                                    <el-card shadow="never" class="box-card" style="margin-right: 5px;margin-top: -20px;">
                                        <div flex="box:last" style="margin-bottom: 5px">
                                            <div flex="dir:top">
                                                <div>
                                                    <div style="display: inline-block;width: 90px;margin-bottom: 10px;padding-right: 10px;text-align: right">
                                                        <el-tag style="border:0;font-size: 14px" type="warning">
                                                            {{item.style == 1 ? '小标题' : '大标题'}}
                                                        </el-tag>
                                                    </div>
                                                    <el-tag style="font-size: 14px" type="info">{{item.name}}</el-tag>
                                                </div>
                                                <div>
                                                    <span class="label">模板ID</span>
                                                    <span>{{item.tpl_id}}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <div v-for="(item2,index2) in item.fields"
                                                 :key="index2"
                                                 flex="dir:left"
                                                 style="margin-bottom: 5px">
                                                <span class="label">{{item2.field_name}}</span>
                                                <el-input class="fields-input" v-model="item2.field_value"
                                                          :placeholder="'请输入' + item2.field_name">
                                                </el-input>
                                            </div>
                                            <div flex="dir:left">
                                                <span class="label">模板链接Url</span>
                                                <el-input class="fields-input" v-model="item.link_url"
                                                          placeholder="例如: pages/index/index">
                                                </el-input>
                                            </div>
                                        </div>
                                    </el-card>
                                </div>
                                <div style="margin-left: -60px;margin-top: 20px">
                                    <el-button @click="destroyTemplate(index)" type="text" size="small" circle>
                                        <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                            <img src="statics/img/mall/del.png" alt="">
                                        </el-tooltip>
                                    </el-button>
                                </div>
                            </div>
                            <template v-if="templateList.length < 3">
                                <el-button style="margin-left: 20px;font-size: 14px;" @click="openDialog"
                                           type="text"><i class="el-icon-plus">新增模板</i></el-button>
                            </template>
                        </el-col>
                    </el-form-item>
                </el-row>
            </div>
            <el-button class="button-item" :loading="btnLoading" type="primary" @click="send" size="small">发送
            </el-button>
        </el-form>

        <el-dialog title="订阅消息格式" :visible.sync="dialogVisible">
            <el-form :model="dialogRuleForm" ref="dialogRuleForm" :rules="dialogRules" size="small" label-width="120px">
                <el-row>
                    <el-col :span="18">
                        <el-form-item label='模板名称' prop="name">
                            <el-input v-model="dialogRuleForm.name" placeholder="请输入模板名称"></el-input>
                        </el-form-item>
                        <el-form-item label='模板ID' prop="tpl_id">
                            <el-input v-model="dialogRuleForm.tpl_id" placeholder="请输入模板ID"></el-input>
                        </el-form-item>
                        <el-form-item label='首行样式' prop="style">
                            <el-radio v-model="dialogRuleForm.style" label="1">小标题</el-radio>
                            <el-radio v-model="dialogRuleForm.style" label="2">大标题</el-radio>
                        </el-form-item>
                        <el-form-item label='模版字段'>
                            <div v-for="(item,index) in dialogRuleForm.fields"
                                 :key="index"
                                 flex="dir:left"
                                 style="margin-bottom: 15px">
                                <el-input :placeholder="'请输入第' +(index + 1)+ '行模板名称'"
                                          v-model="item.field_name"
                                          class="input-with-select">
                                </el-input>
                                <el-button v-if="dialogRuleForm.fields.length > 1"
                                           type="text"
                                           @click="destroyField(index)"
                                           style="margin-left: 10px;padding-top: 5px">
                                    <i class="remove el-icon-remove-outline"></i>
                                </el-button>
                            </div>
                            <el-button type="text" @click="addField"><i class="el-icon-plus"></i>添加字段</el-button>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row>
                    <el-form-item>
                        <el-button :loading="btnLoading"
                                   style="float: right;"
                                   class="button-item"
                                   type="primary"
                                   @click="addTemplate('dialogRuleForm')"
                                   size="small">保存
                        </el-button>
                    </el-form-item>
                </el-row>
            </el-form>
        </el-dialog>

        <el-dialog title="用户列表" :visible.sync="dialogUsersVisible">
            <template>
                <el-input style="width: 260px;margin-bottom: 20px;" size="small" :disabled="ruleForm.is_all"
                          v-model="ruleForm.keyword"
                          placeholder="输入用户ID、昵称搜索">
                    <template slot="append">
                        <el-button :disabled="ruleForm.is_all" @click="getUsers">搜索</el-button>
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
                <el-button type="primary" @click="selectUserSubmit" size="small">确定</el-button>
            </div>
        </el-dialog>
        <el-dialog title="发送订阅消息" :visible.sync="progressVisible">
            <div style="margin: 10px 0;">
                总数{{selectUsers.length}}条,失败{{progressErrorCount}}条。
            </div>
            <el-progress :text-inside="true" :stroke-width="18" :percentage="progressCount"></el-progress>
            <div style="text-align: right;margin-top: 20px;">
                <el-button type="success" :loading="btnLoading" @click="sendConfirm" size="small">确定</el-button>
            </div>
            <div style="margin-top: 20px;" v-for="(item,index) in progressErrors" :key="index">
                用户: {{item.nickname}}(ID:{{item.id}}):{{item.value}}
            </div>

        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
            el: '#app',
            data() {
                return {
                    ruleForm: {
                        is_all: false,
                        current_index: '',
                    },
                    rules: {},
                    activeName: 'first',
                    btnLoading: false,
                    tableLoading: false,
                    cardLoading: false,
                    dialogVisible: false,
                    dialogUsersVisible: false,
                    templateList: [],
                    users: [],
                    selectUsers: [],
                    waitSelectUsers: [],
                    dialogRuleForm: {
                        name: '',
                        tpl_id: '',
                        style: '1',
                        link_url: '',
                        fields: [
                            {
                                field_name: '',
                                field_value: '',
                            },
                            {
                                field_name: '',
                                field_value: '',
                            },
                            {
                                field_name: '',
                                field_value: '',
                            },
                        ]
                    },
                    dialogRules: {
                        name: [
                            {required: true, message: '请输入模版名称', trigger: 'change'},
                        ],
                        tpl_id: [
                            {required: true, message: '请输入模版ID', trigger: 'change'},
                        ],
                        style: [
                            {required: true, message: '请选择样式', trigger: 'change'},
                        ],
                    },
                    progressVisible: false,
                    progressErrors: [],
                    progressCount: 0,
                    progressErrorCount: 0, //发送失败数
                    progressSendCount: 0, //发送总条数
                };
            },
            methods: {
                send() {
                    let self = this;
                    if (self.selectUsers.length == 0) {
                        self.$message.warning('请选择用户');
                        return;
                    }
                    if (self.ruleForm.current_index === '') {
                        self.$message.warning('请选择模板');
                        return;
                    }
                    self.btnLoading = true;
                    self.progressVisible = true;
                    let count = 100;
                    let usersCount = self.selectUsers.length;
                    let progressItem = (count / usersCount).toFixed(0);
                    self.selectUsers.forEach(function (item, index) {
                        request({
                            params: {
                                r: 'plugin/wxapp/template-msg/send'
                            },
                            method: 'post',
                            data: {
                                user_id: item.id,
                                form: self.ruleForm,
                                template: self.templateList[self.ruleForm.current_index]
                            }
                        }).then(e => {
                            self.btnLoading = false;
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
                    navigateTo({
                        r: 'plugin/wxapp/template-msg/send'
                    })
                },
                getUsers() {
                    let self = this;
                    self.btnLoading = true;
                    if (!self.ruleForm.is_all) {
                        self.dialogUsersVisible = true;
                        self.tableLoading = true;
                    }
                    request({
                        params: {
                            r: 'plugin/wxapp/template-msg/users',
                            is_all: self.ruleForm.is_all ? 1 : 0,
                            keyword: self.ruleForm.keyword
                        },
                        method: 'get',
                    }).then(e => {
                        self.btnLoading = false;
                        self.cardLoading = false;
                        self.tableLoading = false;
                        if (e.data.code == 0) {
                            self.waitSelectUsers = [];
                            self.users = e.data.data.users;
                            if (self.ruleForm.is_all) {
                                // 所有用户
                                self.users.forEach(function (item, index) {
                                    self.selectUsers.push(item)
                                })
                            }
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.$message.error(e.data.msg);
                        self.btnLoading = false;
                        self.cardLoading = false;
                        self.tableLoading = false;
                    });
                },
                // 添加模板
                addTemplate(formName) {
                    let self = this;
                    self.$refs[formName].validate((valid) => {
                        if (valid) {
                            self.btnLoading = true;
                            request({
                                params: {
                                    r: 'plugin/wxapp/template-msg/template'
                                },
                                method: 'post',
                                data: {
                                    form: self.dialogRuleForm,
                                }
                            }).then(e => {
                                self.btnLoading = false;
                                if (e.data.code == 0) {
                                    self.getTemplateList();
                                    self.$message.success(e.data.msg);
                                    self.dialogVisible = false;
                                } else {
                                    self.$message.error(e.data.msg);
                                }
                            }).catch(e => {
                                console.log(e);
                            });
                        } else {
                            console.log('error submit!!');
                            return false;
                        }
                    });
                },
                // 获取模板列表
                getTemplateList() {
                    let self = this;
                    self.cardLoading = true;
                    request({
                        params: {
                            r: 'plugin/wxapp/template-msg/template',
                        },
                        method: 'get',
                    }).then(e => {
                        self.cardLoading = false;
                        if (e.data.code == 0) {
                            self.templateList = e.data.data.list;
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                },
                // 删除模板
                destroyTemplate(index) {
                    let self = this;
                    self.$confirm('删除该条模板, 是否继续?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        self.cardLoading = true;
                        self.templateList.splice(index, 1);
                        request({
                            params: {
                                r: 'plugin/wxapp/template-msg/destroy-template'
                            },
                            method: 'post',
                            data: {
                                list: self.templateList,
                            }
                        }).then(e => {
                            self.cardLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            console.log(e);
                        });
                    }).catch(() => {
                        self.$message.info('已取消删除')
                    });
                },
                openDialog() {
                    this.dialogVisible = true;
                },
                // 删除模板字段
                destroyField(index) {
                    this.dialogRuleForm.fields.splice(index, 1);
                },
                // 添加模板字段
                addField() {
                    this.dialogRuleForm.fields.push({
                        field_name: '',
                    })
                },
                // 多选
                handleSelectionChange(val) {
                    var self = this;
                    self.waitSelectUsers = val;
                },
                // 选择所有用户
                selectAllUsers(e) {
                    this.selectUsers = [];
                    if (e) {
                        this.getUsers();
                        this.cardLoading = true;
                    }
                },
                deleteUser(index) {
                    this.selectUsers.splice(index, 1)
                },
                selectUserSubmit() {
                    let self = this;
                    self.waitSelectUsers.forEach(function (item, index) {
                        if (self.selectUsers.length > 0) {
                            try {
                                // 循环查询、如果是重复用户则不添加
                                self.selectUsers.forEach(function (item2, index2) {
                                    if (item2.id == item.id) {
                                        throw new Error('finish')
                                    }
                                });
                                self.selectUsers.push(item)
                            } catch (e) {
                                if (e.message != 'finish') throw e;
                            }
                        } else {
                            self.selectUsers.push(item)
                        }
                    });

                    this.dialogUsersVisible = false;
                }
            },
            mounted: function () {
                this.getTemplateList();
            }
        })
    ;
</script>
