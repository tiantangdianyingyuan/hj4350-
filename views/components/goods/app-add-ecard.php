<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>
<style>

</style>

<template id="app-add-ecard">
    <div class="app-add-ecard">
        <el-dialog width="1100px"  title="选择卡密" :visible.sync="dialogVisible">
            <div style="margin-bottom: 25px;">
                <el-input @change="search" @clear="search" v-model="keyword" placeholder="根据名称搜索" autocomplete="off" clearable>
                    <template slot="append">
                        <el-button @click="search">搜索</el-button>
                    </template>
                </el-input>
            </div>
            <template>
                <el-table
                        :data="list"
                        height="500"
                        border
                        v-loading="tableLoading"
                        style="width: 100%">
                    <el-table-column
                        width="50"
                    >
                        <template slot-scope="scope">
                            <el-radio :label="scope.row.id"
                                v-model="ecard_id"
                            >&nbsp;</el-radio>
                        </template>
                    </el-table-column>
                    <el-table-column
                        prop="name"
                        label="名称">
                    </el-table-column>
                </el-table>
            </template>
            <div slot="footer" class="dialog-footer" flex="main:justify">
                <el-pagination
                        @current-change="pagination"
                        background
                        :current-page="current_page"
                        layout="prev, pager, next"
                        :page-count="page_count">
                </el-pagination>
                <el-button type="primary" size="small" @click="save">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-add-ecard', {
        template: '#app-add-ecard',
        props: {
            mch_id: {
                type: Number,
                default: 0
            },
            newCats: {
                type: Array,
                default: function () {
                    return []
                }
            },

            ecard_url: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
                dialogVisible: false,

                keyword: '',
                list: [],
                ecard_id: '',
                current_page: 1,
                page_count: 1,
                tableLoading: false,
            }
        },
        methods: {
            openDialog() {
                this.getCard();
                this.cats = [];
                this.children = [];
                this.third = [];
                this.dialogVisible = true;
            },

            search() {
                this.current_page = 1;
                this.getCard();
            },

            // 获取商品分类
            async getCard() {
                let self = this;
                self.tableLoading = true;
                const e = await request({
                    params: {
                        r: this.ecard_url,
                        keyword: this.keyword,
                        page: this.current_page
                    },
                    method: 'get',
                });
                self.tableLoading = false;
                if (e.data.code === 0) {
                    self.list = e.data.data.list;
                    self.current_page = e.data.data.pagination.current_page;
                    self.page_count = e.data.data.pagination.page_count;
                } else {
                    self.$message.error(e.data.msg);
                }
            },

            pagination(e) {
                this.current_page = e;
                this.getCard();
            },

            save() {
                let name = '';
                let stock = '';
                for (let i = 0; i < this.list.length; i++) {
                    if (this.list[i].id === this.ecard_id) {
                        name = this.list[i].name;
                        stock = this.list[i].stock;
                    }
                }
                this.$emit('select', {
                    id: this.ecard_id,
                    name: name,
                    stock
                });
                this.dialogVisible = false;
            },
        }
    })
</script>
