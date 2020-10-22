<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

?>
<style>
    .table-body {
        padding: 20px 20px 0 20px;
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
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .table-body .el-table .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .el-form-item {
        margin-bottom: 0;
    }

    .el-alert .el-alert__description {
        margin-top: 0;
    }

    .el-alert .el-button {
        padding: 0
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>快速购买商品分类</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="editAdd" size="small">添加分类</el-button>
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
                <el-alert type="info" style="height: 32px;padding: 0 16px;margin-left: 10px;width: auto" :closable="false">
                    <template>
                        温馨提示：商品分类修改至“<el-button type="text" @click="$navigate({r:'mall/cat/index'}, true)">商品管理-分类</el-button>”修改
                    </template>
                </el-alert>
            </div>
            <el-table v-loading="listLoading" :data="form" border height="620" style="width: 100%">
                <el-table-column prop="id" label="ID" width="80"></el-table-column>
                <el-table-column label="分类名称">
                    <template slot-scope="scope">{{scope.row.cats.name}}</template>
                </el-table-column>
                <el-table-column label="图标">
                    <template slot-scope="scope">
                        <app-image mode="aspectFill" :src="scope.row.cats.pic_url"></app-image>
                    </template>
                </el-table-column>
                <el-table-column label="排序">
                    <template slot-scope="scope">
                        <div v-if="id != scope.row.id">
                            <el-tooltip class="item" effect="dark" content="排序" placement="top">
                                <span>{{scope.row.sort}}</span>
                            </el-tooltip>
                            <el-button class="edit-sort" type="text" @click="editSort(scope.row)">
                                <img src="statics/img/mall/order/edit.png" alt="">
                            </el-button>
                        </div>
                        <div style="display: flex;align-items: center" v-else>
                            <el-input style="min-width: 70px" type="number" size="mini" class="change" v-model="sort"
                                          autocomplete="off"></el-input>
                            <el-button class="change-quit" type="text" style="color: #F56C6C;padding: 0 5px" icon="el-icon-error"
                                           circle @click="quit()"></el-button>
                            <el-button class="change-success" type="text" style="margin-left: 0;color: #67C23A;padding: 0 5px"
                                       icon="el-icon-success" circle @click="sortSubmit(scope.row)">
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="添加日期" width="220"></el-table-column>
                <el-table-column label="操作" width="150" fixed="right">
                    <template slot-scope="scope">
                        <el-button @click="destroy(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div  flex="dir:right" style="margin-top: 20px;">
                <el-pagination @current-change="pagination" hide-on-single-page background  layout="prev, pager, next, jumper" :page-count="pageCount"></el-pagination>
            </div>
        </div>
    </el-card>
    <!-- 更新分类 -->
    <el-dialog title="添加分类" :visible.sync="dialogSortAdd" width="50%">
        <el-form :model="sortAddForm" label-width="60px" :rules="sortAddFormRules" ref="sortAddForm">
            <el-form-item label="分类" prop="cat_list" size="small">
                <el-cascader style="width: 50%" v-model="sortAddForm.cat_list" :options="cats" :show-all-levels="false" change-on-select :props="defaultProps"></el-cascader>
            </el-form-item>
            <el-form-item label="排序" prop="sort" size="small">
                <el-input style="width: 50%" v-model="sortAddForm.sort"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="dialogSortAdd = false">取消</el-button>
            <el-button size="small" :loading="btnLoading" type="primary" @click="sortAddSubmit">提交</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                //新增
                dialogSortAdd: false,
                sortAddForm: {},
                id: null,
                sort: 0,
                sortAddFormRules: {
                    cat_list: [
                        { required: true, message: '分类ID不能为空', trigger: 'blur' },
                    ],
                },
                defaultProps: {
                    children: 'child',
                    label: 'name',
                    value: 'id',
                },

                //排序
                dialogSort: false,
                sortForm: {},
                sortFormRules: {},
                keyword: '',
                form: [],
                cats: {},
                pageCount: 0,
                listLoading: false,
                btnLoading: false,
            };
        },
        methods: {
            quit() {
                this.id = null;
            },

            editAdd() {
                this.dialogSortAdd = true;
            },

            sortAddSubmit() {
                this.$refs.sortAddForm.validate((valid) => {
                    if (valid) {
                        let cat_list = this.sortAddForm.cat_list;
                        let cat_id = cat_list[cat_list.length - 1];
                        let para = Object.assign(this.sortAddForm, { cat_id: cat_id });

                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/quick-shop/edit',
                            },
                            method: 'post',
                            data: para,
                        }).then(e => {
                            if (e.data.code === 0) {
                                location.reload();
                            } else {
                                this.$message.error(e.data.msg);
                            }
                            this.btnLoading = false;
                            this.dialogSort = false;
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            },
            editSort(row) {
                this.id = row.id;
                this.sort = row.sort;
            },
            // 搜索
            search() {
                this.page = 1;
                this.getList();
            },


            sortSubmit(row) {
                let para = row;
                para.sort = this.sort;
                this.btnLoading = true;
                request({
                    params: {
                        r: 'mall/quick-shop/edit-sort',
                    },
                    method: 'post',
                    data: para,
                }).then(e => {
                    if (e.data.code === 0) {
                        this.$message({
                            message: e.data.msg,
                            type: 'success'
                        });
                        this.id = null;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.btnLoading = false;
                    this.dialogSort = false;
                }).catch(e => {
                    this.btnLoading = false;
                });
            },

            //删除
            destroy: function(column) {
                this.$confirm('确认删除该记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'mall/quick-shop/destroy',
                        },
                        data: { id: column.id },
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.getList();
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
                        r: 'mall/quick-shop/index',
                        page: this.page,
                        keyword: this.keyword,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.listLoading = false;
                        this.form = e.data.data.list;
                        this.cats = e.data.data.cats;
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
        mounted: function() {
            this.getList();
        }
    });
</script>