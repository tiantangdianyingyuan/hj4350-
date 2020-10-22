<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/23
 * Time: 11:17
 */
?>
<template id="diy-empty">
    <div>
        <div class="diy-component-preview">
            <div style="padding: 20px 0">
                <div class="diy-empty" :style="cStyle"></div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="背景颜色">
                    <el-color-picker size="small" v-model="data.background"></el-color-picker>
                    <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.background"></el-input>
                </el-form-item>
                <el-form-item label="高度">
                    <el-input size="small" v-model.number="data.height" type="number" min="1">
                        <div slot="append">px</div>
                    </el-input>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-empty', {
        template: '#diy-empty',
        props: {
            value: Object
        },
        data() {
            return {
                data: {
                    background: '#ffffff',
                    height: 10,
                }
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', this.data)
            } else {
                this.data = this.value;
            }
        },
        computed: {
            cStyle() {
                if(this.data.background) {
                    return `background: ${this.data.background};`
                        + `height: ${this.data.height}px;`;
                }else {
                    return `height: ${this.data.height}px;`;
                }
            },
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {}
    });
</script>