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
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/step/mall/user/index'})">用户列表</span></el-breadcrumb-item>
                <el-breadcrumb-item>详情</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="table-body">
            <el-form size="small" :inline="true" :model="search">
                <el-form-item>
                    <!-- 时间选择框 -->
                    <el-date-picker
                            v-model="search.time"
                            type="datetimerange"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期">
                    </el-date-picker>
                </el-form-item>
                <el-form-item>
                    <div class="input-item">
                        <el-input size="small" placeholder="请输入搜索内容" v-model="search.keyword">
                            <el-button slot="append" icon="el-icon-search" @click="searchOrder"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
            </el-form>
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column align="center" width='80' prop="type" label="类型">
                    <template slot-scope="scope">
                        <el-tag type="success" v-if="scope.row.type == 1">收入</el-tag>
                        <el-tag type="danger" v-if="scope.row.type == 2">支出</el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="data" label="活动/商品"></el-table-column>
                <el-table-column width='150' prop="currency" label="收支情况(活力币)">
                    <template slot-scope="scope">
                        <div style="font-size: 16px;color: #67C23A" v-if="scope.row.type == 1"><span v-if="scope.row.currency > 0">+</span>{{scope.row.currency}}</div style="font-size: 16px;color: #ff4544">
                        <div style="font-size: 16px;color: #ff4544" v-else-if="scope.row.type == 2"><span v-if="scope.row.currency > 0">-</span>{{scope.row.currency}}</div style="font-size: 16px;color: #ff4544">
                    </template>
                </el-table-column>
                <el-table-column prop="remark" label="详情"></el-table-column>
                <el-table-column width='200' prop="created_at" label="创建时间">
                </el-table-column>
            </el-table>
            <div flex="box:last cross:center">
                <div style="visibility: hidden">
                    <el-button plain type="primary" size="small">批量操作1</el-button>
                    <el-button plain type="primary" size="small">批量操作2</el-button>
                </div>
                <div>
                    <el-pagination v-if="pagination"
                            :page-size="pagination.pageSize" style="display: inline-block;float: right;" background @current-change="pageChange" layout="prev, pager, next, jumper" :total="pagination.total_count">
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
            search: {
                time: null,
                keyword: '',
                date_start: '',
                date_end: '',
            },
            loading: false,
            people_list: false,
            list: []
        };
    },

    methods: {
        //分页
        pageChange(page) {

        },
        // 搜索
        searchOrder() {
            if(this.search.time != null) {
                this.search.date_start = this.search.time[0];
                this.search.date_end = this.search.time[1];
            }else {
                this.search.date_start = '';
                this.search.date_end = '';
            }
            this.loading = true;
            request({
                params: this.search,
            }).then(e => {
                this.loading = false;
                if (e.data.code == 0) {
                    this.list = e.data.data.list;                  
                    let detail = [];
                    for(let i = 0;i < this.list.length;i++) {
                        this.list[i].detail = [this.list[i].detail]
                    }
                    this.pagination = e.data.data.pagination;
                }

            }).catch(e => {
            });
        },
        getList(id) {
            let self = this;
            self.loading = true;
            request({
                params: {
                    r: 'plugin/step/mall/user/log',
                    id: id
                },
                method: 'get',
            }).then(e => {
                self.loading = false;
                if (e.data.code === 0) {
                    self.list = e.data.data.list;
                } else {
                    self.$message.error(e.data.msg);
                }
            }).catch(e => {
                self.loading = false;
            });
        },
    },
    created() {
        let id = getQuery('id');
        this.getList(id);
    }
})
</script>