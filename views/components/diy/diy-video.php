<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/24
 * Time: 20:07
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .diy-video {
        width: 100%;
        height: 400px;
        background: #353535;
    }

    .diy-video .el-input-group__append {
        background-color: #fff
    }
</style>
<template id="diy-video">
    <div>
        <div class="diy-component-preview">
            <div class="diy-video">
                <img :src="data.pic_url" style="width: 100%;height:100%;" v-if="data.pic_url">
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="封面图片">
                    <app-attachment title="选择图片" :multiple="false" :max="1" type="image" v-model="data.pic_url">
                        <el-tooltip class="item" effect="dark"
                                    content="建议尺寸750*400"
                                    placement="top">
                            <el-button size="mini">选择图片</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <app-gallery :url="data.pic_url" :show-delete="true"
                                 @deleted="deletePic()"></app-gallery>
                </el-form-item>
                <el-form-item label="视频链接">
                    <label slot="label">视频链接
                        <el-tooltip class="item" effect="dark"
                                    content="支持格式mp4;支持编码H.264;视频大小不能超过50 MB"
                                    placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </label>
                    <el-input size="small" v-model="data.url" placeholder="请输入视频原地址或选择上传视频">
                        <template slot="append">
                            <app-attachment :multiple="false" :max="1" v-model="data.url"
                                            type="video">
                                <el-button size="mini">选择文件</el-button>
                            </app-attachment>
                        </template>
                    </el-input>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-video', {
        template: '#diy-video',
        props: {
            value: Object
        },
        data() {
            return {
                data: {
                    pic_url: '',
                    url: ''
                },
            }
        },
        created() {
            if (!this.value) {
                this.$emit('input', this.data)
            } else {
                this.data = this.value;
            }
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            deletePic() {
                this.data.pic_url = '';
            }
        }
    });
</script>
