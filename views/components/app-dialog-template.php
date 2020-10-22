<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/18
 * Time: 13:52
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .app-dialog-dialog {
        min-width: 700px;
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
<template id="app-dialog-template">
    <div class="app-dialog-template">
        <el-dialog append-to-body title="选择模板" :visible.sync="show" :close-on-click-modal="false"
                   custom-class="app-dialog-dialog" :before-close="close">
            <el-form size="small" :inline="true" v-if="status == 1">
                <el-form-item>
                    <el-select @change="onSubmit" style="width: 150px" v-model="is_buy">
                        <el-option value="" label="全部模板"></el-option>
                        <el-option :value="1" label="已购买"></el-option>
                        <el-option :value="0" label="未购买"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item style="display: none">
                    <div class="input-item">
                        <el-input @keyup.enter.native="onSubmit" size="small" placeholder="请输入模板名称"
                                  v-model="keyword" clearable @clear="onSubmit">
                            <el-button slot="append" icon="el-icon-search" @click="onSubmit"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
                <el-form-item>
                    <el-checkbox v-model="checkAll" :indeterminate="isIndeterminate" @change="handleCheckAllChange">批量选择</el-checkbox>
                </el-form-item>
            </el-form>
            <div v-loading="listLoading">
                <el-checkbox-group v-model="selected_list">
                    <el-checkbox v-for="(item, index) in list" style="width: 145px;margin-right: 5px;margin-top: 10px;"
                                 :label="item.id" :key="index">{{item.name}}</el-checkbox>
                </el-checkbox-group>
                <div style="text-align: center;margin-top: 20px;color: #999999;">
                    <template v-if="no_more">
                        <span>没有更多数据</span>
                    </template>
                    <el-button type="text" :loading="loading" @click="more" v-else>加载更多</el-button>
                </div>
            </div>
            <div style="margin-top: 24px;" slot="footer">
                <el-button size="small" @click="cancel">取消</el-button>
                <el-button type="primary" size="small" @click="confirm">确定</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('app-dialog-template', {
        template: '#app-dialog-template',
        props: {
            show: Boolean,
            selected: Array,
            status: Number,
        },
        data() {
            return {
                listLoading: false,
                list: [],
                keyword: '',
                selected_list: [],
                page: 1,
                type: '',
                loading: false,
                no_more: false,
                is_buy: '',
                checkAll: false,
            };
        },
        watch: {
            show() {
                if (this.show) {
                    this.reset();
                    this.loadData();
                    this.selected_list = this.selected;
                }
            },
            selected_list: {
                handler() {
                    this.checkAll = this.list.length > 0 && this.list.length === this.selected_list.length;
                },
                immediate: true,
                deep: true,
            }
        },
        computed: {
            isIndeterminate() {
                return this.selected_list.length > 0 && this.selected_list.length < this.list.length
            }
        },
        methods: {
            loadData() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'admin/template/list',
                        page: this.page,
                        is_buy: this.is_buy,
                        status: this.status
                    }
                }).then(response => {
                    this.listLoading = false;
                    if (response.data.code == 0) {
                        this.list = [...this.list, ...response.data.data.list];
                        if (response.data.data.list.length == 20) {
                            this.page++;
                        } else {
                            this.no_more = true;
                        }
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(response => {
                    this.listLoading = false;
                });
            },
            reset() {
                this.page = 1;
                this.list = [];
                this.type = '';
                this.keyword = '';
                this.is_buy = '';
            },
            close(done) {
                this.reset();
                this.$emit('selected');
            },
            cancel() {
                this.reset();
                this.$emit('selected');
            },
            confirm() {
                let list = [];
                for (let i in this.selected_list) {
                    this.list.forEach(item => {
                        if (item.id == this.selected_list[i]) {
                            list.push(item);
                        }
                    });
                }
                this.$emit('selected', list);
                this.reset();
            },
            onSubmit() {
                this.page = 1;
                this.list = [];
                this.loadData();
            },
            more() {
                if (!this.no_more) {
                    this.loadData();
                }
            },
            handleCheckAllChange(val) {
                if (val) {
                    let list = [];
                    for (let i in this.list) {
                        list.push(this.list[i].id);
                    }
                    this.selected_list = list;
                } else {
                    this.selected_list = [];
                }
            }
        }
    });
</script>