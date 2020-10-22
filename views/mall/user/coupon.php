<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>优惠券管理</span>
            </div>
        </div>
        <div class="table-body">
            <el-date-picker
                style="margin-bottom: 10px;"
                @change='search'
                size="small"
                v-model="date"
                type="datetimerange"
                value-format="yyyy-MM-dd HH:mm:ss"
                range-separator="至"
                start-placeholder="开始日期"
                end-placeholder="结束日期">
            </el-date-picker>
            <el-tabs v-model="status" @tab-click="search">
                <el-tab-pane label="全部" name="0"></el-tab-pane>
                <el-tab-pane label="未使用" name="1"></el-tab-pane>
                <el-tab-pane label="已使用" name="2"></el-tab-pane>
                <el-tab-pane label="已过期" name="3"></el-tab-pane>
                <el-table :data="form" border style="width: 100%" v-loading="listLoading">
                    <el-table-column prop="id" label="ID" width="100"></el-table-column>
                    <el-table-column prop="coupon.name" label="优惠券名称"></el-table-column>
                    <el-table-column prop="coupon_min_price" label="最低消费金额（元）" width="180"></el-table-column>
                    <el-table-column label="优惠方式" width="180" :formatter="subPriceFormatter"></el-table-column>
                    <el-table-column label="有效时间" width="350" :formatter="timeFormatter"></el-table-column>
                    <el-table-column prop="created_at" label="领取时间" width="180"></el-table-column>
                    <el-table-column prop="receive_type" label="获取方式" width="180"></el-table-column>
                    <el-table-column label="状态" width="80" :formatter="UseFormatter"></el-table-column>
                    <el-table-column label="操作" width="80">
                        <template slot-scope="scope">
                            <el-button type="text" size="mini" @click="destroy(scope.row)" circle>
                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                    <img src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
                 <div style="text-align: right;margin: 20px 0;">
                    <el-pagination @current-change="pagination" background layout="prev, pager, next, jumper" :page-count="pageCount"></el-pagination>
                </div>
            </el-tabs>
        </div>
    </el-card>
</div>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            form: [],
            date: '',
            page: 1,
            listLoading: false,
            status: '0',
            pageCount: 10
        };
    },
    methods: {
        //格式化
        subPriceFormatter(row) {
            if (row.type == 2) {
                return '优惠：' + row.sub_price + '元';
            } else {
                return row.discount + '折';
            }
        },
        timeFormatter(row) {
            return row.start_time + `--` + row.end_time;
        },

        UseFormatter(row) {
            return row.is_use==1 ? '已使用' : (new Date(row.end_time).valueOf() < new Date().valueOf() ? '已过期' : '未使用');
        },

        //
        search() {
            this.page = 1;
            this.getList();
        },
        pagination(currentPage) {
            this.page = currentPage;
            this.getList();
        },
        //删除
        destroy: function(column) {
            this.$confirm('确认删除该记录吗?', '提示', {
                type: 'warning'
            }).then(() => {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/user/coupon-destroy'
                    },
                    data: { id: column.id },
                    method: 'post'
                }).then(e => {
                    if (e.data.code === 0) {
                        location.reload();
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            });
        },

        getList() {
            this.listLoading = true;
            request({
                params: {
                    r: 'mall/user/coupon',
                    user_id: getQuery('user_id'),
                    status: this.status,
                    date: this.date,
                    page: this.page,
                },
            }).then(e => {
                this.listLoading = false;
                if (e.data.code == 0) {
                    this.form = e.data.data.list;
                    this.pageCount = e.data.data.pagination.page_count;
                } else {
                    self.$message.error(e.data.msg);
                }
            });
        },
    },

    mounted() {
        this.getList();
    }
})
</script>
