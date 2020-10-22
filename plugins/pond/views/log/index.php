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
            <span>抽奖记录</span>
        </div>
        <div class="table-body">
            <el-form size="small" :inline="true" :model="search">
    <!--             <el-form-item>
                    <el-select style="width: 100px" v-model="search.status1">
                        <el-option label="全部用户" value="0"></el-option>
                        <el-option label="微信" value="1"></el-option>
                        <el-option label="支付宝" value="2"></el-option>
                    </el-select>
                </el-form-item> -->
                <el-form-item>
                    <el-select style="width: 100px" @change="typeSearch(search.status)" v-model="search.status">
                        <el-option label="全部" value="0"></el-option>
                        <el-option label="余额红包" value="1"></el-option>
                        <el-option label="优惠券" value="2"></el-option>
                        <el-option label="积分" value="3"></el-option>
                        <el-option label="赠品" value="4"></el-option>
                        <el-option label="谢谢参与" value="5"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <div class="input-item">
                        <el-input @keyup.enter.native="pondSearch(search.keyword)" size="small" placeholder="请输入用户名" v-model="search.keyword" clearable @clear='pondSearch(search.keyword)'>
                            <el-button slot="append" icon="el-icon-search" @click="pondSearch(search.keyword)"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
            </el-form>
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column prop="nickname" label="用户" width="330"></el-table-column>
                <el-table-column prop="name" label="物品"></el-table-column>
                <el-table-column prop="status" label="领取状态" width="150">
                    <template slot-scope="scope">
                        <!-- <el-switch v-model="scope.row.status" active-value="1" inactive-value="0" disabled> </el-switch> -->
                        <el-tooltip class="item" effect="dark" content="已领取" placement="top" v-if="scope.row.name == '谢谢参与'">
                            <app-image src="./../plugins/pond/assets/img/already.png" height="20px" width="20px"></app-image>
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" content="已领取" placement="top" v-else-if="scope.row.status == 1">
                            <app-image src="./../plugins/pond/assets/img/already.png" height="20px" width="20px"></app-image>
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" content="未领取" placement="top" v-else-if="scope.row.status == 0">
                            <app-image src="./../plugins/pond/assets/img/not.png" height="20px" width="20px"></app-image>
                        </el-tooltip>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="抽奖时间" width="250">
                </el-table-column>
                <el-table-column prop="raffled_at" label="领取时间" width="250">
                    <template slot-scope="scope">
                        <span v-if="scope.row.name == '谢谢参与'">{{ scope.row.created_at }}</span>
                        <span v-else-if="scope.row.raffled_at != '0000-00-00 00:00:00'">{{ scope.row.raffled_at }}</span>
                    </template>
                </el-table-column>
            </el-table>    
            <div class="fixed-pagination">

                    <el-pagination
                        :page-size="20"
                        hide-on-single-page
                        style="display: inline-block;float: right;"
                        background
                        @current-change="pageChange"
                        layout="prev, pager, next, jumper"
                        :total="pagination.total_count"
                    >
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
            pagination: null,
            page:1,
        };
    },

    methods: {
        //分页
        pageChange(page) {
            this.page = page;
            this.getList();
        },
        // 选择器筛选
        typeSearch(val) {
            this.loading = true;
            request({
                params: {
                    r: 'plugin/pond/mall/log',
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
        // 关键词搜索
        pondSearch(val) {
            this.loading = true;
            request({
                params: {
                    r: 'plugin/pond/mall/log',
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
                    r: 'plugin/pond/mall/log',
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