<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */
?>
<style>
    .custom-dialog {
        min-width: 680px;
        width: 600px;
    }

    .tpl-box {
        width: 300px;
        height: 440px;
    }

    .tpl-box:last-child:after {
        margin-left: 20px;
    }

    .tpl-box .tpl-head {
        padding: 0 20px;
        font-size: 15px;
        color: #909399;
        margin-bottom: 10px;
        line-height: 44px;
        background: #f5f7fa;
    }

    .tpl-scrollbar {
        height: calc(440px - 44px - 10px);
    }

    .tpl-scrollbar .tpl-checkbox .tpl-item {
        padding: 10px 0;
        max-width: 260px;
    }

    .tpl-scrollbar .tpl-checkbox .tpl-item .el-checkbox__label {
        white-space: normal;
    }

    .tpl-scrollbar .tpl-checkbox {
        padding: 0 20px;
    }

    .tpl-scrollbar .tpl-checkbox.active {
        background: #f0f7ff;
    }

    .tpl-scrollbar .tpl-checkbox .el-checkbox {
        display: flex;
        -webkit-align-items: center;
        align-items: center;
    }

    .el-message-box {
        width: 350px;
    }

    .el-message-box .el-message-box__content .el-message-box__status {
        top: calc(50% - 12px);
    }
</style>
<template id="app-attr-template">
    <div class="app-attr-template">
        <el-dialog custom-class="custom-dialog" :visible.sync="templateDialog">
            <div slot="title" style="margin-bottom: -30px">
                <span style="font-size: 18px;color:#7a7a7a">选择规格模板</span>
                <span style="font-size:14px;color:#c9c9c9;margin-left: 10px">规格模板最多支持添加5组</span>
            </div>
            <div flex="dir:left">
                <div class="tpl-box">
                    <div class="tpl-head">规格名选择</div>
                    <el-scrollbar class="tpl-scrollbar">
                        <el-checkbox-group v-model="parentValue">
                            <div class="tpl-checkbox"
                                 v-for="(group,index) in templateList" :key="index"
                                 :class="{'active': group.only === tplActiveBg[0]}"
                                 @click="tplActiveBg[0] = group.only">
                                <el-checkbox class="tpl-item"
                                             :label="group.only"
                                             :disabled="group.disabled"
                                             :indeterminate="false"
                                             @change="groupChange(group)"
                                             name="type">
                                    {{group.label}}
                                </el-checkbox>
                            </div>
                        </el-checkbox-group>
                    </el-scrollbar>
                </div>

                <div class="tpl-box" style="margin-left: 20px">
                    <div class="tpl-head">规格值选择</div>
                    <el-scrollbar class="tpl-scrollbar">
                        <span v-for="(group,index) in templateList" :key="index">
                            <div v-if="group.only == tplActiveBg[0]"
                                 class="tpl-checkbox"
                                 v-for="(item,index) in group.children" :key="index"
                                 :class="{'active': item.only === tplActiveBg[1]}"
                                 @click="tplActiveBg[1] = item.only">
                                <el-checkbox-group v-model="childrenValue">
                                    <el-checkbox class="tpl-item"
                                                 :label="item.only"
                                                 :disabled="group.disabled"
                                                 :indeterminate="false"
                                                 @change="itemChange(group)"
                                                 name="type">
                                        {{item.label}}
                                    </el-checkbox>
                                </el-checkbox-group>
                            </div>
                        </span>
                    </el-scrollbar>
                </div>
            </div>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" type="primary" @click="templateSubmit">确定选择</el-button>
            </div>
        </el-dialog>

        <div @click="showTemplate">
            <slot></slot>
        </div>
    </div>
