<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

Yii::$app->loadViewComponent('app-goods-list');
?>
<style>
</style>
<div id="app" v-cloak>
    <app-goods-list
            ref="goodsList"
            goods_url="plugin/step/mall/goods/index"
            :is-show-svip="false"
            edit_goods_url='plugin/step/mall/goods/edit'>

        <template slot="column-col">
            <el-table-column width="100" label="所需活力币">
                <template slot-scope="scope">
                    {{scope.row.stepGoods.currency}}
                </template>
            </el-table-column>
        </template>
    </app-goods-list>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                search: {
                    keyword: '',
                    status: '-1',
                },
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                id: null,
                sort: 0,
                choose_list: []
            };
        },
        created() {
            this.getList();
        },
        methods: {
            handleSelectionChange(row) {
                this.choose_list = row;
            },
            
            quit() {
                this.id = null;
            },

            editSort(row) {
                this.id = row.id;
                this.sort = row.sort;
            },

            change(row) {
                let self = this;
                row.sort = self.sort;
                request({
                    params: {
                        r: 'plugin/step/mall/goods/edit-sort'
                    },
                    method: 'post',
                    data: {
                        id: row.id,
                        sort: self.sort,
                    }
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                        this.id = null;
                        this.getList();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                    self.btnLoading = false;
                });
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/step/mall/goods/index',
                        page: self.page,
                        search: self.search,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            edit(row) {
                if (row) {
                    navigateTo({
                        r: 'plugin/step/mall/goods/edit',
                        id: row.id,
                    });
                } else {
                    navigateTo({
                        r: 'plugin/step/mall/goods/edit',
                    });
                }
            },
            destroy(row, index) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: '/mall/goods/destroy',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.list.splice(index, 1);
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },
            handleClick(row) {
                console.log(row);
            },
            // 商品上下架
            switchStatus(row) {
                let self = this;
                request({
                    params: {
                        r: '/mall/goods/switch-status',
                    },
                    method: 'post',
                    data: {
                        status: row.status,
                        id: row.id
                    }
                }).then(e => {
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            // 搜索
            commonSearch() {
                this.page = 1;
                this.getList();
            }
        }
    });
</script>
