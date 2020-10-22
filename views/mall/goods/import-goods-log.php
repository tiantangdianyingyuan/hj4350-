<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .set-el-button {
        padding: 0 !important;
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
    <el-card v-loading="loading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div flex="dir:left box:last cross:center">
                <el-breadcrumb separator="/">
                    <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'mall/goods/import-data'})">批量导入</span>
                    </el-breadcrumb-item>
                    <el-breadcrumb-item>商品导入历史</el-breadcrumb-item>
                </el-breadcrumb>
                <div>
                    <form style="display: inline" target="_blank" :action_url="'<?= Yii::$app->request->baseUrl . '/index.php?r=' ?>' + 'mall/goods/import-goods'" method="post">
                        <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                        <input name="flag" value="EXPORT" type="hidden">
                        <button v-if="isDownload" type="submit" class="el-button el-button--primary el-button--mini">下载最新失败数据</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="table-body">
            <el-form size="small" :inline="true" :model="search">
                <el-form-item>
                    <div flex="dir:left">
                        <div flex="cross:center" style="height: 32px;">导入时间：</div>
                        <el-date-picker
                                size="small"
                                @change="changeTime"
                                v-model="datetime"
                                type="datetimerange"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                        </el-date-picker>
                    </div>
                </el-form-item>
                <el-form-item>
                    <div flex="dir:left">
                        <div style="margin-right: 10px">导入状态</div>
                        <el-select @change="getList" style="width: 150px" v-model="search.status">
                            <el-option
                                    v-for="item in statusList"
                                    :key="item.stauts"
                                    :value="item.status"
                                    :label="item.name">
                            </el-option>
                        </el-select>
                    </div>
                </el-form-item>
                <el-form-item>
                    <div flex="dir:left">
                        <div style="margin-right: 10px">操作人</div>
                        <el-select @change="getList" style="width: 150px" v-model="search.user_id">
                            <el-option
                                    v-for="item in userList"
                                    :key="item.user_id"
                                    :value="item.user_id"
                                    :label="item.nickname">
                            </el-option>
                        </el-select>
                    </div>
                </el-form-item>
            </el-form>
            <el-table border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column width="220" label="导入名称">
                    <template slot-scope="scope">
                        <el-tooltip effect="dark" placement="top">
                            <template slot="content">
                                <div style="width: 200px;">{{scope.row.file_name}}</div>
                            </template>
                            <div>
                                <app-ellipsis :line="1">{{scope.row.file_name}}</app-ellipsis>
                            </div>
                        </el-tooltip>
                    </template>
                </el-table-column>
                <el-table-column width='180' prop="created_at" label="导入时间"></el-table-column>
                <el-table-column prop="count" label="上传商品总数"></el-table-column>
                <el-table-column prop="success_count" label="成功商品数"></el-table-column>
                <el-table-column prop="error_count" label="失败商品数"></el-table-column>
                <el-table-column prop="status_cn" label="导入状态">
                    <template slot-scope="scope">
                        <span style="color: red;" v-if="scope.row.status_cn == '导入失败'">{{scope.row.status_cn}}</span>
                        <span v-else>{{scope.row.status_cn}}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="nickname" label="操作员"></el-table-column>
            </el-table>
            <div flex="main:right cross:center" style="margin-top: 20px;">
                <div v-if="pageCount > 0">
                    <el-pagination
                            @current-change="pagination"
                            background
                            :current-page="currentPage"
                            layout="prev, pager, next, jumper"
                            :page-count="pageCount">
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
                    start_date: '',
                    end_date: '',
                    status: -1,
                    user_id: 0,
                },
                datetime: [],
                userList: [
                    {
                        user_id: 0,
                        nickname: '全部'
                    },
                ],
                statusList: [
                    {
                        status: -1,
                        name: '全部'
                    },
                    {
                        status: 1,
                        name: '导入失败'
                    },
                    {
                        status: 2,
                        name: '导入成功'
                    },
                ],
                status: -1,
                pageCount: 0,
                currentPage: 1,
                page: 1,
                loading: false,
                list: [],
                isDownload: false,
            };
        },
        methods: {
            changeTime() {
                if (this.datetime) {
                    this.search.start_date = this.datetime[0];
                    this.search.end_date = this.datetime[1];
                } else {
                    this.search.start_date = null;
                    this.search.end_date = null;
                }
                this.getList();
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
            getList() {
                let self = this;
                self.loading = true;
                request({
                    params: {
                        r: 'mall/goods/import-goods-log',
                        page: self.page,
                        search: self.search,
                    },
                    method: 'get',
                }).then(e => {
                    self.loading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                    self.currentPage = e.data.data.pagination.current_page;
                    self.userList = e.data.data.user_list;
                    self.isDownload = e.data.data.is_download;
                }).catch(e => {
                    console.log(e);
                });
            },
        },
        mounted() {
            this.getList();
        }
    })
</script>
