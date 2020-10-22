<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/23
 * Time: 15:09
 */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .templat-list {
        border: 1px solid #EBEEF5;
    }

    .templat-item {
        line-height: 40px;
        height: 40px;
        display: flex;
        font-size: 13px;
    }

    .templat-item:nth-child(odd) {
        background-color: #f7f7f7;
    }

    .templat-item:nth-child(even) {
        background-color: #fff;
    }

    .templat-item div {
        width: 50%;
        padding-left: 20px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>页面管理</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small"
                       @click="$navigate({r:'plugin/diy/mall/page/edit'})">新增页面
            </el-button>
        </div>
        <div style="padding: 20px;background-color: #fff">
            <el-table v-loading="loading" border :data="list" style="margin-bottom: 15px;">
                <el-table-column prop="id" label="ID" width="100px"></el-table-column>
                <el-table-column prop="title" label="标题"></el-table-column>
                <el-table-column prop="template" label="导航与模板" width="750px">
                    <template slot-scope="scope">
                        <div class="templat-list">
                            <div class="templat-item" v-for="item in scope.row.navs">
                                <div>{{item.navs}}</div>
                                <div style="color: #999999;">{{item.template}}</div>
                            </div>

                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="设为首页" width="80px">
                    <template slot-scope="scope">
                        <el-switch v-model="scope.row.is_home_page" :inactive-value="0" :active-value="1"
                                   @change="homePageChange(scope.row,scope.$index)"></el-switch>
                    </template>
                </el-table-column>
                <el-table-column label="禁用/启用" width="85px">
                    <template slot-scope="scope">
                        <el-switch v-model="scope.row.is_disable" :inactive-value="1" :active-value="0"
                                   @change="disableChange(scope.row,scope.$index)"></el-switch>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="180px">
                    <template slot-scope="scope">
                        <el-button circle size="mini" type="text"
                                   @click="$navigate({r:'plugin/diy/mall/page/edit',id:scope.row.id})">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button :loading="scope.row.loading" circle size="mini" type="text"
                                   @click="deleteItem(scope.row,scope.$index)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <el-pagination
                v-if="pagination"
                style="display: inline-block;float: right;"
                background
                :page-size.sync="pagination.pageSize"
                @current-change="pageChange"
                layout="prev, pager, next, jumper"
                :total="pagination.totalCount">
        </el-pagination>
    </el-card>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                page: 0,
                list: [],
                pagination: null
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            loadData() {
                this.loading = true;
                this.$request({
                    params: {
                        r: 'plugin/diy/mall/page/index',
                        page: this.page,
                    }
                }).then(response => {
                    this.loading = false;
                    if (response.data.code === 0) {
                        this.pagination = response.data.data.pagination;
                        for (let i in response.data.data.list) {
                            response.data.data.list[i].loading = false;
                        }
                        this.list = response.data.data.list;
                    }
                }).catch(e => {
                });
            },
            pageChange(page) {
                this.page = page;
                this.loadData();
            },
            deleteItem(item, index) {
                this.$confirm('确认删除？').then(() => {
                    item.loading = true;
                    item.is_delete = 1;
                    this.update(item).then(() => {
                        this.list.splice(index, 1);
                    }).catch(() => {
                        item.loading = false;
                    });
                }).catch(() => {
                });
            },
            homePageChange(item, index) {
                this.update(item).then(() => {
                    location.reload();
                });
            },
            disableChange(item, index) {
                this.update(item);
            },
            update(item) {
                return new Promise(((resolve, reject) => {
                    this.$request({
                        params: {
                            r: 'plugin/diy/mall/page/update',
                        },
                        method: 'post',
                        data: item,
                    }).then(response => {
                        if (response.data.code === 0) {
                            this.$message.success(response.data.msg);
                            resolve();
                        } else {
                            this.$message.error(response.data.msg);
                            reject();
                        }
                    }).catch(e => {
                    });
                }));
            }
        },
    });
</script>