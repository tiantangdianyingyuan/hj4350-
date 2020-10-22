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
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .table-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .sort-input {
        width: 100%;
        background-color: #F3F5F6;
        height: 32px;
    }

    .sort-input span {
        height: 32px;
        width: 100%;
        line-height: 32px;
        display: inline-block;
        padding: 0 10px;
        font-size: 13px;
    }

    .sort-input .el-input__inner {
        height: 32px;
        line-height: 32px;
        background-color: #F3F5F6;
        float: left;
        padding: 0 10px;
        border: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>奖品列表</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small" @click="$navigate({r:'plugin/scratch/mall/scratch/edit'})">添加奖品
            </el-button>
        </div>
        <div class="table-body">
            <el-form size="small" :inline="true" :model="search">
                <!-- 搜索框 -->
                <el-form-item>
                    <el-select style="width: 120px" @change="scratchSearch" v-model="search.type">
                        <el-option label="全部" value="0"></el-option>
                        <el-option label="余额红包" value="1"></el-option>
                        <el-option label="优惠券" value="2"></el-option>
                        <el-option label="积分" value="3"></el-option>
                        <el-option label="赠品" value="4"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <div class="input-item">
                        <el-input @keyup.enter.native="scratchSearch" size="small" placeholder="请输入搜索内容" v-model="search.keyword" clearable @clear='scratchSearch'>
                            <el-button slot="append" icon="el-icon-search" @click="scratchSearch"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
            </el-form>
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column prop="type" label="奖品类型" width="220">
                    <template slot-scope="scope">
                        <span v-if="scope.row.type == 1">余额红包</span>
                        <span v-if="scope.row.type == 2">优惠券</span>
                        <span v-if="scope.row.type == 3">积分</span>
                        <span v-if="scope.row.type == 4">赠品</span>
                    </template>
                </el-table-column>
                <el-table-column prop="name" label="奖品明细">
                    <template slot-scope="scope">
                        <span v-if="scope.row.type == 1">{{ scope.row.price }}元</span>
                        <span v-if="scope.row.type == 2">{{ scope.row.coupon.name }}</span>
                        <span v-if="scope.row.type == 3">{{ scope.row.num }}</span>
                        <span v-if="scope.row.type == 4">{{ scope.row.goods_name }}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="stock" label="库存" width="150">
<!--                     <template slot-scope="scope">
                        <el-input v-focus v-if="editForm.id == scope.row.id" v-model="editForm.stock" @keyup.enter.native="editSubmit(scope.row)" class="sort-input"></el-input>
                        <div class="sort-input" v-else>
                            <span @click="editSort(scope.$index, scope.row)">{{scope.row.stock}}</span>
                        </div>
                    </template> -->
                    <template slot-scope="scope">
                        <div v-if="editForm.id != scope.row.id">
                            <el-tooltip class="item" effect="dark" content="排序" placement="top">
                                <span>{{scope.row.stock}}</span>
                            </el-tooltip>
                            <el-button class="edit-sort" type="text" @click="editSort(scope.row)">
                                <img src="statics/img/mall/order/edit.png" alt="">
                            </el-button>
                        </div>
                        <div style="display: flex;align-items: center" v-else>
                            <el-input style="min-width: 70px" type="number" size="mini" class="change" v-model="editForm.stock"
                                          autocomplete="off"></el-input>
                            <el-button class="change-quit" type="text" style="color: #F56C6C;padding: 0 5px" icon="el-icon-error"
                                           circle @click="quit()"></el-button>
                            <el-button class="change-success" type="text" style="margin-left: 0;color: #67C23A;padding: 0 5px"
                                       icon="el-icon-success" circle @click="editSubmit(scope.row)">
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="status" label="状态" width="100">
                    <template slot-scope="scope">
                        <el-switch v-model="scope.row.status" active-value="1" inactive-value="0" @change="toChosen(scope.row)">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column prop="updated_at" label="修改时间" width="200">
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="200">
                    <template slot-scope="scope">
                        <el-button type="text" size="mini" v-if="false" circle @click="handleEdit(scope.$index, scope.row)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" size="mini" circle @click="handleDel(scope.$index, scope.row,list.id)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    background
                    hide-on-single-page
                    :page-size="pagination.pageSize"
                    @current-change="pageChange"
                    layout="prev, pager, next, jumper"
                    :total="pagination.total_count">
                </el-pagination>
            </div>
        </div>
    </el-card>
</div>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            search: {
                keyword: '',
                type: '0',
            },
            list: [],
            coupon: [],
            loading: false,
            pagination: "",
            page:1,
            //修改门店
            dialogEdit: false,
            editForm: {
                id: 0,
                stock: 0,
            },
            editFormRules: {
                stock: [
                    { required: true, message: '库存不能为空', trigger: 'blur' },
                ],
            },
            btnLoading: false,
        };
    },

    directives: {
        // 注册一个局部的自定义指令 v-focus
        focus: {
            // 指令的定义
            inserted: function (el) {
                // 聚焦元素
                el.querySelector('input').focus()
            }
        }
    },

    methods: {
        scratchSearch(){
            this.page = 1;
            this.getList();
        },
        //分页
        pageChange(page) {
            this.list= [];
            this.page = page;
            this.getList();
        },
        editSubmit(row) {
            row.stock = this.editForm.stock;
            let para = Object.assign({}, this.editForm);
            this.btnLoading = true;
            request({
                params: {
                    r: 'plugin/scratch/mall/scratch/edit-stock'
                },
                data: para,
                method: 'post'
            }).then(e => {
                if (e.data.code == 0) {
                    this.$message.success(e.data.msg);
                    this.editForm.id = null;
                } else {
                    this.$alert(e.data.msg, '提示', {
                        confirmButtonText: '确定'
                    })
                }
                 this.btnLoading = false;
            }).catch(e => {});
        },

        editSort(row) {
            this.editForm.id = row.id;
            this.editForm.stock = row.stock;
        },

        quit() {
            this.editForm.id = null;
        },
        // 切换状态
        toChosen(val) {
            console.log(val)
            request({
                params: {
                    r: 'plugin/scratch/mall/scratch/edit-status'
                },
                data: {
                    id: val.id,
                    status: val.status,
                },
                method: 'post'
            }).then(e => {
                if (e.data.code == 0) {
                    this.$message({
                        message: '切换成功',
                        type: 'success'
                    });
                } else {
                    this.$alert(e.data.msg, '提示', {
                        confirmButtonText: '确定'
                    })
                }
            }).catch(e => {
                this.$alert(e.data.msg, '提示', {
                    confirmButtonText: '确定'
                })
            });
        },
        //带着ID前往编辑页面
        handleEdit: function(row, column) {
            navigateTo({ r: 'plugin/scratch/mall/scratch/edit', id: column.id });
        },
        //删除
        handleDel: function(row, column) {
            this.$confirm('确认删除该记录吗?', '提示', {
                type: 'warning'
            }).then(() => {
                let para = { id: column.id };
                request({
                    params: {
                        r: 'plugin/scratch/mall/scratch/destory'
                    },
                    data: para,
                    method: 'post'
                }).then(e => {
                    if (e.data.code === 0) {
                        const h = this.$createElement;
                        this.$message({
                            message: '删除成功',
                            type: 'success'
                        });
                        setTimeout(function() {
                            location.reload();
                        }, 300);
                    } else {
                        this.$alert(e.data.msg, '提示', {
                            confirmButtonText: '确定'
                        })
                    }
                }).catch(e => {
                    this.$alert(e.data.msg, '提示', {
                        confirmButtonText: '确定'
                    })
                });
            })
        },
        getList() {
            this.loading = true;
            request({
                params: {
                    r: 'plugin/scratch/mall/scratch',
                    page: this.page,
                    type: this.search.type,
                    keyword: this.search.keyword,
                },
            }).then(e => {
                if (e.data.code == 0) {
                    this.loading = false;
                    this.list = e.data.data.list;
                    this.pagination = e.data.data.pagination;
                }
            }).catch(e => {
            });
        },
    },
    mounted() {
        this.getList();
    }
})
</script>