<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */
?>
<style>
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

    .attr-template .close {
        position: absolute;
        top: -4px;
        right: -4px;
        font-size: 16px;
        cursor: pointer;
        background: #ffffff;
    }

    .attr-template .attr-list {
        display: inline-block;
        margin-right: 10px;
        margin-bottom: 10px;
        position: relative;
        cursor: move;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .attr-name {
        background: #f3f3f3;
        color: #666666;
        display: inline-block;
        line-height: 32px;
        margin: 5px;
        border-radius: 5px;
        padding: 0 15px;
    }
</style>
<div id="app" v-cloak class="material material-dialog">
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <el-breadcrumb separator="/" style="display: inline-block">
                    <el-breadcrumb-item>
                        <span>规格模板</span>
                    </el-breadcrumb-item>
                </el-breadcrumb>
                <div style="float: right; margin: -5px 0">
                    <el-button type="primary" @click="handleAdd" size="small">新增模板管理</el-button>
                </div>
            </div>
        </div>

        <div class="table-body">
            <!--工具条 过滤表单和新增按钮-->
            <el-col :span="24" class="toolbar" style="padding-bottom: 0px">
                <div class="input-item">
                    <el-input @keyup.enter.native="searchList"
                              size="small"
                              placeholder="请输入规格名或规格值搜索"
                              v-model="search.keyword"
                              clearable
                              @clear="searchList">
                        <el-button slot="append" icon="el-icon-search" @click="searchList"></el-button>
                    </el-input>
                </div>
            </el-col>

            <!--列表-->
            <el-table :data="list" v-loading="listLoading" style="width: 100%;" border>
                <el-table-column prop="attr_group_name" label="规格名" width="300"></el-table-column>
                <el-table-column prop="attr_list" label="规格值">
                    <template slot-scope="scope">
                        <span v-for="(item,index) in scope.row.attr_list" :key="index">
                            <div class="attr-name">{{item.attr_name}}</div>
                        </span>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="180" fixed="right">
                    <template slot-scope="scope">
                        <el-button @click="handleEdit(scope.$index,scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="handleDel(scope.$index,scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <!--工具条 分页-->
            <el-col  flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                        background
                        hide-on-single-page
                        layout="prev, pager, next, jumper"
                        @current-change="pageChange"
                        :page-size="pagination.pageSize"
                        :total="pagination.total_count"
                        style="float:right;margin:15px 0">
                </el-pagination>
            </el-col>
        </div>
        <!--新增界面-->
        <el-dialog :title="title" :visible.sync="ruleFormVisible" class="attr-template">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="120px"
                     @submit.native.prevent>
                <el-form-item label="规格名" prop="attr_group_name" style="width: 450px">
                    <el-input v-model="ruleForm.attr_group_name" placeholder="请输入规格名"></el-input>
                </el-form-item>
                <el-form-item label="规格值" prop="attr_list">
                    <draggable :options="{draggable:'.attr-list'}" @end="makeAttrGroup" :mask="false"
                               flex="dir:left" style="flex-wrap: wrap" v-model="ruleForm.attr_list">
                        <div class="attr-list" v-for="(attr, j) in ruleForm.attr_list" :key="j">
                            <el-input style="width:152px"
                                      size="mini"
                                      type="text"
                                      placeholder="请输入规格值"
                                      v-model.trim="attr.attr_name"
                            ></el-input>
                            <i class="el-icon-error close" @click="handleAttrListDel(j)"></i>
                        </div>
                        <div slot="footer">
                            <el-button type="text" @click="addAttrList">添加规格值</el-button>
                        </div>
                    </draggable>
                </el-form-item>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button size="small" @click.native="ruleFormVisible = false">取消</el-button>
                <el-button size="small" type="primary" :loading="btnLoading" @click="editSubmit">保存</el-button>
            </span>
        </el-dialog>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                listLoading: false,
                search: {
                    keyword: '',
                },
                page: 1,
                list: [],
                pagination: {},
                btnLoading: false,
                ruleForm: {},
                ruleFormVisible: false,
                rules: {
                    attr_group_name: [
                        {required: true, message: '规格名不能为空', trigger: 'blur'},
                        {
                            required: true, type: 'string', validator: (rule, value, callback) => {
                                let preg = /[\'"\\=]/;
                                if (preg.test(value)) {
                                    callback(`规格名称不能包含\ ' " \\ =等特殊符`);
                                }
                                callback();
                            }
                        }
                    ],
                    attr_list: [
                        {required: true, message: '规格值不能为空', trigger: 'change'},
                        {
                            required: true, type: 'array', validator: (rule, value, callback) => {
                                let s = new Set();
                                let sentinel = false;
                                let preg = /[\'"\\=]/;
                                let status = value.every(item => {
                                    if (preg.test(item.attr_name)) {
                                        sentinel = true;
                                    }
                                    s.add(item.attr_name);
                                    return item.attr_name;
                                });
                                if (sentinel) {
                                    callback(`规格名称不能包含\ ' " \\ =等特殊符`);
                                }

                                if (!status) {
                                    callback('规格值不能为空');
                                }
                                if (s.size !== value.length) {
                                    callback('规格值不能重复');
                                }
                                callback();
                            }
                        }
                    ],
                },
                title: '新增规格模板',
            };
        },
        mounted() {
            this.getList();
        },
        methods: {
            makeAttrGroup() {
                console.log(1);
            },
            addAttrList() {
                this.ruleForm.attr_list.push({
                    attr_id: 1,
                    attr_name: '',
                    pic_url: '',
                });
            },
            handleAttrListDel(index) {
                this.ruleForm.attr_list.splice(index, 1);
            },
            pageChange(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            searchList() {
                this.page = 1;
                this.getList();
            },
            handleAdd() {
                this.title = '新增规格模板';
                this.ruleForm = {
                    attr_group_name: '',
                    attr_group_id: 0,
                    attr_list: [],
                };
                this.ruleFormVisible = true;
            },
            handleEdit(index, row) {
                this.title = '编辑规格模板';
                this.ruleForm = JSON.parse(JSON.stringify(row));
                this.ruleFormVisible = true;
            },
            editSubmit() {
                const self = this;
                self.$refs.ruleForm.validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/goods-attr-template/post'
                            },
                            method: 'POST',
                            data: {
                                id: self.ruleForm.id,
                                attr_group_name: self.ruleForm.attr_group_name,
                                attr_group_id: self.ruleForm.attr_group_id,
                                attr_list: self.ruleForm.attr_list,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            self.ruleFormVisible = false;
                            if (e.data.code === 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({r: 'mall/goods-attr-template/index'});
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.btnLoading = false;
                        });
                    }
                });
            },
            handleDel(index, row) {
                this.$confirm('确认删除该记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    let para = {id: row.id};
                    request({
                        params: {
                            r: 'mall/goods-attr-template/destroy'
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.list.splice(index, 1);
                            this.$message.success(e.data.msg);
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    })
                })
            },
            getList() {
                const self = this;
                self.listLoading = true;
                const params = Object.assign({r: 'mall/goods-attr-template/index',page: self.page}, self.search);
                request({
                    params,
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pagination = e.data.data.pagination;
                }).catch(e => {
                    self.listLoading = false;
                });
            },
        }
    });
</script>
