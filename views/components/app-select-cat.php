<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/10/29
 * Time: 9:59
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .app-goods-cat-list {
        border: 1px solid #E8EAEE;
        border-radius: 5px;
        margin-top: -5px;
        padding: 10px;
    }
</style>
<template id="app-select-cat">
    <el-dialog title="选择分类" width="1100px" :visible.sync="dialogVisible">
        <el-row v-loading="dialogLoading" :gutter="20" style="margin-top: -30px;">
            <template v-if="options.length > 0">
                <el-col :span="8">
                    <h3>一级分类</h3>
                    <div class="app-goods-cat-list">
                        <el-checkbox-group v-model="cat_list"
                                           size="mini" flex="dir:left" style="flex-wrap: wrap">
                            <el-checkbox v-for="(option,index) in options" style="width: 100%;"
                                         @change="selectCats(option,1)" size="mini"
                                         :key="option.value" :label="option.value">
                                {{option.label}}
                            </el-checkbox>
                        </el-checkbox-group>
                    </div>
                </el-col>
                <el-col :span="8" v-if="children.length > 0">
                    <h3>二级分类</h3>
                    <div class="app-goods-cat-list">
                        <el-checkbox-group v-model="cat_list"
                                           size="mini" flex="dir:left" style="flex-wrap: wrap">
                            <el-checkbox v-for="(option,index) in children" style="width: 100%;"
                                         @change="selectCats(option,2)"
                                         :key="option.value" :label="option.value" size="mini">
                                {{option.label}}
                            </el-checkbox>
                        </el-checkbox-group>
                    </div>
                </el-col>
                <el-col :span="8" v-if="third.length > 0">
                    <h3>三级分类</h3>
                    <div class="app-goods-cat-list">
                        <el-checkbox-group v-model="cat_list"
                                           size="mini" flex="dir:left" style="flex-wrap: wrap">
                            <el-checkbox v-for="(option,index) in third" style="width: 100%;"
                                         @change="selectCats(option,3)"
                                         :key="option.value" :label="option.value" size="mini">
                                {{option.label}}
                            </el-checkbox>
                        </el-checkbox-group>
                    </div>
                </el-col>
            </template>
            <template v-else>
                <div flex="main:center" style="align-items: center;margin-top: 20px;">
                    <span>无系统分类</span>
                    <el-button style="display: inline-block;margin-left: 10px" flex="main:center" type="primary"
                               size="small"
                               @click="$navigate({r: 'mall/cat/edit'})">
                        请先添加商品分类
                    </el-button>
                </div>
            </template>
        </el-row>
        <span slot="footer" class="dialog-footer">
                <el-button size='small' @click="catDialogCancel">取 消</el-button>
                <el-button size='small' type="primary" @click="beSelectCats">确 定</el-button>
            </span>
    </el-dialog>
</template>
<script>
    Vue.component('app-select-cat', {
        template: '#app-select-cat',
        props: {
            show: Boolean,
            value: Array,
        },
        data() {
            return {
                dialogLoading: false,
                dialogVisible: false,
                options: [],
                children: [],
                third: [],
                data: [],
                cat_list: [],
            };
        },
        created() {
            this.cat_list = [];
            this.value.forEach(item => {
                this.cat_list.push(item.value);
            });
            this.data = JSON.parse(JSON.stringify(this.value));
        },
        watch: {
            show: {
                handler(v) {
                    this.dialogVisible = this.show;
                    if (v) {
                        this.getCats();
                    }
                },
                immediate: true,
            },
            value: {
                deep: true,
                handler(newVal, oldVal) {
                    this.cat_list = [];
                    this.value.forEach(item => {
                        this.cat_list.push(item.value);
                    });
                    this.data = JSON.parse(JSON.stringify(this.value));
                },
            },
            dialogVisible: {
                handler(v) {
                    if (!v) {
                        this.catDialogCancel();
                    }
                }
            }
        },
        methods: {
            getCats() {
                let self = this;
                self.dialogLoading = true;
                request({
                    params: {
                        r: 'mall/cat/options',
                        mch_id: 0
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    self.dialogLoading = false;
                    if (e.data.code == 0) {
                        self.options = e.data.data.list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            selectCats(option, type) {
                let flag = true;
                let list = JSON.parse(JSON.stringify(this.data));
                list.forEach(item => {
                    if (item.value == option.value) {
                        flag = false;
                    }
                });
                if (flag) {
                    list.push({
                        label: option.label,
                        value: option.value
                    });
                }
                let newList = [];
                this.cat_list.forEach(item => {
                    list.forEach(data => {
                        if (data.value == item) {
                            newList.push(data);
                        }
                    })
                });
                this.data = newList;
                if (type == 1) {
                    if (typeof option.children !== 'undefined') {
                        this.children = option.children;
                    } else {
                        this.children = [];
                    }
                }
                if (type == 2) {
                    if (typeof option.children !== 'undefined') {
                        this.third = option.children;
                    } else {
                        this.third = [];
                    }
                }
            },
            beSelectCats() {
                this.$emit('input', this.data);
                this.catDialogCancel();
            },
            catDialogCancel() {
                this.dialogVisible = false;
                this.$emit('cancel');
            }
        }
    });
</script>
