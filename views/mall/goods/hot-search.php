<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .t-omit-two {
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
        white-space: normal !important;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" flex="dir:left" style="justify-content:space-between;">
            <span>商品热搜</span>
            <el-button @click="$navigate({r:'mall/goods-hot-search/edit'})" style="margin: -5px 0" type="primary"
                       size="small">新建热搜商品
            </el-button>
        </div>
        <div class="table-body">
            <el-table v-loading="listLoading" :data="list" border>
                <el-table-column prop="goods_id" label="商品ID" width="100"></el-table-column>
                <el-table-column prop="goods_name" label="商品名称" min-width="300" width="400">
                    <template slot-scope="scope">
                        <div flex="dir:left">
                            <div style="padding-right: 10px;">
                                <app-image mode="aspectFill" :src="scope.row.cover_pic"></app-image>
                            </div>
                            <div flex="cross:center">
                                <div class="t-omit-two">
                                    {{scope.row.goods_name}}
                                </div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="title" label="热搜词">
                    <template slot-scope="scope">
                        <div flex="cross:center">
                            <div class="t-omit-two">
                                {{scope.row.title}}
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="type" label="获取方式" width="250">
                    <template slot-scope="scope">
                        <el-tag size="medium" v-if="scope.row.type === 'goods'">自动获取</el-tag>
                        <el-tag size="medium" v-if="scope.row.type === 'hot-search'" type="success">手动添加</el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="sort" label="热搜名次" width="250">
                    <template slot-scope="scope">
                        <div v-if="is_sort == scope.row.sort" style="display: flex;align-items: center">
                            <el-input style="min-width: 70px"
                                      type="text"
                                      size="mini"
                                      class="change"
                                      v-model="temp_sort"
                                      maxlength="10"
                                      oninput="this.value = this.value.match(/10|[1-9]/)"
                                      autocomplete="off"
                            ></el-input>
                            <el-button class="change-quit"
                                       type="text"
                                       style="color: #F56C6C;padding: 0 5px"
                                       icon="el-icon-error"
                                       circle
                                       @click="is_sort = -1"
                            ></el-button>
                            <el-button class="change-success"
                                       type="text"
                                       style="margin-left: 0;color: #67C23A;padding: 0 5px"
                                       icon="el-icon-success" circle
                                       @click="submit(scope.row)">
                            </el-button>
                        </div>
                        <div v-else flex="cross:center">
                            <img v-if="scope.row.sort == 1"
                                 src="statics/img/mall/goods/hot-search/list_icon_first.png"
                                 alt=""
                            >
                            <img v-if="scope.row.sort == 2"
                                 src="statics/img/mall/goods/hot-search/list_icon_second.png"
                                 alt=""
                            >
                            <img v-if="scope.row.sort == 3"
                                 src="statics/img/mall/goods/hot-search/list_icon_third.png"
                                 alt=""
                            >
                            <span v-text="formatSort(scope.row.sort)"></span>
                            <span v-if="scope.row.type === 'hot-search'"
                                  @click="editSort(scope.row.sort,scope.row)"
                                  style="cursor: pointer;padding-left: 5px">
                                <img src="statics/img/mall/order/edit.png" alt="">
                            </span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="120" fixed="right">
                    <template slot-scope="scope">
                        <el-button type="text" @click="edit(scope.row)"
                                   size="small" circle>
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button class="set-el-button" size="mini" type="text" circle @click="destroy(scope.row,scope.$index)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <!--工具条 批量操作和分页-->
        <el-col :span="24" class="toolbar">
            <el-pagination
                    background
                    layout="prev, pager, next"
                    @current-change="pageChange"
                    :page-size="pagination.pageSize"
                    :total="pagination.total_count"
                    style="float:right;margin-bottom:15px"
                    v-if="pagination">
            </el-pagination>
        </el-col>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                listLoading: false,
                list: [],
                pagination: null,
                is_sort: -1,
                temp_sort: '',
            };
        },
        mounted() {
            this.getList();
        },
        methods: {
            formatSort(sort) {
                if (sort < 4) {
                    return '';
                }
                if (sort < 10) {
                    return '0' + sort;
                }
                return sort;
            },
            editSort(index, column) {
                if (column.type === 'hot-search') {
                    this.is_sort = index;
                    this.temp_sort = column.sort;
                }
            },
            edit(column) {
                navigateTo({
                    r: 'mall/goods-hot-search/edit',
                    goods_id: column.goods_id,
                    type: column.type,
                })
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/goods-hot-search/get-all',
                        page: this.page,
                    }
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            },

            submit(column) {
                let para = Object.assign({}, column, {sort: this.temp_sort});
                request({
                    params: {
                        r: 'mall/goods-hot-search/change-sort',
                    },
                    data: para,
                    method: 'POST',
                }).then(e => {
                    //column.sort = this.temp_sort;
                    if (e.data.code === 0) {
                        this.$message.success(e.data.msg);
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },

            destroy(params, index) {
                this.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'mall/goods-hot-search/destroy',
                        },
                        data: {
                            goods_id: params.goods_id,
                            type: params.type,
                        },
                        method: 'POST',
                    }).then(e => {
                        this.listLoading = false;
                        if (e.data.code === 0) {
                            this.$message.success(e.data.msg);
                            this.list.splice(index, 1);
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.listLoading = false;
                    });
                }).catch(() => {
                    this.$message({type: 'info', message: '已取消删除'});
                });
            },
        }
    });
</script>
