<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/5/6
 * Time: 10:47
 */
?>
<style>
    .diy-modal .modal-background {
        width: 224px;
        height: 400px;
        background: rgba(0, 0, 0, 0.5);
        border-radius: 5px;
        margin-right: 20px;
    }

    .diy-modal .modal-container {
        max-width: 184px;
        max-height: 300px;
        overflow: hidden;
    }

    .diy-modal .modal-container img {
        max-width: 100%;
        max-height: 100%;
    }

    .diy-modal .pic-select {
        width: 72px;
        height: 72px;
        color: #00a0e9;
        border: 1px solid #ccc;
        line-height: normal;
        text-align: center;
        cursor: pointer;
        font-size: 12px;
    }

    .diy-modal .pic-preview {
        width: 72px;
        height: 72px;
        border: 1px solid #ccc;
        cursor: pointer;
        background-position: center;
        background-repeat: no-repeat;
        background-size: contain;
    }

    .diy-modal .edit-options {
        position: relative;
    }

    .diy-modal .edit-options .el-button {
        height: 25px;
        line-height: 25px;
        width: 25px;
        padding: 0;
        text-align: center;
        border: none;
        border-radius: 0;
        position: absolute;
        margin-left: 0;
    }

    .chooseLink .el-input-group__append {
        background-color: #fff;
    }
</style>
<template id="diy-modal">
    <div class="diy-modal">
        <div class="diy-component-preview">
            <div @click="dialogVisible=true" style="padding: 20px 0;text-align: center;">
                <div>弹窗广告设置</div>
                <div style="font-size: 22px;color: #909399">本条内容不占高度</div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-dialog title="弹窗广告设置" :visible.sync="dialogVisible" :close-on-click-modal="false">
                <div flex="box:first">
                    <div>
                        <div class="modal-background"
                             flex="main:center cross:center">
                            <div class="modal-container" flex="main:center cross:center">
                                <img v-if="data.list.length" :src="data.list[0].picUrl">
                            </div>
                        </div>
                    </div>
                    <div>
                        <el-form @submit.native.prevent>
                            <el-form-item label="是否开启">
                                <el-switch v-model="data.opened"></el-switch>
                            </el-form-item>
                            <el-form-item v-if="data.opened" label="广告图片">
                                <span style="color: #909399;">图片最大高度700px，最大宽度650px。</span>
                                <br>
                                <div style="border: 1px solid #e2e2e2;padding: 5px;margin-bottom: 5px;line-height: normal;max-width: 400px" v-for="(item,index) in data.list">
                                    <div class="edit-options">
                                        <el-button @click="deleteAd(index)" icon="el-icon-delete" type="primary"
                                                   style="right: -31px;top: -6px;"></el-button>
                                    </div>
                                    <div flex="box:first">
                                        <div style="margin-right: 10px">
                                            <app-image-upload v-model="item.picUrl"></app-image-upload>
                                        </div>
                                        <div class="chooseLink">
                                            <div style="margin: 5px 0;">广告链接</div>
                                            <div @click="pickLinkClick(index)">
                                                <el-input v-model="item.link.url" placeholder="点击选择链接" :disabled="true"
                                                              size="small">
                                                    <app-pick-link slot="append" @selected="linkSelected">
                                                        <el-button size="small">选择链接</el-button>
                                                    </app-pick-link>
                                                </el-input>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <el-button size="small" @click="addAd" v-if="data.list.length<3">添加广告</el-button>
                            </el-form-item>
                            <el-form-item v-if="data.opened" label="弹窗次数">
                                <app-radio v-model="data.times" :label="0">每次</app-radio>
                                <app-radio v-model="data.times" :label="1">仅首次</app-radio>
                            </el-form-item>
                            <el-button style="padding: 9px 25px" v-if="data.opened" size="small" type="primary" @click="dialogVisible = false">保存</el-button>
                        </el-form>
                    </div>
                </div>
            </el-dialog>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-modal', {
        template: '#diy-modal',
        props: {
            value: Object,
        },
        data() {
            return {
                dialogVisible: false,
                currentEditListIndex: null,
                data: {
                    opened: false,
                    list: [
                        {
                            picUrl: '',
                            link: {
                                url: '',
                                openType: '',
                            },
                        },
                    ],
                    times: 0,
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
        methods: {
            addAd() {
                this.data.list.push({
                    picUrl: '',
                    link: {
                        url: '',
                        openType: '',
                    },
                });
            },
            deleteAd(index) {
                this.data.list.splice(index, 1);
            },
            pickLinkClick(index) {
                this.currentEditListIndex = index;
            },
            linkSelected(list, params) {
                if (!list.length) {
                    return;
                }
                const link = list[0];
                if (this.currentEditListIndex !== null) {
                    this.data.list[this.currentEditListIndex].link.openType = link.open_type;
                    this.data.list[this.currentEditListIndex].link.url = link.new_link_url;
                    this.currentEditListIndex = null;
                }
            },
        }
    });
</script>