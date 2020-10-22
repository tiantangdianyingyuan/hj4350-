<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
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
        padding: 15px;
    }

    .table-body .el-table .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .el-form-item {
        margin-bottom: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>用户列表</span>
            <el-button type="primary" style="float: right;margin-top: -5px" @click="destroyCurrency" size="small" :loading="deLoading">一键清空活力币</el-button>
        </div>
        <div class="table-body">
            <el-form @submit.native.prevent inline>
                <el-form-item>
                    <div class="input-item">
                        <el-input @keyup.enter.native="search" size="small" placeholder="请输入搜索内容" v-model="keyword">
                            <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
                <el-form-item v-if="delList.length != 0">
                    <el-button type="primary" @click="batchCurrency" size="small">批量编辑</el-button>
                </el-form-item>
            </el-form>
            <el-table v-loading="loading" border :data="list" @selection-change="handleSelectionChange" style="width: 100%;margin-bottom: 15px">
                <el-table-column type="selection" width="50"></el-table-column>
                <el-table-column align="center" width='100' prop="user_id" label="用户ID"></el-table-column>
                <el-table-column prop="user" label="基本信息">
                    <template slot-scope="scope">
                        <!-- <app-image mode="" width="40px" height="40px" :src="scope.row.user.avatar" style="float: left;margin-right: 10px"></app-image> -->
                        <div style="line-height: 40px;height: 40px;">{{scope.row.user.nickname}}</div>
                    </template>
                </el-table-column>
                <el-table-column align="center" prop="child_num" label="邀请人数">
                    <template slot-scope="scope">
                        <div style="color: #3399ff;cursor: pointer" @click="getPeople(scope.row)">{{scope.row.child_num}}</div>
                    </template>
                </el-table-column>
                <el-table-column align="center" prop="ratio" label="步数加成(千分之)"></el-table-column>
                <el-table-column align="center" width='200' prop="step_currency" label="活力币">
                    <template slot-scope="scope">
                        <div v-if="id != scope.row.id">
                            <span>{{scope.row.step_currency}}</span>
                            <el-button type="text" @click="change(scope.row)">
                                <img src="statics/img/mall/order/edit.png" alt="">
                            </el-button>
                        </div>
                        <div v-else>
                            <el-input type="number" size="mini" class="change" v-model="changePirce"
                                          autocomplete="off"></el-input>
                            <el-button class="change-quit" type="text" style="color: #F56C6C;" icon="el-icon-error"
                                           circle @click="quit(scope.row)"></el-button>
                            <el-button class="change-success" type="text" style="margin-left: 0;color: #67C23A"
                                       icon="el-icon-success" circle
                                       @click="changeExpressPrice(scope.row)"></el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column align="center" width='300' prop="created_at" label="创建时间">
                </el-table-column>
                <el-table-column align="center" width='150' label="详情" fixed="right">
                    <template slot-scope="scope">
                        <el-button circle size="mini" type="text" @click="toDetail(scope.row)">
                            <el-tooltip class="item" effect="dark" content="兑换记录" placement="top">
                                <img src="statics/img/mall/detail.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div  flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                        hide-on-single-page
                        :page-size="pagination.pageSize"  background @current-change="pageChange" layout="prev, pager, next, jumper" :total="pagination.total_count">
                </el-pagination>
            </div>
        </div>
    </el-card>
    <el-dialog title="邀请名单" :visible.sync="people_list">
        <el-table :data="invite_list" v-loading="invite_loading">
            <el-table-column property="user" label="基础信息">
                <template slot-scope="scope">
                    <!-- <app-image mode="" width="40px" height="40px" :src="scope.row.user.avatar" style="float: left;margin-right: 10px"></app-image> -->
                    <div style="line-height: 40px;height: 40px;">{{scope.row.user.nickname}}</div>
                </template>
            </el-table-column>
            <el-table-column property="invite_ratio" label="邀请加成(千分之)" width="200"></el-table-column>
            <el-table-column property="created_at" label="邀请时间" width="300"></el-table-column>
        </el-table>
    </el-dialog>

    <!--默认发件人信息-->
    <el-dialog title="步数币批量修改" :visible.sync="batchFormVisible" width="50%" :close-on-click-modal="false">
        <el-form :model="batchForm" label-width="100px" :rules="batchFormRules" ref="batchForm">
            <el-form-item label="步数币" prop="currency">
                <el-input v-model="batchForm.currency" type="number" style="width:50%" auto-complete="off"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button @click.native="batchFormVisible = false">取消</el-button>
            <el-button type="primary" @click.native="batchSubmit" :loading="batchLoading">提交</el-button>
        </div>
    </el-dialog>
</div>

<style>
    .change {
        width: 80px;
    }

    .change .el-input__inner {
        height: 22px !important;
        line-height: 22px !important;
    }

    .el-button.is-circle {
        padding: 12px 2px;
    }
</style>

<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            loading: false,
            people_list: false,
            invite_loading: false,
            list: [],
            invite_list: [],
            id: null,
            changePirce: 0,
            pagination: {},
            delList:[],
            keyword: null,
            deLoading: false,
            batchForm:{},
            batchLoading: false,
            batchFormVisible: false,
            batchFormRules: {},

        };
    },

    methods: {
        search() {
            this.getList(1);
        },

        destroyCurrency(row) {
            this.$confirm('是否清空用户活力币(无法恢复)?', '提示', {
                type: 'warning'
            }).then(() => {
                this.deLoading = true;
                let para = Object.assign(row);
                request({
                    params: {
                        r: 'plugin/step/mall/user/destroy-currency'
                    },
                    data: para,
                    method: 'post'
                }).then(e => {
                    this.deLoading = false;
                    if (e.data.code === 0) {
                        location.reload();
                    }
                }).catch(e => {
                    this.deLoading = false;
                });
            });
        },

        handleSelectionChange(e) {
            this.delList = e;
        },
        //分页
        pageChange(page) {
            this.getList(page);
        },
        batchCurrency() {
            if(this.delList.length == 0){
                this.$message.info('请勾选');
                return;
            }
            this.batchFormVisible = true;

        },
        batchSubmit(){
            this.$refs.batchForm.validate((valid) => {
                if (valid) {
                    this.batchLoading = true;
                    let ids = [];
                    this.delList.forEach(v => {
                        ids.push(v.id);
                    })
                    let para = Object.assign({ids: ids}, this.batchForm);
                    request({
                        params: {
                            r: 'plugin/step/mall/user/batch-currency'
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        this.batchLoading = false;
                        if (e.data.code === 0) {
                            this.$message.success(e.data.msg);
                            location.reload();
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    });
                }
            });
        },
        changeExpressPrice(row) {
            let type = 2;
            let step_id = row.id;
            let currency = this.changePirce;
            if(currency < 0) {
                this.$message.error('数量不能为负');
                return;                
            }
            if(currency == +row.step_currency) {
                this.id = null;
                return;
            }else if(currency > +row.step_currency) {
                currency = currency - row.step_currency;
                type = 1;
            }else if(currency < +row.step_currency) {
                currency = row.step_currency - currency;
                type = 2;
            }
            request({
                params: {
                    r: 'plugin/step/mall/user/edit-currency'
                },
                data:{
                    type: type,
                    step_id: step_id,
                    currency: currency,
                },
                method: 'post',
            }).then(e => {
                self.loading = false;
                if (e.data.code === 0) {
                    this.$message({
                        message: e.data.msg,
                        type: 'success'
                    });
                    this.getList();
                    this.id = null;
                } else {
                    this.$message.error(e.data.msg);
                }
            }).catch(e => {
                self.loading = false;
            });
        },

        change(row) {
            this.id = row.id;
            this.changePirce = row.step_currency;
        },

        quit() {
            this.id = null;
        },

        getPeople(row) {
            let id = row.id;
            let self = this;
            self.invite_loading = true;
            self.people_list = true;
            request({
                params: {
                    r: 'plugin/step/mall/user/invite',
                    id: id
                },
                method: 'get',
            }).then(e => {
                self.invite_loading = false;
                if (e.data.code === 0) {
                    this.invite_list = e.data.data.invite_list;
                } else {
                    this.$message.error(e.data.msg);
                }
            }).catch(e => {
                self.invite_loading = false;
            });
        },

        getList(page) {
            let self = this;
            self.loading = true;
            request({
                params: {
                    r: 'plugin/step/mall/user',
                    page: page,
                    keyword: self.keyword,
                },
                method: 'get',
            }).then(e => {
                self.loading = false;
                if (e.data.code === 0) {
                    this.pagination = e.data.data.pagination;
                    this.list = e.data.data.list;
                } else {
                    this.$message.error(e.data.msg);
                }
            }).catch(e => {
                self.loading = false;
            });
        },

        toDetail(row) {
            this.$navigate({
                r: 'plugin/step/mall/user/log',
                id: row.id
            })
        },
    },
    created() {
        this.getList();
    }
})
</script>