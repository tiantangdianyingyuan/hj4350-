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
            <div>
                <span>奖品列表</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" size="small" @click="$navigate({r: 'plugin/lottery/mall/lottery/edit'})">添加商品</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input  @keyup.enter.native="search"size="small" placeholder="请输入搜索内容" v-model="keyword" clearable @clear='search'>
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table :data="form" border style="width: 100%" v-loading="listLoading">
                <el-table-column prop="lotteryGoods.id" label="抽奖ID" width="100"></el-table-column>
                <el-table-column prop="goodsWarehouse.name" label="商品名称"></el-table-column>
                <el-table-column prop="attr_str" label="规格" width="220"></el-table-column>
                <el-table-column label="活动时间" width="170">
                    <template slot-scope="scope">
                        {{scope.row.lotteryGoods.start_at}}-{{scope.row.lotteryGoods.end_at}}
                    </template>
                </el-table-column>
                <el-table-column prop="lotteryGoods.stock" label="中奖数量" width="120"></el-table-column>
                <el-table-column prop="lotteryGoods.status" label="状态" width="80">
                    <template slot-scope="scope">
                        <el-switch
                                active-value="1"
                                inactive-value="0"
                                :disabled="scope.row.lotteryGoods.type == 1"
                                @change="switchStatus(scope.row)"
                                v-model="scope.row.lotteryGoods.status"
                        >
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column prop="lotteryGoods.type" label="说明" width="120">
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.lotteryGoods.type == 2" type="warning">人数不足</el-tag>
                        <el-tag v-if="scope.row.lotteryGoods.type == 1" type="success">已开奖</el-tag>
                        <el-tag v-if="scope.row.lotteryGoods.type == 0
                            && scope.row.lotteryGoods.start_at > (new Date()).pattern('yyyy-MM-dd HH:mm:ss')"
                                type="info">未开始
                        </el-tag>
                        <el-tag v-if="scope.row.lotteryGoods.type == 0
                            && scope.row.lotteryGoods.start_at < (new Date()).pattern('yyyy-MM-dd HH:mm:ss')
                            && scope.row.lotteryGoods.end_at > (new Date()).pattern('yyyy-MM-dd HH:mm:ss')">进行中
                        </el-tag>
                        <el-tag v-if="scope.row.lotteryGoods.type == 0
                            && scope.row.lotteryGoods.end_at < (new Date()).pattern('yyyy-MM-dd HH:mm:ss')"
                                type="warning">已到期
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="lotteryGoods.sort" label="排序" width="150">
                    <template slot-scope="scope">
                        <div v-if="id != scope.row.id">
                            <el-tooltip class="item" effect="dark" content="排序" placement="top">
                                <span>{{scope.row.lotteryGoods.sort}}</span>
                            </el-tooltip>
                            <el-button class="edit-sort" type="text" @click="editSort(scope.row)">
                                <img src="statics/img/mall/order/edit.png" alt="">
                            </el-button>
                        </div>
                        <div style="display: flex;align-items: center" v-else>
                            <el-input style="min-width: 70px" type="number" size="mini" class="change" v-model="sort"
                                      autocomplete="off"></el-input>
                            <el-button class="change-quit" type="text" style="color: #F56C6C;padding: 0 5px"
                                       icon="el-icon-error"
                                       circle @click="quit"></el-button>
                            <el-button class="change-success" type="text"
                                       style="margin-left: 0;color: #67C23A;padding: 0 5px"
                                       icon="el-icon-success" circle @click="sortSubmit(scope.row)">
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" width="280" label="操作" width="160">
                    <template slot-scope="scope">
                        <el-button size="mini" type="text" v-if="scope.row.lotteryGoods.type == 0
                            && scope.row.lotteryGoods.end_at < (new Date()).pattern('yyyy-MM-dd HH:mm:ss')"
                                   @click="navTest(scope.row)" circle>
                            <el-tooltip class="item" effect="dark" content="可选择在结束时间一分钟后 手动触发抽奖" placement="top">
                                <img src="statics/img/mall/change.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button size="mini" type="text"
                                   @click="$navigate({r:'plugin/lottery/mall/lottery/info',lottery_id:scope.row.lotteryGoods.id})"
                                   circle>
                            <el-tooltip class="item" effect="dark" content="参与详情" placement="top">
                                <img src="statics/img/mall/detail.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" size="mini" @click="destroy(scope.row)" circle>
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination @current-change="pagination" hide-on-single-page background layout="prev, pager, next, jumper" :page-count="pageCount"></el-pagination>
            </div>
        </div>
    </el-card>
