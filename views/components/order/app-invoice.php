<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: fjt
 */
?>

<style scope>
    .app-invoice .title-box {
        margin: 15px 0;
    }

    .app-invoice .title-box .text {
        background-color: #FEFAEF;
        color: #E6A23C;
        padding: 6px;
    }

    .app-invoice .get-print {
        width: 100%;
        height: 100%;
    }

    .app-invoice .el-table__header-wrapper th {
        background-color: #f5f7fa;
    }

    .app-invoice .el-dialog__body {
        padding: 5px 20px 10px;
    }

    .app-invoice .el-radio__label {
        display: none;
    }

    .app-invoice .input-1 .el-form-item__content {
        margin: 0 320px 0 0 !important;
    }

    .input-1 .el-icon-search {
        margin: 0 20px;
    }

    .input-1 .el-input-group__append, .el-input-group__prepend {
        padding: 0;
    }

    .el-input-group__append .el-button.el-button--default {
        padding: 0;
    }

    .image-dialog .el-dialog__wrapper > div {
        margin-top: 25vh !important;
        min-width: 550px;
    }
</style>

<template id="app-invoice">
    <div class="app-invoice" style="user-select: none;">
        <!-- 打印 -->
        <el-dialog title="打印发货单" :visible.sync="dialogVisible" width="35%" @close="closeDialog">
            <template v-if="printStatus">
                <div class="title-box">
                    <span class="text">选择打印商品</span>
                    <span>(默认全选)</span></div>
                <el-table
                        ref="multipleTable"
                        :data="orderDetail"
                        tooltip-effect="dark"
                        style="width: 100%"
                        max-height="250"
                        @selection-change="handleSelectionChange">
                    <el-table-column
                            type="selection"
                            width="55">
                    </el-table-column>
                    <el-table-column
                            label="图片"
                            width="60">
                        <template slot-scope="scope">
                            <app-image width="30" height="30"
                                       :src="scope.row.goods_info.goods_attr.cover_pic"></app-image>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="名称"
                            show-overflow-tooltip>
                        <template slot-scope="scope">
                            <el-tag v-if="scope.row.expressRelation" type="success" size="mini">已发货</el-tag>
                            <span>{{scope.row.goods_info.goods_attr.name}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="goods_info.goods_attr.number"
                            label="数量"
                            width="80"
                            show-overflow-tooltip>
                    </el-table-column>
                    <el-table-column
                            label="规格"
                            width="120"
                            show-overflow-tooltip>
                        <template slot-scope="scope">
                            <span v-for="attrItem in scope.row.goods_info.attr_list">
                                {{attrItem.attr_group_name}}:{{attrItem.attr_name}}
                            </span>
                        </template>
                    </el-table-column>
                </el-table>
            </template>
            <div class="title-box">
                <span class="text">选择发货单模板</span>
            </div>
            <el-form label-width="130px"
                     class="sendForm"
                     :model="express"
                     @submit.native.prevent="getExpress"
                     ref="sendForm">
                <el-form-item class="input-1">
                    <el-input
                            v-model="keyword"
                            @keyup.enter.native="getExpress"
                            clearable
                            @clear="getExpress"
                            size="small"
                            placeholder="请输入模板名称"
                            class="input-with-select"
                    >
                        <el-button style="padding:0" slot="append" icon="el-icon-search"></el-button>
                    </el-input>
                </el-form-item>
                <el-table
                        v-loading="printLoading"
                        :data="express_list"
                        max-height="250"
                        style="width: 100%">
                    <el-table-column width="50">
                        <template slot-scope="scope">
                            <el-radio v-model="invoice_template" :label="scope.row.id"></el-radio>
                        </template>
                    </el-table-column>
                    <el-table-column label="图片">
                        <template slot-scope="scope">
                            <image style="width:28px;height:28px;cursor:pointer;" @click="getImg(scope.row)"
                                   :src="scope.row.cover_pic"/>
                        </template>
                    </el-table-column>
                    <el-table-column label="发货单模板名称" prop="name"></el-table-column>
                </el-table>
                <el-form-item style="text-align: right">
                    <el-button size="small" @click="dialogVisible=false">取 消</el-button>
                    <el-button size="small" type="primary"
                               @click="print()">
                        打印
                    </el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
        <div class="image-dialog">
            <el-dialog
                    :title="template_name"
                    :visible.sync="imageVisible"
                    width="18%"
            >
                <div style="width: 100%; position: relative;padding-top: 35px;">
                    <image style="width: 100%;position: relative;left: 50%;transform: translateX(-50%);"
                           :src="image_url"></image>
                </div>
                <span slot="footer" class="dialog-footer">
                <el-button type="primary" @click="imageVisible = false" size="small">我知道了</el-button>
            </span>
            </el-dialog>
        </div>
    </div>
</template>

<script>
    Vue.component('app-invoice', {
        template: '#app-invoice',
        props: {
            isShow: {
                type: Boolean,
                default: false,
            },
            order: {
                type: Object,
                default: function () {
                    return {}
                }
            },
            sendType: {
                type: String,
                default: '',
            },
            isShowPrint: {
                type: Boolean,
                default: true,
            },
            printStatus: {
                type: Boolean,
                default: true,
            },
            expressId: {
                type: Number,
                default: 0,
            },
        },
        watch: {
            isShow: function (newVal) {
                if (newVal) {
                    this.openExpress();
                    if (this.printStatus) {
                        this.getExpressData();
                    }
                } else {
                    this.dialogVisible = false;
                    this.keyword = '';
                }
            },
            keyword: function (newVal) {
                this.getExpress();
            }
        },
        data() {
            return {
                dialogVisible: false,
                express: {},
                express_list: [],
                multipleSelection: [],
                orderDetail: [],
                invoice_template: '',
                keyword: '',
                imageVisible: false,
                image_url: '',
                template_name: '',
                printLoading: false,
            }
        },
        methods: {
            // 打开打印框
            openExpress() {
                let self = this;
                self.getExpress();
                self.dialogVisible = true;
            },
            getExpressData() {
                let self = this;
                self.orderDetail = self.order.detail;
                // 默认全选
                for (let i = 0; i < this.orderDetail.length; i++) {
                    setTimeout(() => {
                        self.$refs.multipleTable.toggleRowSelection(self.orderDetail[i], true);
                    }, 1);
                }
            },
            closeDialog() {
                this.$emit('close');
            },
            // 发货
            print() {
                for (let i = 0; i < this.express_list.length; i++) {
                    if (this.express_list[i].id === this.invoice_template) {
                        if (this.printStatus) {
                            if (this.multipleSelection.length === 0) {
                                return;
                            }
                            this.$emit('select_template', this.express_list[i], this.multipleSelection, this.order);
                        } else {
                            this.$emit('select_template_all', this.express_list[i]);
                        }
                    }
                }
            },
            getExpress() {
                this.printLoading = true;
                request({
                    params: {
                        r: 'mall/order-send-template/index',
                        keyword: this.keyword
                    },
                }).then(e => {
                    this.printLoading = false;
                    if (e.data.code === 0) {
                        this.express_list = e.data.data.list;
                        for (let i = 0; i < this.express_list.length; i++) {
                            if (this.express_list[i].is_default == 1) {
                                this.invoice_template = this.express_list[i].id;
                            }
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.printLoading = false;
                    console.log(e);
                });
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            },
            selectInit(row, index) {
                if (row.expressRelation) {
                    return false;
                } else {
                    return true;
                }
            },
            getImg(e) {
                console.log(e);
                this.imageVisible = true;
                this.image_url = e.cover_pic;
                this.template_name = e.name;
            }
        }
    })
</script>