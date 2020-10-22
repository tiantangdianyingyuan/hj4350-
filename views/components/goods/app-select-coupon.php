<style>
    .app-select-coupon .input-item {
        display: inline-block;
        width: 250px;
    }

    .app-select-coupon .input-item .el-input__inner {
        border-right: 0;
    }

    .app-select-coupon .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .app-select-coupon .input-item .el-input__inner:focus {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .app-select-coupon .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .app-select-coupon .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .app-select-coupon .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .app-select-coupon .el-dialog__body {
        padding: 10px 20px;
    }

    .app-select-coupon .coupon-list {
        margin-top: 20px;
    }

    .coupon-list .coupon-list-item {
        margin: 5px 0;
        width: 100%;
    }

    .app-select-coupon .pagination-box {
        margin-top: 20px;
    }
      .el-dialog__body  .coupon-list .el-scrollbar .el-scrollbar__view {
        padding: 0 15px;
    }
    .el-dialog__body  .coupon-list .el-scrollbar .is-horizontal {
          display: none;
      }
</style>
<!-- todo 样式调整？ -->
<template id="app-select-coupon">
    <div class="app-select-coupon">
        <el-dialog @close="closeCouponDialog" title="选择优惠券" :visible.sync="couponDialog" top="10vh" width="30%" append-to-body>
            <div class="input-item">
                <el-input @keyup.enter.native="searchCoupon" size="small" placeholder="请输入优惠券名称"
                          v-model="search.keyword" clearable
                          @clear="searchCoupon">
                    <el-button slot="append" icon="el-icon-search" @click="searchCoupon"></el-button>
                </el-input>
            </div>
            <div class="coupon-list" v-loading="listLoading" flex="dir:top">
                <el-scrollbar style="height: 500px">
                    <div class="coupon-list-item"
                         flex="dir:left box:last cross:center"
                         v-for="(item, index) in couponList">
                        <app-ellipsis :line="1" style="padding: 8px 0">
                            <el-checkbox
                                    style="width: 360px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;vertical-align: top"
                                    v-model="item.checked" :key="item.id">
                                {{item.name}}
                            </el-checkbox>
                        </app-ellipsis>
                        <div v-if="hasNum">
                            <el-input-number type="number" size="small" v-model="item.num" :min="1" :max="100"
                                             label="输入数量"></el-input-number>
                        </div>
                    </div>
                </el-scrollbar>
            </div>
            <!--工具条 批量操作和分页-->
            <el-col :span="24" class="toolbar">
                <el-pagination
                        background
                        layout="prev, pager, next"
                        @current-change="pageChange"
                        :page-size="pagination.pageSize"
                        :total="pagination.total_count"
                        style="float:right;margin: 15px 0"
                        v-if="pagination">
                </el-pagination>
            </el-col>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="couponDialog = false">取 消</el-button>
                <el-button size="small" type="primary" @click="couponConfirm">确 定</el-button>
            </div>
        </el-dialog>
        <div @click="openCouponDialog" style="display: inline-block">
            <slot></slot>
        </div>
    </div>
</template>

<script>
    Vue.component('app-select-coupon', {
        template: '#app-select-coupon',
        props: {
            value: {
                type: Array,
                default: () => {
                    return []
                }
            },
            url: {
                type: String,
                default: 'mall/coupon/index'
            },
            hasNum: {
                type: Boolean,
                default: true,
            }
        },
        data() {
            return {
                couponDialog: false,
                page: 1,
                pagination: null,
                listLoading: false,

                couponSelect: [],
                couponList: [],
                search: {
                    keyword: '',
                }
            }
        },
        methods: {
            closeCouponDialog() {
                this.couponDialog = false;
            },
            openCouponDialog() {
                this.getList();
                this.couponDialog = true;
            },
            pageChange(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            searchCoupon() {
                this.page = 1;
                this.getList();
            },

            listFormatter(coupon) {
                const value = this.value;
                console.log(value)

                for (let i in coupon) {
                    console.log(coupon[i]);
                    for (let j in value) {
                        console.log(value[j]);
                        if (value[j].coupon_id == coupon[i].id) {
                            coupon[i].checked = true;
                            coupon[i].num = value[j].send_num;
                            break;
                        }
                    }
                    coupon[i].checked = coupon[i].checked || false;
                    coupon[i].num = coupon[i].num || 1;
                }
            },

            getList() {
                const self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: this.url,
                        keyword: self.search.keyword,
                        page: self.page,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    if (e.data.code === 0) {
                        let coupon = e.data.data.list;
                        self.listFormatter(coupon);
                        self.pagination = e.data.data.pagination;
                        self.couponList = coupon;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                });
            },

            couponConfirm() {
                const self = this;
                let value = self.value;
                self.couponList.map(v => {
                    let sentry = value.every((item, key) => {
                        if (item.coupon_id == v.id) {
                            if (v.num > 0 && v.checked) {
                                value[key].name = v.name;
                                value[key].send_num = v.num;
                            } else {
                                value.splice(key, 1)
                            }
                            return false;
                        }
                        return true;
                    });
                    if (sentry && v.num > 0 && v.checked) {
                        value.push({
                            id: '',
                            coupon_id: v.id,
                            send_num: v.num,
                            name: v.name
                        })
                    }
                });
                console.log(value);
                self.$emit('input', value);
                self.$emit('select', value);
                self.closeCouponDialog();
            },
        },
        created() {

        }
    })
</script>