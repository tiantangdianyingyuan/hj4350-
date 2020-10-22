<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/18
 * Time: 14:12
 */
Yii::$app->loadViewComponent('goods/app-batch');
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-table .el-button {
        padding: 0 !important;
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

    .content {
        padding: 0 5px;
        line-height: 20px;
        color: #E6A23C;
        background-color: #FCF6EB;
        width: auto;
        display: inline-block;
    }

    .select {
        float: left;
        width: 100px;
        margin-right: 10px;
    }
    .zero-stock {
        color: #ff4544;
    }

    .dialog-extra-title {
        position: absolute;
        top: 24px;
        left: 110px;
        font-size: 15px;
    }
    .info>div {
        width: 300px;
        height: 100px;
        background-color: #ECF5FE;
        margin-right: 20px;
        color: #999;
        padding-left: 30px;
        margin-bottom: 20px;
    }

    .info .about {
        margin-top: 8px;
        font-size: 16px;
        color: #353535;
    }

    .sort-active {
        color: #3399ff;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span style="color: #3399ff;cursor: pointer;" @click="routeGo('plugin/community/mall/middleman/index')">团长管理</span>
            <span style="margin: 0 5px;">/</span>
            <span>团长详情</span>
        </div>
        <div class="table-body">
                <el-table v-if="middleman.length > 0" :header-cell-style="{backgroundColor: '#ecf5ff',padding: '5px 0 0',textAlign: 'center'}" :data="middleman" border style="margin-bottom: 15px;width: 600px">
                    <el-table-column prop="name">
                        <template slot="header" slot-scope="scope">
                            <div style="height: 28px;">
                                <img style="width: 20px;height: 20px;display: inline-block;margin-right: 5px" src="statics/img/plugins/middleman.png" alt="">
                                <div style="height: 20px;line-height: 20px;color: #6fb1ff">团长</div>
                            </div>
                        </template>
                        <template slot-scope="prop">
                            <div style="text-align: center">{{prop.row.name}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="children_count">
                        <template slot="header" slot-scope="scope">
                            <div style="height: 28px;">
                                <img style="width: 20px;height: 20px;display: inline-block;margin-right: 5px" src="statics/img/plugins/middleman-member.png" alt="">
                                <div style="height: 20px;line-height: 20px;color: #6fb1ff">团员人数</div>
                            </div>
                        </template>
                        <template slot-scope="prop">
                            <div style="text-align: center">{{prop.row.children_count}}</div>
                        </template>
                    </el-table-column>
                </el-table>
            <div flex="dir:left cross:center" style="margin-bottom: 20px;">
                <div class="input-item">
                    <el-input @keyup.enter.native="toSearch" size="small" placeholder="请输入搜索内容" v-model="search.keyword" clearable @clear="toSearch">
                        <el-select size="small" v-model="search.type" slot="prepend" class="select">
                            <el-option key="1" label="团员昵称" value="name"></el-option>
                            <el-option key="4" label="团员ID" value="id"></el-option>
                        </el-select>
                        <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                    </el-input>
                </div>
            </div>
            <div style="width: 780px">
                <el-table :header-cell-style="{backgroundColor: '#F5F7FA'}" :data="list" border v-loading="loading" style="margin-bottom: 25px;">
                    <el-table-column label="ID" prop="id" width="80">
                        <template slot-scope="scope">
                            <span>{{scope.row.id}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="基本信息" prop="nickname" width="450">
                        <template slot-scope="scope">
                            <app-image style="float: left;margin-right: 2px;margin: 20px" mode="aspectFill" :src="scope.row.avatar"></app-image>
                            <div style="margin-top: 25px;">{{scope.row.nickname}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-button @click="release(scope.row.id)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="解除关系" placement="top">
                                    <img src="statics/img/plugins/release.png" alt="">
                                </el-tooltip>
                            </el-button>
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
                                layout="prev, pager, next" 
                                :current-page="pagination.current_page"
                                :total="pagination.totalCount">
                        </el-pagination>
                    </div>
                </div>
            </div>
        </div>
    </el-card>
</div>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/js/jspdf.debug.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/js/jspdf.min.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/js/html2canvas.js"></script>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                id: null,
                list: [],
                search: {
                    keyword: '',
                    type: 'name'
                },
                middleman: [],
                pagination: {
                    pageSize: 1
                }
            }
        },
        created() {
            this.id = getQuery('id');
            this.loadData();
        },
        methods: {
            routeGo(r) {
                this.$navigate({
                    r: r
                });
            },
            release(id) {
                let that = this;
                that.$confirm('确认解除关系?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    that.loading = true;
                    request({
                        params: {
                            r: 'plugin/community/mall/middleman/relieve',
                            user_list: id,
                            id: that.id,
                        },
                        method: 'get',
                    }).then(e => {
                        that.loading = false;
                        if (e.data.code == 0) {
                            that.$message({
                                type: 'success',
                                message: e.data.msg
                            });
                            that.loadData();
                        } else {
                            that.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        that.loading = false;
                    });
                })
            },
            pageChange(page) {
                this.search.page = page;
                this.loadData();
            },
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/community/mall/middleman/detail',
                        id: this.id,
                        prop_value: this.search.keyword,
                        prop: this.search.type
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    console.log(this.loading)
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.middleman = [e.data.data.middleman];
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            toSearch() {
                this.search.page = 1;
                this.loadData();
            }
        }
    });
</script>