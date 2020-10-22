
<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-info .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        display: inline-block;
        width: 250px;
        margin: 0 0 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .select {
        float: left;
        width: 100px;
        margin-right: 10px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>管理员列表</span>
                <el-button style="float: right; margin: -5px 0" type="primary" @click="dialogAdd = true" size="small">
                    添加管理员
                </el-button>
            </div>
        </div>
        <div class="table-body">
            <el-select size="small" v-model="platform" @change='search' class="select">
                <el-option key="0" label="全部平台" value="0"></el-option>
                <el-option key="wxapp" label="微信" value="wxapp"></el-option>
                <el-option key="aliapp" label="支付宝" value="aliapp"></el-option>
                <el-option key="ttapp" label="抖音/头条" value="ttapp"></el-option>
                <el-option key="bdapp" label="百度" value="bdapp"></el-option>
            </el-select>
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入用户昵称搜索" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table class="table-info" :data="form" border style="width: 100%" v-loading="listLoading">
                <el-table-column prop="user_id" label="ID" width="100"></el-table-column>
                <el-table-column label="头像" width="480">
                    <template slot-scope="scope">
                        <app-image mode="aspectFill" style="float: left;margin-right: 8px" :src="scope.row.avatar"></app-image>
                        <div>{{scope.row.nickname}}</div>
                        <img class="platform-img" v-if="scope.row.platform == 'wxapp'" src="statics/img/mall/wx.png" alt="">
                        <img class="platform-img" v-if="scope.row.platform == 'aliapp'" src="statics/img/mall/ali.png" alt="">
                        <img class="platform-img" v-if="scope.row.platform == 'bdapp'" src="statics/img/mall/baidu.png" alt="">
                        <img class="platform-img" v-if="scope.row.platform == 'ttapp'" src="statics/img/mall/toutiao.png" alt="">
                        <el-button @click="openId(scope.$index)" type="success" style="float:right;padding:5px !important;">显示OpenId</el-button>
                        <div v-if="scope.row.is_open_id">{{scope.row.platform_user_id}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="mobile" label="手机号" width="200">
                </el-table-column>
                <el-table-column prop="created_at" label="加入时间" wdith="220"></el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-tooltip v-if="scope.row.is_admin == 1" class="item" effect="dark" content="移除管理员" placement="top">
                            <el-button circle type="text" size="mini" @click="down(scope.row)">
                                <img src="statics/img/mall/down.png" alt="">
                            </el-button>
                        </el-tooltip>
                    </template>
                </el-table-column>
            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination @current-change="pagination" background layout="prev, pager, next, jumper"
                               :page-count="pageCount"></el-pagination>
            </div>
        </div>
    </el-card>
    <el-dialog title="设置管理员" :visible.sync="dialogAdd" width="30%">
        <el-form @submit.native.prevent :model="addForm" label-width="100px" :rules="addFormRules" ref="addForm">
            <el-form-item label="昵称搜索" prop="nickname">
                <el-autocomplete style="width: 50%" size="small" v-model="addForm.nickname" value-key="nickname"
                                 :fetch-suggestions="querySearchAsync" placeholder="请输入内容"
                                 @select="clerkClick"></el-autocomplete>
            </el-form-item>
            <el-form-item>
                <el-button size="small" style="float: right;padding: 0;width: 80px;height: 32px;margin-right: 20px" type="primary" @click="addSubmit" :loading="btnLoading">保存</el-button>
                <el-button size="small" style="float: right;padding: 0;width: 80px;height: 32px;margin-right: 20px" @click="dialogAdd = false">取消</el-button>
            </el-form-item>
        </el-form>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            let validatePass = (rule, value, callback) => {
                if (!this.addForm.hasOwnProperty('user_id') || !this.addForm.user_id) {
                    callback('请选择用户');
                } else {
                    callback();
                }
            };
            return {
                platform: '0',
                mall_members: [],
                keyword: '',
                form: [],
                member: [],
                dialogAdd: false,
                pageCount: 0,
                listLoading: false,
                btnLoading: false,
                addForm: {},
                addFormRules: {
                    nickname: [
                        {required: true, message: '用户不能为空', trigger: ['blur']},
                        {validator: validatePass, trigger: 'blur'}
                    ],
                },
            };
        },
        methods: {
        	querySearchAsync(queryString, cb) {
                this.userKeyword = queryString;
                this.clerkUser(cb);
            },

            clerkUser(cb) {
                request({
                    params: {
                        r: 'mall/user/index',
                        keyword: this.userKeyword,
                        is_admin: 2,
                        is_change_name: 1
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        cb(e.data.data.list);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },

            addSubmit() {
                this.$refs.addForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, this.addForm);
                        this.update(para);
                    }
                });
            },

            clerkClick(row) {
                //this.addForm = Object.assign({id: row.id}, this.addForm);
                this.addForm.user_id = row.user_id;
                this.$refs.addForm.validateField('nickname');
            },

            openId(index) {
                let item = this.form;
                item[index].is_open_id = !item[index].is_open_id;
                this.form = JSON.parse(JSON.stringify(this.form));
            },
            search() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/user/index',
                        page: this.page,
                        keyword: this.keyword,
                        is_admin: 1,
                        platform: this.platform
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data.list;
                        this.exportList = e.data.data.exportList;
                        this.pageCount = e.data.data.pagination.page_count;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },

            update(row) {
            	let that = this;
                request({
                    params: {
                        r: 'admin/user/bind'
                    },
                    data:{
                        user_id: row.user_id
                    },
                    method: 'post'
                }).then(e => {
                    if (e.data.code === 0) {
                        that.$message.success(e.data.msg);
                        that.dialogAdd = false;
                        that.form = [];
                        that.addForm = {};
                        that.getList();
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.listLoading = false;
                });            
            },
      
            down(row) {
                this.$confirm('确认移除管理员?', '提示', {
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'admin/user/destroy-bind'
                        },
                        data:{
                            id: row.user_id
                        },
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.$message.success(e.data.msg);
                            this.form = [];
                            this.getList();
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.listLoading = false;
                    });
                });
            },

            pagination(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/user/index',
                        page: this.page,
                        keyword: this.keyword,
                        is_admin: 1
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data.list;
                        this.exportList = e.data.data.exportList;
                        this.pageCount = e.data.data.pagination.page_count;
                        this.mall_members = e.data.data.mall_members;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
        },
        mounted: function () {
            this.getList();
        }
    });
</script>