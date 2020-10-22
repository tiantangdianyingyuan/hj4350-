<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .input-item {
        display: inline-block;
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

    .table-body {
        padding: 20px;
        background-color: #fff;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>自动发放</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small"
                       @click="$navigate({r:'mall/coupon-auto-send/edit'})">添加自动发放方案
            </el-button>
        </div>
        <div class="table-body">
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column prop="id" label="ID"></el-table-column>
                <el-table-column prop="event" label="触发事件">
                  <template slot-scope="scope">
                    <span v-if="scope.row.event == 1">分享</span>
                    <span v-else-if="scope.row.event == 2">购买并付款</span>
                    <span v-else-if="scope.row.event == 3">新人领券</span>
                  </template>
                </el-table-column>
                <el-table-column prop="coupon.name" label="优惠券">
                </el-table-column>
                <el-table-column prop="send_count" label="发放次数限制">
                    <template slot-scope="scope">
                        <span v-if="scope.row.send_count == 0">不限制</span>
                        <span v-else>{{scope.row.send_count}}</span>
                    </template>
                </el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button size="mini" circle type="text" @click="handleEdit(scope.$index, scope.row,list.id)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>                        
                        </el-button>
                        <el-button size="mini" circle type="text" @click="handleDel(scope.$index, scope.row,list.id)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="box:last cross:center">
                <div style="visibility: hidden">
                    <el-button plain type="primary" size="small">批量操作1</el-button>
                    <el-button plain type="primary" size="small">批量操作2</el-button>
                </div>
                <div>
                    <el-pagination
                            v-if="pagination"
                            style="display: inline-block;float: right;"
                            background
                            :page-size="pagination.pageSize"
                            @current-change="pageChange"
                            layout="prev, pager, next, jumper"
                            :total="pagination.total_count">
                    </el-pagination>
                </div>
            </div>
        </div>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                list: [],
                pagination: null,
            };
        },

        methods: {
            //带着ID前往编辑页面
            handleEdit: function(row, column)
            {
                navigateTo({r: 'mall/coupon-auto-send/edit',id:column.id});
            },

            //分页
            pageChange(page) {
                this.loading = true;
                loadList('mall/coupon-auto-send/index',page).then(e => {
                    this.loading = false;
                    this.list = e.list;
                    this.pagination = e.pagination;
                });
            },

            //删除
            handleDel: function(row, column) {
                this.$confirm('确认删除该记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    let para = { id: column.id};
                    request({
                        params: {
                            r: 'mall/coupon-auto-send/destroy'
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
                        setTimeout(function(){
                            location.reload();
                        },300);
                    }else{
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
            }
        },
        created() {
            this.loading = true;
            // 获取列表
            loadList('mall/coupon-auto-send/index').then(e => {
                this.loading = false;
                this.list = e.list;
                this.pagination = e.pagination;
            });
        }
    })
</script>
