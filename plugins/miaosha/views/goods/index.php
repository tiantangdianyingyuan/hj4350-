<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
//Yii::$app->loadViewComponent('goods/app-batch');
Yii::$app->loadViewComponent('app-activity-list');

?>

<div id="app" v-cloak>
    <app-activity-list
        activity_name="秒杀"
        :tabs="tabs"
        goods_url="plugin/miaosha/mall/activity/index"
        edit_activity_url='plugin/miaosha/mall/goods/edit'
    ></app-activity-list>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                search: {
                    keyword: '',
                    status: '',
                    date_start: '',
                    date_end: '',
                },
                tableData: [],
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                choose_list: [],
                tabs: [
                    {
                        name: '全部',
                        value: '-1'
                    },
                    {
                        name: '未开始',
                        value: '1'
                    },
                    {
                        name: '进行中',
                        value: '0'
                    },
                    {
                        name: '已结束',
                        value: '2'
                    },
                    {
                        name: '下架中',
                        value: '3'
                    },
                ]
            };
        },
        created() {
            // this.getList();
        },
        methods: {
            handleSelectionChange(val) {
                let self = this;
                self.choose_list = [];
                val.forEach(function (item) {
                    self.choose_list.push(item.goods_warehouse_id);
                })
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
                        r: '',
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
            edit(id) {
                if (id) {
                    navigateTo({
                        r: 'plugin/miaosha/mall/goods/edit',
                        id: id,
                    });
                } else {
                    navigateTo({
                        r: 'plugin/miaosha/mall/goods/edit',
                    });
                }
            },
            miaosha(row) {
                navigateTo({
                    r: 'plugin/miaosha/mall/goods/miaosha-list',
                    id: row.goods_warehouse_id,
                });
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
                            r: 'plugin/miaosha/mall/goods/destroy',
                        },
                        method: 'post',
                        data: {
                            goods_warehouse_id: row.goods_warehouse_id,
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

            batchDestroy() {
                let self = this;
                self.$confirm('批量删除数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/miaosha/mall/goods/batch-destroy',
                        },
                        method: 'post',
                        data: {
                            choose_list: this.choose_list,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            self.getList();
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
            // 搜索
            commonSearch() {
                this.getList();
            },
        }
    });
</script>
