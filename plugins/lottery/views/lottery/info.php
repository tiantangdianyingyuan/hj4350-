<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

?>
<style>
    .demo-table-expand {
        font-size: 0;
        padding:10px 50px;
      }
    .demo-table-expand label {
        width: 90px;
        color: #99a9bf;
      }
    .demo-table-expand .el-form-item {
        margin-right: 0;
        margin-bottom: 0;
        width: 50%;
      }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 200px;
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

    .table-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>参与详情</span>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input size="small" placeholder="请输入搜索内容" v-model="keyword">
                    <el-button slot="append" icon="el-icon-search" @click="userSearch"></el-button>
                </el-input>
            </div>
            <el-table :data="form" border style="width: 100%" v-loading="listLoading">
                <el-table-column prop="id" label="Id" width="100"></el-table-column>
                <el-table-column prop="user.nickname" label="用户信息"></el-table-column>
                <el-table-column prop="child" label="邀请人信息">
                    <template slot-scope="scope">
                        <div v-if="scope.row.child != null" v-text="scope.row.child.user.nickname"></div>
                    </template>
                </el-table-column>

                <el-table-column prop="id" label="中奖状态">
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.lottery_status == 3 || scope.row.lottery_status == 4">已中奖</el-tag>
                        <el-tag v-else-if="scope.row.lottery_status == 2">未中奖</el-tag>
                        <el-tag v-else>待开奖</el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="id" label="幸运码数量">
                    <template slot-scope="scope">
                        <el-button type="text" v-text="scope.row.lottery_num" @click="handleNum(scope.row)"></el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="id" label="是否内定">
                    <template slot-scope="scope">
                        <el-switch active-value="1" :disabled="scope.row.lottery_status == 2 || scope.row.lottery_status == 3 || scope.row.lottery_status == 4" inactive-value="0" @change="switchDefault(scope.row)" v-model="scope.row.lottery_default">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" width='200' label="创建时间"></el-table-column>
            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination @current-change="pagination" background layout="prev, pager, next, jumper" :page-count="pageCount"></el-pagination>
            </div>
        </div>
        <!--邀请列表-->
        <el-dialog title="邀请列表" :visible.sync="formChildVisible" width="50%" :close-on-click-modal="false">
            <el-table :data="formChild" v-loading="childLoading">
                <el-table-column property="childUser.nickname" label="基本信息"></el-table-column>
                <el-table-column property="lucky_code" label="获赠幸运码"></el-table-column>
                <el-table-column property="status" label="状态">
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.status == 0">未抽奖</el-tag>
                        <el-tag v-if="scope.row.status == 1">待开奖</el-tag>
                        <el-tag v-if="scope.row.status == 2">未中奖</el-tag>
                        <el-tag v-if="scope.row.status == 3">已中奖</el-tag>
                        <el-tag v-if="scope.row.status == 4">已领取</el-tag>
                    </template>
                </el-table-column>
                <el-table-column property="created_at" label="邀请时间"></el-table-column>
            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination @current-change="paginationChild" background layout="prev, pager, next, jumper" :page-count="pageCountChild"></el-pagination>
            </div>
        </el-dialog>
    </el-card>
</div>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            form: [],
            pageCount: 0,
            page: 0,
            listLoading: false,
            keyword:'',
            //邀请列表
            formChildVisible: false,

            formChild: [],
            pageChild: 0,
            pageCountChild: 0,
            childLoading: false,
            user_id: 0,
            lottery_id: 0,
            selfInfo: [],
        };
    },
    methods: {
        userSearch(){
            this.page = 1;
            this.getList();
        },
        switchDefault(row) {
            request({
                params: {
                    r: 'plugin/lottery/mall/lottery/default',
                },
                method: 'post',
                data: {
                    lottery_id: row.lottery_id,
                    user_id: row.user_id,
                    status: row.lottery_default
                }
            }).then(e => {
                if (e.data.code === 0) {
                    this.$message.success(e.data.msg);
                } else {
                    this.$message.error(e.data.msg);
                }
            }).catch(e => {});
        },
        paginationChild(currentPage) {
            this.pageChild = currentPage;
            this.getChild();
        },

        getChild() {
            this.childLoading = true;
            request({
                params: {
                    r: 'plugin/lottery/mall/lottery/get-child',
                    page: this.pageChild,
                    user_id: this.user_id,
                    lottery_id: this.lottery_id,
                },
            }).then(e => {
                if (e.data.code === 0) {
                    let list = e.data.data.list;
                    if (this.pageChild == 1) {
                        list.splice(0, 0, this.selfInfo);
                    }
                    this.formChild = list;
                    this.pageCountChild = e.data.data.pagination.page_count;
                } else {
                    this.$message.error(e.data.msg);
                }
                this.childLoading = false;
            }).catch(e => {
                this.childLoading = false;
            });
        },

        handleNum(row) {
            this.formChildVisible = true;
            this.pageChild = 1;
            this.lottery_id = row.lottery_id;
            this.user_id = row.user_id;
            this.selfInfo = {
                childUser: {
                    nickname: row.user.nickname + '（本人）',
                },
                lucky_code: row.lucky_code,
                status: row.status,
                created_at: row.created_at,
            }
            this.getChild();
        },
        editSubmit() {
            this.$refs.editForm.validate((valid) => {
                if (valid) {
                    this.btnLoading = true;
                    let para = Object.assign({}, this.editForm);
                    request({
                        params: {
                            r: 'plugin/lottery/mall/lottery/edit-sort',
                        },
                        method: 'post',
                        data: para,
                    }).then(e => {
                        this.btnLoading = false;
                        if (e.data.code == 0) {
                            this.$message.success(e.data.msg);
                            location.reload();
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.btnLoading = false;
                    });
                }
            })
        },

        //
        pagination(currentPage) {
            this.page = currentPage;
            this.getList();
        },

        getList() {
            this.listLoading = true;
            request({
                params: {
                    r: 'plugin/lottery/mall/lottery/info',
                    page: this.page,
                    lottery_id: getQuery('lottery_id'),
                    keyword:this.keyword,
                },
            }).then(e => {
                if (e.data.code === 0) {
                    this.form = e.data.data.list;
                    this.pageCount = e.data.data.pagination.page_count;
                } else {
                    this.$message.error(e.data.msg);
                }
                this.listLoading = false;
            }).catch(e => {
                this.listLoading = false;
            });
        },
    },
    mounted: function() {
        this.getList();
    }
});
</script>