<style>
    .app-area-limit .form-button {
        margin: 0 !important;
    }

    .app-area-limit .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .app-area-limit .button-item {
        padding: 9px 25px;
    }

    .app-area-limit .doit {
        position: absolute;
        right: 20px;
        top: 20px;
    }

    .app-area-limit .el-dialog {
        min-width: 800px;
    }
</style>

<template id="app-area-limit">
    <div>
        <el-button size="small" v-if="value[0].list.length == 0" type="text" @click="openDistrict">
            <i class="el-icon-plus">添加地区</i>
        </el-button>
        <el-card style="position: relative" shadow="never"
                 style="margin-bottom: 12px;width: 650px" v-else
                 v-for="(item, index) in value" :key="item.id">
            <div flex="dir:left box:last">
                <div>
                    <div flex="dir:left" style="flex-wrap: wrap;width: 90%">
                        <div style="margin: auto 0">区域：</div>
                        <el-tag type="info" style="margin:5px;border:0" v-for="(value, key) in item.list"
                                :key="key.id">
                            {{value.name}}
                        </el-tag>
                    </div>
                </div>
                <div class="doit">
                    <el-button size="small" type="text" circle @click="openDistrict(index)">
                        <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                            <img src="statics/img/mall/edit.png" alt="">
                        </el-tooltip>
                    </el-button>
                    <el-button size="small" circle type="text" @click="deleteDistrict(index)">
                        <el-tooltip class="item" effect="dark" content="删除" placement="top">
                            <img src="statics/img/mall/del.png" alt="">
                        </el-tooltip>
                    </el-button>
                </div>
            </div>
        </el-card>
        <el-dialog title="选择地区" :visible.sync="dialogVisible" width="50%">
            <div style="margin-bottom: 1rem;">
                <app-district :edit="detail" @selected="selectDistrict" :level="3"></app-district>
                <div style="text-align: right;margin-top: 1rem;">
                    <el-button type="primary" @click="districtConfirm">
                        确定选择
                    </el-button>
                </div>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('app-area-limit', {
        template: '#app-area-limit',
        props: {
            value: {
                type: Array,
                default: [{list: []}],
            },
        },
        data() {
            return {
                loading: false,
                submitLoading: false,
                dialogVisible: false,
                detail: {
                    list: []
                }
            };
        },
        methods: {
            openDistrict(index) {
                this.detail = JSON.parse(JSON.stringify(this.value));
                this.dialogVisible = true;
            },
            deleteDistrict(index) {
                let temp = JSON.parse(JSON.stringify(this.value));
                temp[0].list = [];
                this.$emit('input', temp);
            },
            selectDistrict(e) {
                let list = [];
                for (let i in e) {
                    let obj = {
                        id: e[i].id,
                        name: e[i].name
                    };
                    list.push(obj);
                }
                this.detail[0].list = list;
            },
            districtConfirm() {
                this.$emit('input', this.detail);
                this.detail = [];
                this.dialogVisible = false;
            },
        }
    });
</script>