</template>
<script>
    Vue.component('app-attr-template', {
        template: '#app-attr-template',
        props: {
            value: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            attrGroupMaxCount: {
                type: Number,
                default: 5,
            },
        },
        data() {
            return {
                templateDialog: false,
                templateList: [],
                only: 0,
                tplActiveBg: [-1, -1],
                parentValue: [],
                childrenValue: [],
            }
        },
        mounted() {
            this.getList();
        },
        methods: {
            showTemplate() {
                if (this.templateList.length === 0) {
                    this.emptyModel();
                } else {
                    this.parentValue = [];
                    this.childrenValue = [];
                    this.checkDisabled();
                    this.templateDialog = true;
                }
            },
            //子操作父节点
            itemChange(group) {
                let p = new Set(this.parentValue);
                let c = new Set(this.childrenValue);
                const result = group.children.some(item => {
                    return c.has(item.only)
                });
                result ? p.add(group.only) : p.delete(group.only);
                this.parentValue = Array.from(p);
                this.checkDisabled(p);
            },
            //父操作子节点
            groupChange(group) {
                let p = new Set(this.parentValue);
                let c = new Set(this.childrenValue);
                const result = p.has(group.only);
                group.children.forEach(item => {
                    result ? c.add(item.only) : c.delete(item.only);
                });
                this.childrenValue = Array.from(c);
                this.checkDisabled(p);
            },
            //数据禁用处理
            checkDisabled(p = new Set(), is_all = false) {
                const self = this;
                const templateList = self.templateList;

                /* 启用 */
                function enable() {
                    self.templateList = templateList.map(group => {
                        if (group.disabled || is_all) {
                            group.disabled = false;
                            group.children = group.children.map(item => {
                                item.disabled = false;
                                return item;
                            })
                        }
                        return group;
                    })
                }

                /* 禁用 */
                function disable() {
                    self.templateList = templateList.map(group => {
                        if (!p.has(group.only) || is_all) {
                            group.disabled = true;
                            group.children = group.children.map(item => {
                                item.disabled = true;
                                return item;
                            })
                        }
                        return group;
                    })
                }

                if (p.size + this.value.length === this.attrGroupMaxCount) {
                    disable();
                } else {
                    enable();
                }
            },

            templateSubmit() {
                const self = this;
                const onlyArr = self.parentValue.concat(this.childrenValue);
                const templateList = self.templateList;

                let obj = {};
                const value = this.value;

                let attr_id = 0;
                onlyArr.forEach(only => {
                    templateList.forEach(group => {
                        if (group.only === only && !obj.hasOwnProperty(only)) {
                            obj[only] = {
                                attr_group_id: value.length + Object.keys(obj).length + 1,
                                attr_group_name: group.label,
                                attr_list: [],
                            }
                        }
                        group.children.forEach(item => {
                            if (item.only === only) {
                                let attr_list = {
                                    attr_id: ++attr_id,
                                    attr_name: item.label,
                                    pic_url: '',
                                };
                                if (obj.hasOwnProperty(item.last_only)) {
                                    obj[item.last_only].attr_list.push(attr_list)
                                } else {
                                    obj[item.last_only] = {
                                        attr_group_id: value.length + Object.keys(obj).length + 1,
                                        attr_group_name: group.label,
                                        attr_list: [attr_list],
                                    }
                                }
                            }
                        })
                    })
                })

                // 限制添加的规格组
                if (Object.keys(obj).length + self.value.length > this.attrGroupMaxCount) {
                    self.$message.error("规格名最多添加" + this.attrGroupMaxCount + "个");
                    return;
                }
                self.value.push.apply(self.value, Object.values(obj));
                this.$emit('submit', this.value);
                self.templateDialog = false;
            },


            emptyModel() {
                const self = this;
                const h = self.$createElement;

                function navTemplate() {
                    self.$navigate({
                        r: 'mall/goods-attr-template/index'
                    }, true);
                }

                self.$msgbox({
                    title: '提示',
                    confirmButtonText: '我知道了',
                    message: h('p', {style: 'color:#666666;font-size:14px'}, [
                        h('p', null, '暂无规格模板数据'),
                        h('span', null, '请先至'),
                        h('span', {on: {click: navTemplate}, style: 'color:#3399ff;cursor:pointer'}, '商品管理-规格模板'),
                        h('span', null, '中添加'),
                    ]),
                    type: 'warning'
                });
            },

            getList() {
                request({
                    params: {
                        r: 'mall/goods-attr-template/index',
                        page_size: 999
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        this.templateList = e.data.data.list.map(group => {
                            let only = this.only++;
                            let children = group.attr_list.map(attr => {
                                return {
                                    last_only: only,
                                    id: attr.attr_id,
                                    label: attr.attr_name,
                                    disabled: false,
                                    only: this.only++,
                                };
                            });
                            return {
                                id: group.attr_group_id,
                                label: group.attr_group_name,
                                only: only,
                                disabled: false,
                                children: children,
                            };
                        });
                    } else {
                        this.$message.error(e.data.msg);
                    }
                })
            },
        }
    });
</script>