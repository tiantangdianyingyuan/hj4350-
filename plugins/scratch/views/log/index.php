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
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>刮刮卡抽奖记录</span>
        </div>
        <div class="table-body">
            <el-form size="small" :inline="true" :model="search">
                <el-form-item>
                    <el-select style="width: 100px" @change="typeSearch(search.status)" v-model="search.status">
                        <el-option label="全部" value="0"></el-option>
                        <el-option label="余额红包" value="1"></el-option>
                        <el-option label="优惠券" value="2"></el-option>
                        <el-option label="积分" value="3"></el-option>
                        <el-option label="赠品" value="4"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>                  
                    <div class="input-item">
                        <el-input @keyup.enter.native="scratchSearch(search.keyword)" size="small" placeholder="请输入用户名" v-model="search.keyword" clearable @clear='scratchSearch(search.keyword)'>
                            <el-button slot="append" icon="el-icon-search" @click="scratchSearch(search.keyword)"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
            </el-form>
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column prop="nickname" label="用户"></el-table-column>
                <el-table-column prop="type" label="奖品分类" width="100">
                    <template slot-scope="scope">
                        <span v-if="scope.row.type == 1">
                            余额红包
                        </span>
                        <span v-else-if="scope.row.type == 2">
                            优惠券
                        </span>
                        <span v-else-if="scope.row.type == 3">
                            积分
                        </span>
                        <span v-else-if="scope.row.type == 4">
                            赠品
                        </span>
                    </template>
                </el-table-column>
                <el-table-column prop="name" label="奖品明细" width="350">
                </el-table-column>
                <el-table-column prop="status" label="领取状态" width="150">
                    <template slot-scope="scope" v-if="scope.row.status != 0">
                        <el-switch v-model="scope.row.status" active-value="2" inactive-value="1" disabled>
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="抽奖时间" width="250">
                </el-table-column>
                <el-table-column prop="raffled_at" label="领取时间" width="250">
                    <template slot-scope="scope">
                        <span v-if="scope.row.raffled_at > scope.row.created_at">{{ scope.row.raffled_at }}</span>
                    </template>
                </el-table-column>
            </el-table>
            <div class="fixed-pagination">

                    <el-pagination :page-size="20" hide-on-single-page background @current-change="pageChange" layout="prev, pager, next, jumper" :total="pagination.total_count">
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
                status: '0',
            },
            loading: false,
            list: [],
            pagination: "",
            page:1,
        };
    },

    methods: {
        //分页
        pageChange(page) {
            this.page = page;
            this.getList();
        },
        // 分类筛选
        typeSearch(val) {
            this.loading = true;
            if (val == '0') {
                val = null
            }
            request({
                params: {
                    r: 'plugin/scratch/mall/log',
                    type: val
                },
            }).then(e => {
                this.loading = false;
                if (e.data.code == 0) {
                    this.list = e.data.data.list;
                }
            }).catch(e => {

            });
        },
        // 搜索
        scratchSearch(val) {
            this.loading = true;
            request({
                params: {
                    r: 'plugin/scratch/mall/log',
                    nickname: val
                },
            }).then(e => {
                this.loading = false;
                if (e.data.code == 0) {
                    this.list = e.data.data.list;
                }
            }).catch(e => {

            });
        },
        getList() {
            this.loading = true;
            request({
                params: {
                    r: 'plugin/scratch/mall/log',
                    page: this.page,
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
    created() {
        this.getList();
    }
})
</script>