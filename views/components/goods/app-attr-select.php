<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/5
 * Time: 19:20
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .app-attr-dialog {
        min-width: 700px;
    }
    .app-attr-dialog .block {
        padding: 12px;
    }
</style>
<template id="app-attr-select">
    <div class="app-attr-select">
        <el-dialog append-to-body title="规格选择" :visible.sync="visible" :close-on-click-modal="false"
                   custom-class="app-attr-dialog">
            <div>
                <template v-for="(attr_group, index) in attrGroups">
                    <div class="block">{{attr_group.attr_group_name}}</div>
                    <el-radio-group v-model="attrGroups[index].active">
                        <el-radio class="block" v-for="(attr, key) in attr_group.attr_list" :key="attr.attr_id"
                                  :label="key">{{attr.attr_name}}
                        </el-radio>
                    </el-radio-group>
                </template>
            </div>
            <div style="margin-top: 24px;">
                <el-row>
                    <el-button type="primary" size="small" style="float: right" @click="confirm">选择</el-button>
                </el-row>
            </div>
        </el-dialog>
        <div @click="click" style="display: inline-block">
            <slot></slot>
        </div>
    </div>
</template>
<script>
    Vue.component('app-attr-select', {
        template: '#app-attr-select',
        props: {
            attrGroups: Array // 规格组信息
        },
        data() {
            return {
                visible: false,
                result: []
            };
        },
        created() {
            this.defaultData();
        },
        watch: {
            attrGroups() {
                this.defaultData();
            }
        },
        methods: {
            click() {
                this.visible = !this.visible;
            },
            confirm() {
                this.getResult();
                this.visible = !this.visible;
            },
            defaultData() {
                if (this.attrGroups && this.attrGroups.length === 1 && this.attrGroups[0].attr_list.length === 1) {
                    this.attrGroups[0].active = 0;
                    this.getResult();
                }
            },
            getResult() {
                let result = [];
                for (let i in this.attrGroups) {
                    let temp = this.attrGroups[i];
                    result[i] = {
                        attr_group_name: temp.attr_group_name,
                        attr_name: temp.attr_list[temp.active].attr_name,
                    }
                }
                this.$emit('input', result);
            }
        }
    });
</script>