</div>
<script>
Date.prototype.pattern = function(fmt) {
    var o = {
        "M+": this.getMonth() + 1, //月份           
        "d+": this.getDate(), //日           
        "h+": this.getHours() % 12 == 0 ? 12 : this.getHours() % 12, //小时           
        "H+": this.getHours(), //小时           
        "m+": this.getMinutes(), //分           
        "s+": this.getSeconds(), //秒           
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度           
        "S": this.getMilliseconds() //毫秒           
    };
    var week = {
        "0": "/u65e5",
        "1": "/u4e00",
        "2": "/u4e8c",
        "3": "/u4e09",
        "4": "/u56db",
        "5": "/u4e94",
        "6": "/u516d"
    };
    if (/(y+)/.test(fmt)) {
        fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    }
    if (/(E+)/.test(fmt)) {
        fmt = fmt.replace(RegExp.$1, ((RegExp.$1.length > 1) ? (RegExp.$1.length > 2 ? "/u661f/u671f" : "/u5468") : "") + week[this.getDay() + ""]);
    }
    for (var k in o) {
        if (new RegExp("(" + k + ")").test(fmt)) {
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
        }
    }
    return fmt;
}
const app = new Vue({
    el: '#app',
    data() {
        return {
            form: [],
            pageCount: 0,
            listLoading: false,

            //默认
            editFormVisible: false,
            editForm: {},
            id: null,
            sort: 0,
            btnLoading: false,
            editFormRules: {
                sort: [
                    { required: true, message: '排序不能为空', trigger: 'blur' },
                ]
            },
            keyword: '',

        };
    },
    methods: {
        search() {
            this.page = 1;
            this.getList();
        },
        navTest(row) {
            this.$confirm('是否触发手动开奖吗?', '提示', {
                type: 'info'
            }).then(() => {
                request({
                    params: {
                        r: 'plugin/lottery/mall/lottery/test',
                    },
                    method: 'post',
                    data: {
                        lottery_id: row.lotteryGoods.id
                    }
                }).then(e => {
                    if (e.data.code === 0) {
                        this.$message.success(e.data.msg);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            })
        },

        switchStatus(row) {
            request({
                params: {
                    r: 'plugin/lottery/mall/lottery/switch-status',
                },
                method: 'post',
                data: {
                    status: row.lotteryGoods.status,
                    id: row.lotteryGoods.id
                }
            }).then(e => {
                if (e.data.code === 0) {
                    this.$message.success(e.data.msg);
                } else {
                    this.$message.error(e.data.msg);
                }
            });
        },

        quit() {
            this.id = null;
        },

        editSort(row) {
            this.id = row.id;
            this.sort = row.lotteryGoods.sort;
        },

        sortSubmit(row) {
            let para = row;
            para.lotteryGoods.sort = this.sort;
            this.btnLoading = true;
            request({
                params: {
                    r: 'plugin/lottery/mall/lottery/edit-sort',
                },
                method: 'post',
                data: {
                    id: row.lotteryGoods.id,
                    sort: this.sort
                },
            }).then(e => {
                if (e.data.code === 0) {
                    this.$message({
                      message: e.data.msg,
                      type: 'success'
                    });
                    this.id = null;
                } else {
                    this.$message.error(e.data.msg);
                }
                this.btnLoading = false;
                this.dialogSort = false;
            }).catch(e => {
                this.btnLoading = false;
            });
        },

        //删除
        destroy: function(column) {
            this.$confirm('确认删除该记录吗?', '提示', {
                type: 'warning'
            }).then(() => {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/lottery/mall/lottery/destroy'
                    },
                    data: { id: column.lotteryGoods.id },
                    method: 'post'
                }).then(e => {
                    this.listLoading = false;
                    location.reload();
                }).catch(e => {
                    this.listLoading = false;
                });
            });
        },

        //
        pagination(currentPage) {
            this.page = currentPage;
            this.getList();
        },

        getList() {
            this.listLoading = true;
            request({
                params: {
                    r: 'plugin/lottery/mall/lottery/index',
                    page: this.page,
                    keyword: this.keyword,
                },
            }).then(e => {
                if (e.data.code === 0) {
                    this.form = e.data.data.list;
                    this.pageCount = e.data.data.pagination.page_count;
                } else {
                    this.$message.error(e.data.msg);
                }
                this.listLoading = false;
            }).catch(e => {
                this.listLoading = false;
            });
        },
    },
    mounted: function() {
        this.getList();
    }
});
</script>