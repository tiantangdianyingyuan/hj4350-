<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

?>
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

    .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus {
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

    .table-body .el-table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="listLoading">
        <div slot="header">
            <div>
                <span>机器人设置</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="editAdd" size="small">添加机器人</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div style="display: flex">
                <div class="input-item">
                    <el-input @keyup.enter.native="search" size="small" placeholder="请输入搜索内容" v-model="keyword" clearable @clear='search'>
                        <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                    </el-input>
                </div>
            </div>
            <el-table :data="list" border style="width: 100%">
                <el-table-column prop="id" label="ID" width="80"></el-table-column>
                <el-table-column prop="nickname" label="昵称"></el-table-column>
                <el-table-column prop="avatar" label="头像">
                    <template slot-scope="scope">
                        <app-image mode="aspectFill" :src="scope.row.avatar"></app-image>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="添加日期" width="220"></el-table-column>
                <el-table-column label="操作" width="150">
                    <template slot-scope="scope">
                        <el-button @click="edit(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="destroy(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination @current-change="pagination" background layout="prev, pager, next, jumper"
                               :page-count="pageCount"></el-pagination>
            </div>
        </div>
    </el-card>
    <!-- 更新分类 -->
    <el-dialog :title="dialogForm.id ? '编辑机器人' : '添加机器人' " :visible.sync="dialogRobotAdd" width="30%">
        <el-form :model="dialogForm" label-width="80px" :rules="dialogFormRules" ref="dialogForm" @submit.native.prevent>
            <el-form-item label="用户昵称" prop="nickname" size="small">
                <el-input  v-model="dialogForm.nickname" @keyup.enter.native="stop"></el-input>
            </el-form-item>
            <el-form-item label="用户头像" prop="avatar">
                <app-attachment :multiple="false" :max="1" v-model="dialogForm.avatar">
                    <el-tooltip class="item"
                                effect="dark"
                                content="建议尺寸:240 * 240"
                                placement="top">
                        <el-button size="mini">选择文件</el-button>
                    </el-tooltip>
                </app-attachment>
                <app-image mode="aspectFill" width='80px' height='80px' :src="dialogForm.avatar">
                </app-image>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogRobotAdd = false">取消</el-button>
            <el-button :loading="btnLoading" type="primary" @click="addRobotSubmit">提交</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                //新增
                dialogRobotAdd: false,
                dialogForm: {},
                dialogFormRules: {
                    avatar: [
                        {required: true, message: '用户头像', trigger: 'change'},
                    ],
                    nickname: [
                        {required: true, message: '用户昵称', trigger: 'change'},
                    ],
                },
                //排序
                keyword: '',
                list: [],
                pageCount: 0,
                listLoading: false,
                btnLoading: false,
            };
        },
        methods: {
            editAdd() {
                this.dialogForm = {};
                this.dialogRobotAdd = true;
            },
            stop() {},

            addRobotSubmit() {
                this.$refs.dialogForm.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/pintuan/mall/robot/edit',
                            },
                            method: 'post',
                            data: this.dialogForm,
                        }).then(e => {
                            if (e.data.code === 0) {
                                location.reload();
                            } else {
                                this.$message.error(e.data.msg);
                            }
                            this.btnLoading = false;
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            },

            edit(row) {
                let data  = JSON.parse(JSON.stringify(row));
                this.dialogForm = data;
                this.dialogRobotAdd = true;
            },
            // 搜索
            search() {
                this.getList();
            },

            //删除
            destroy: function (column) {
                this.$confirm('确认删除该机器人吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/pintuan/mall/robot/destroy',
                        },
                        data: {id: column.id},
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            location.reload();
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
                        r: 'plugin/pintuan/mall/robot/index',
                        page: this.page,
                        keyword: this.keyword
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pageCount = e.data.data.pagination.page_count;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>