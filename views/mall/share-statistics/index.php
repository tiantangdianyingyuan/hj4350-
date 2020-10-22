<?php defined('YII_ENV') or exit('Access Denied');
$url = Yii::$app->urlManager->createUrl(Yii::$app->controller->route);
Yii::$app->loadViewComponent('statistics/app-search');
Yii::$app->loadViewComponent('statistics/app-header');
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .sort-active {
        color: #3399ff;
    }

    .el-card__header {
        padding: 14px 20px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <app-header :url="url" :new-search="JSON.stringify(search)">分销排行</app-header>
        </div>
        <div style="margin-bottom: 10px;background: #ffffff">
            <app-search
                    @to-search="toSearch"
                    @search="searchList"
                    :new-search="search"
                    placeholder="请输入分销商名称或ID搜索"
                    :is-show-picker="false">
            </app-search>
        </div>
        <div class="table-body">
            <el-table v-loading="loading" @sort-change="changeSort"
                      :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}"
                      :data="list">
                <el-table-column width="120" prop="id" label="ID">
                </el-table-column>
                <el-table-column prop="name" label="分销商名称" width="180">
                    <template slot-scope="scope">
                        <div flex="dir:left">
                            <div style="flex-grow: 0">
                                <app-image style="margin-right: 10px;float: left;" :src="scope.row.avatar" width="48px"
                                           height="48px">
                            </div>
                            </app-image>
                            <div flex="dir:top">
                                <div>
                                    <img src="statics/img/mall/ali.png" v-if="scope.row.platform == 'aliapp'" alt="">
                                    <img src="statics/img/mall/wx.png" v-else-if="scope.row.platform == 'wxapp'" alt="">
                                    <img src="statics/img/mall/toutiao.png" v-else-if="scope.row.platform == 'ttapp'"
                                         alt="">
                                    <img src="statics/img/mall/baidu.png" v-else-if="scope.row.platform == 'bdapp'"
                                         alt="">
                                </div>
                                <app-ellipsis :line="1">{{scope.row.nickname}}</app-ellipsis>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="first_children" width="200" label="直接下级数量" sortable='custom'
                                 :label-class-name="chooseProp == 'first_children' ? 'sort-active': ''">
                </el-table-column>
                <el-table-column prop="all_children" width="200" label="总下级数" sortable='custom'
                                 :label-class-name="chooseProp == 'all_children' ? 'sort-active': ''">
                </el-table-column>
                <el-table-column prop="all_order" width="200" label="分销订单数量" sortable='custom'
                                 :label-class-name="chooseProp == 'all_order' ? 'sort-active': ''">
                </el-table-column>
                <el-table-column prop="all_money" width="200" label="总佣金" sortable='custom'
                                 :label-class-name="chooseProp == 'all_money' ? 'sort-active': ''">
                    <template slot="header" slot-scope="scope">
                        <span>总佣金</span>
                        <el-tooltip class="item" effect="dark" content="包括已完成订单和未完成订单" placement="bottom">
                            <i style="color: #92959B" class="el-icon-question"></i>
                        </el-tooltip>
                    </template>
                </el-table-column>
                <el-table-column prop="total_money" width="200" label="累计佣金" sortable='custom'
                                 :label-class-name="chooseProp == 'total_money' ? 'sort-active': ''">
                    <template slot="header" slot-scope="scope">
                        <span>累计佣金</span>
                        <el-tooltip class="item" effect="dark" content="包括已提现佣金和未提现佣金" placement="bottom">
                            <i style="color: #92959B" class="el-icon-question"></i>
                        </el-tooltip>
                    </template>
                </el-table-column>
                <el-table-column prop="price" width="200" label="已提现佣金" sortable='custom'
                                 :label-class-name="chooseProp == 'price' ? 'sort-active': ''">
                </el-table-column>
            </el-table>
            <div style="margin-top: 10px;" flex="box:last cross:center">
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
                            :current-page="pagination.current_page"
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
                chooseProp: null,
                list: [],
                pagination: [],
                page: 1,
                url: '<?= $url ?>',
                search: {
                    platform: '',
                    keyword: '',
                    order: null,
                }
            };
        },
        methods: {
            // 换页
            pageChange(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            // 获取列表
            getList() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/share-statistics/index',
                        name: this.search.name,
                        order: this.search.order,
                        page: this.page,
                        platform: this.search.platform,
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        for (let i = 0; i < e.data.data.export_list.length; i++) {
                            this.export_list.push(e.data.data.export_list[i].key)
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            changeSort(column) {
                this.loading = true;
                if (column.order == "descending") {
                    this.search.order = column.prop + ' DESC'
                } else if (column.order == "ascending") {
                    this.search.order = column.prop + ' ASC'
                } else {
                    this.search.order = null
                }
                this.chooseProp = column.prop;
                this.getList();
            },
            toSearch(searchData) {
                this.search = searchData;
                this.page = 1;
                this.getList();
            },
            searchList(searchData) {
                this.search = searchData;
                this.page = 1;
                this.getList();
            },
        },
        created() {
            this.getList();
        },
    })
</script>