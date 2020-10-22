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

    .award-name .label-name {
        flex-shrink: 0;
        margin-right: 5px
    }

    .award-name .value-name {
        margin-right: 5px;
        margin-bottom: 5px;
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>签到记录</span>
        </div>
        <div class="table-body">
            <!--工具条 过滤表单和新增按钮-->
            <el-col :span="24" class="toolbar">
                <el-form size="small" :inline="true" :model="search">
                    <!-- 搜索框 -->
                    <el-form-item prop="time">
                        <span style="color:#606266">签到时间：</span>
                        <el-date-picker
                                v-model="search.time"
                                @change="searchList"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                type="datetimerange"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item>
                        <div class="input-item">
                            <el-input @keyup.enter.native="searchList" size="small" placeholder="请输入用户昵称搜索"
                                      v-model="search.keyword" clearable @clear='searchList'>
                                <el-button slot="append" icon="el-icon-search" @click="searchList"></el-button>
                            </el-input>
                        </div>
                    </el-form-item>
                </el-form>
            </el-col>
            <!--列表-->
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column prop="user_id" label="用户ID" width="120"></el-table-column>
                <el-table-column prop="user_name" label="用户" width="280">
                    <template slot-scope="scope">
                        <app-image mode="aspectFill" style="float: left;margin-right: 8px"
                                   :src="scope.row.avatar"></app-image>
                        <div flex="dir:left cross:center">
                            {{scope.row.nickname}}
                        </div>
                        <img v-if="scope.row.platform == 'wxapp'" src="statics/img/mall/wx.png" alt="">
                        <img v-if="scope.row.platform == 'aliapp'" src="statics/img/mall/ali.png" alt="">
                        <img v-if="scope.row.platform == 'bdapp'" src="statics/img/mall/baidu.png" alt="">
                        <img v-if="scope.row.platform == 'ttapp'" src="statics/img/mall/toutiao.png" alt="">
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="签到时间" width="180"></el-table-column>
                <el-table-column prop="type" label="赠送内容" :formatter="formatterType"></el-table-column>
            </el-table>

            <!--工具条 批量操作和分页-->
            <el-col :span="24" class="toolbar">
                <el-pagination
                        background
                        layout="prev, pager, next, jumper"
                        @current-change="pageChange"
                        :page-size="pagination.pageSize"
                        :total="pagination.total_count"
                        style="float:right;margin-bottom:15px"
                        v-if="pagination">
                </el-pagination>
            </el-col>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                loading: false,
                pagination: null,
                page: 1,
                search: {
                    time: null,
                },
            };
        },
        methods: {
            formatterType(row) {
                let label;
                switch (parseInt(row.status)) {
                    case 1:
                        label = '普通签到赠送:';
                        break;
                    case 2:
                        label = '连续签到赠送:';
                        break;
                    case 3:
                        label = '累计签到赠送:';
                        break;
                    default:
                        label = '';
                        break;
                }
                if (row.type === 'integral') {
                    return label + parseInt(row.number) + '积分';
                }
                if (row.type === 'balance') {
                    return label + parseFloat(row.number) + '余额';
                }
                return '';
            },
            searchList() {
                this.page = 1;
                this.getList();
            },
            pageChange(page) {
                this.page = page;
                this.getList();
            },
            getList() {
                this.loading = true;
                let param = Object.assign({r: 'plugin/check_in/mall/index/log'}, this.search, {page: this.page});
                request({
                    params: param,
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
        },
        mounted() {
            this.getList();
        }
    })
</script>