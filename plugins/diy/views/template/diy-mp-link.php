<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/25
 * Time: 13:48
 */
?>
<style>
    .look-btn {
        height: 52px;
        line-height: 50px;
        text-align: center;
        width: 120px;
        background-color: #fff;
        border: 1px solid #308e20;
        color: #308e20;
        font-size: 24px;
        border-radius: 8px;
    }
</style>
<template id="diy-mp-link">
    <div>
        <div class="diy-component-preview">
            <div style="background: #fff;border: 1px solid #e2e2e2;border-width: 1px 0;padding: 20px;">
                <div style="color: #909399;font-size: 20px;margin-bottom: 10px;">小程序关联的公众号</div>
                <div flex="box:justify cross:center">
                    <div>
                        <div style="height: 80px;width: 80px;background: #333;border-radius: 100px; margin-right: 20px;"></div>
                    </div>
                    <div>
                        <div style="font-size: 30px;">公众号名称</div>
                        <div style="font-size: 24px;color: #666;">公众号简介</div>
                    </div>
                    <div>
                        <div class="look-btn">查看</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="组件位置">
                    <app-radio v-model="data.position" label="auto">自动</app-radio>
                    <app-radio v-model="data.position" label="top">悬浮顶部</app-radio>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-mp-link', {
        template: '#diy-mp-link',
        props: {
            value: Object,
        },
        data() {
            return {
                data: {
                    position: 'auto',
                }
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {},
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