<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .set-el-button {
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

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        display: inline-block;
        width: 250px;
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

</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>专题</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small"
                       @click="$navigate({r:'mall/topic/edit'})">新增
            </el-button>
        </div>
        <div class="table-body">
            <el-form size="small" :inline="true" :model="search">
                <el-form-item>
                    <el-select @change="onSubmit" style="width: 150px" v-model="value">
                        <el-option value="" label="全部专题"></el-option>
                        <el-option
                          v-for="item in select"
                          :key="item.id"
                          :value="item.id"
                          :label="item.name">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <div class="input-item">
                        <el-input @keyup.enter.native="onSubmit" size="small" placeholder="请输入搜索内容" v-model="keyword" clearable @clear="onSubmit">
                            <el-button slot="append" icon="el-icon-search" @click="onSubmit"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
            </el-form>
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column width="100" prop="id" label="ID"></el-table-column>
                <el-table-column width='120' prop="topicType.name" label="分类"></el-table-column>
                <el-table-column prop="title" label="专题">
                    <template slot-scope="scope">
                        <div flex="box:first">
                            <div style="padding-right: 10px">
                                <app-image mode="aspectFill" :src="scope.row.cover_pic"></app-image>
                            </div>
                            <div>
                                <app-ellipsis :line="1">{{scope.row.title}}</app-ellipsis>
                                <app-ellipsis :line="1" class="created-time">{{scope.row.created_at}}</app-ellipsis>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column width='120' prop="layout" label="布局方式">
                    <template slot-scope="scope">
                        <span v-if="scope.row.layout == 0">小图模式</span>
                        <span v-else-if="scope.row.layout == 1">大图模式</span>
                        <span v-else-if="scope.row.layout == 2">多图模式</span>
                    </template>
                </el-table-column>
                <el-table-column prop="is_chosen" label="是否精选" width="100">
                    <template slot-scope="scope">
                        <el-switch v-model="scope.row.is_chosen" :active-value="1" :inactive-value="0" @change="toChosen(scope.row)">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column prop="sort" label="排序" width="200">
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
                                       icon="el-icon-success" circle @click="change(scope.row)">
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="150" fixed="right">
                    <template slot-scope="scope">
                        <el-button type="text" class="set-el-button" size="mini" circle @click="handleEdit(scope.$index, scope.row,list.id)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" class="set-el-button" size="mini" circle @click="handleDel(scope.$index, scope.row)">
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
<script src="<?= Yii::$app->request->baseUrl ?>/statics/ueditor/ueditor.config.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/ueditor/ueditor.all.min.js"></script>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                select:[],
                pagination:'',
                keyword: '',
                value:'',
                loading:false,
                search:null,
                id: 0,
                sort: 0,
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
            quit() {
                this.id = null
            },

            editSort(row) {
                this.id = row.id;
                this.sort = row.sort;
            },
            change(row) {
                let self = this;
                row.sort = self.sort;
                request({
                    params: {
                        r: 'mall/topic/edit-sort'
                    },
                    method: 'post',
                    data: {
                        id: self.id,
                        sort: self.sort
                    },
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                        this.id = null;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                    self.btnLoading = false;
                });
            },
            // 搜索
            onSubmit:function(){
                this.loading = true;
                let search = {
                    page:1,
                    type:this.value,
                    keyword:this.keyword,
                }
                request({
                    params: {
                        r: 'mall/topic/index',
                        search: JSON.stringify(search),
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.select = e.data.data.select;
                        this.pagination = e.data.data.pagination;
                    }
                }).catch(e => {
                    this.loading = false;
                    this.$alert(e.data.msg, '提示', {
                      confirmButtonText: '确定'
                    })
                });
            },
            //切换是否精选
            toChosen: function(val) {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/topic/edit-chosen',
                    },
                    data: {
                        id: val.id,
                        is_chosen: val.is_chosen
                    },
                    method: 'post'
                }).then(e => {
                    this.loading = false;
                    if(e.data.code == 0){
                        if(val.is_chosen == 1) {
                            this.$message.success('已设置为精选');
                        }else if(val.is_chosen == 0) {
                            this.$message.success('已取消精选');
                        }
                    }else{
                        this.$alert(e.data.msg, '提示', {
                            confirmButtonText: '确定'
                        })
                    }   
                }).catch(e => {
                    this.loading = false;
                    this.$alert(e.data.msg, '提示', {
                        confirmButtonText: '确定'
                    })
                });
            },
            // 选择页数
            pageChange: function(page){
                this.loading = true;
                this.list = [];
                loadList('mall/topic',page).then(e => {
                    this.loading = false;
                    this.list = e.list;
                    this.select = e.select;
                    this.pagination = e.pagination;
                })   
            },
            //带着ID前往编辑页面
            handleEdit: function(row, column)
            {
                navigateTo({r: 'mall/topic/edit',id:column.id});
            },

            //删除
            handleDel: function(index, row) {
                let _this = this;
                this.$confirm('确认删除该记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    let para = { id: row.id};
                    request({
                        params: {
                            r: 'mall/topic/destroy'
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        const h = this.$createElement;
                        this.$message({
                            message: '删除成功',
                            type: 'success'
                        });
                        setTimeout(function(){
                            location.reload();
                        },300);
                    }).catch(e => {
                        this.$alert(e.data.msg, '提示', {
                          confirmButtonText: '确定'
                        })
                    });
                })
            }
        },
        mounted() {
            this.loading = true;
            // 获取列表
            loadList('mall/topic').then(e => {
                this.loading = false;
                this.list = e.list;
                this.select = e.select;
                this.pagination = e.pagination;
            });
        }
    })
</script>
