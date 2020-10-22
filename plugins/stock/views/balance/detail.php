<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/29 15:59
 */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }
    .input-item {
        display: inline-block;
        width: 350px;
    }

    .input-item .el-input-group__prepend {
        background-color: #fff;
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
    .select {
        float: left;
        width: 100px;
        margin-right: 10px;
    }
    .el-tag {
        border-color: #409EFF;
        margin-right: 10px;
    }
</style>
<div id="app" v-cloak v-loading="loading">
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'plugin/stock/mall/balance/index'})">分红结算</span></el-breadcrumb-item>
                <el-breadcrumb-item>结算详情</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="table-body">
            <el-table :data="detail" border v-loading="loading" style="margin-bottom: 15px;width: 900px">
                <el-table-column label="结算周期" width="300">
                    <template slot-scope="scope">
                        <div flex="dir:left cross:center">
                            <el-tag size="small" v-if="scope.row.bonus_type == 1">按周</el-tag>
                            <el-tag size="small" v-else>按月</el-tag>
                            <div>{{scope.row.start_time}}~{{scope.row.end_time}}</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="订单数" prop="order_num"></el-table-column>
                <el-table-column label="分红金额" prop="price" width="250">
                    <template slot-scope="scope">
                        <div>￥{{scope.row.bonus_price}}({{scope.row.bonus_rate}}%分红比例)</div>
                    </template>
                </el-table-column>
                <el-table-column label="股东数" prop="stock_num"></el-table-column>
            </el-table>
            <div flex="dir:left cross:center" style="margin-bottom: 20px;">
                <div flex="dir:left cross:center">
                    <div style="margin-right: 10px;">股东等级</div>
                    <el-select size="small" v-model="level" @change="changeLevel" class="select">
                        <el-option :key="item.level_name" v-for="item in level_list" :label="item.level_name" :value="item.level_name"></el-option>
                    </el-select>
                    <div class="input-item">
                        <el-input @keyup.enter.native="toSearch" size="small" placeholder="请输入搜索内容" v-model="keyword_1" clearable @clear="toSearch">
                            <el-select size="small" v-model="keyword" slot="prepend" class="select">
                                <el-option key="1" label="昵称" :value="1"></el-option>
                                <el-option key="2" label="姓名" :value="2"></el-option>
                                <el-option key="3" label="手机号" :value="3"></el-option>
                            </el-select>
                            <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                        </el-input>
                    </div>
                </div>
            </div>

            <el-table :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;">
                <el-table-column label="基本信息" width="400">
                    <template slot-scope="scope">
                        <app-image style="float: left;margin-right: 5px;margin: 20px" mode="aspectFill" :src="scope.row.avatar"></app-image>
                        <div style="margin-top: 25px;">{{scope.row.nickname}}</div>
                        <img src="statics/img/mall/wx.png" v-if="scope.row.platform == 'wxapp'" alt="">
                        <img src="statics/img/mall/ali.png" v-else-if="scope.row.platform == 'aliapp'" alt="">
                        <img src="statics/img/mall/toutiao.png" v-else-if="scope.row.platform == 'ttapp'" alt="">
                        <img src="statics/img/mall/baidu.png" v-else-if="scope.row.platform == 'bdapp'" alt="">
                    </template>
                </el-table-column>
                <el-table-column label="姓名" width="250" prop="name">
                    <el-table-column label="手机号" prop="mobile">
                        <template slot-scope="scope">
                            <div>{{scope.row.name}}</div>
                            <div>{{scope.row.phone}}</div>
                        </template>
                    </el-table-column>
                </el-table-column>
                <el-table-column label="股东等级" width="150" prop="level_name">
                </el-table-column>
                <el-table-column label="等级分红比例" prop="rate">
                    <template slot-scope="scope">
                        <div>{{scope.row.bonus_rate}}%</div>
                    </template>
                </el-table-column>
                <el-table-column label="分红金额" prop="price">
                    <template slot-scope="scope">
                        <div>￥{{scope.row.price}}</div>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="box:last cross:center">
                <div></div>
                <div>
                    <el-pagination
                            v-if="list.length > 0"
                            style="display: inline-block;float: right;"
                            background :page-size="pagination.pageSize"
                            @current-change="pageChange"
                            layout="prev, pager, next, jumper" :current-page="pagination.current_page"
                            :total="pagination.totalCount">
                    </el-pagination>
                </div>
            </div>
        </div>
    </el-card>
</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {
                type: '1',
                level: '全部股东',
                level_name: '',
                keyword: 1,
                keyword_1: '',
                bonus_id: 0,
                loading: false,
                list: [],
                level_list: [],
                detail: [
                    {id: 1,start: '2019-04-01',end: '2019-04-07',type: 1,order_num: 685,price:'606.09',rate: 0.1,people_num: 4859,created_at:'2019-10-11 14:27:16'}],
                pagination: {}

            };
        },
        created() {
            this.bonus_id = getQuery('id');
            this.loadData();
        },
        methods: {
            changeLevel(e) {
                this.level_name = e != '全部股东' ? e : '';
                this.loadData();
            },
            loadData(page) {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/stock/mall/balance/detail',
                        bonus_id: this.bonus_id,
                        level_name: this.level_name,
                        keyword: this.keyword_1?this.keyword: '',
                        keyword_1: this.keyword_1,
                        bonus_id: this.bonus_id,
                        page: page ? page : 1
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.level_list = e.data.data.level_list;
                        let defult = {level_name:'全部股东'}
                        this.level_list.unshift(defult);
                        this.detail = [];
                        this.detail.push(e.data.data.bonus_data);
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            toSearch() {
                this.loadData();
            },
            pageChange(page) {
                this.loading = true;
                this.loadData(page);
            }
        } 
    })
</script>