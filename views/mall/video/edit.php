<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
    }

    .form-button {
        margin: 0!important;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    } 
</style>
<section id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'mall/video/index'})">视频</span></el-breadcrumb-item>
                <el-breadcrumb-item>视频编辑</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-card v-loading="loading" shadow="never" style="border: none;">
                <el-form :model="form" :rules="FormRules" size="small" ref="form" label-width="120px">
                    <el-form-item label="标题" prop="title">
                        <el-input v-model="form.title"></el-input>
                    </el-form-item>
                    <el-form-item label="视频来源" prop="type">
                        <el-radio v-model="form.type" :label="0">源地址</el-radio>
                        <el-radio v-model="form.type" :label="1">腾讯</el-radio>
                    </el-form-item>
                    <el-form-item label="视频链接" prop="url">
                        <el-input placeholder="请输入链接" v-model="form.url">
                            <template slot="append">
                                <app-attachment type="video" :multiple="false" :max="1" @selected="videoUrl">
                                    <el-tooltip class="item" effect="dark" content="支持格式mp4;支持编码H.264;视频大小不能超过50 MB" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                </app-attachment>
                            </template>
                        </el-input>
                        <a class="video-check" style="text-decoration:none" :href="form.url" target="_blank">视频预览</a>
                    </el-form-item>
                    <el-form-item label="封面图" prop="pic_url">
                        <app-attachment v-model="form.pic_url" :multiple="false" :max="1">
                            <el-tooltip class="item"
                                        effect="dark"
                                        content="建议尺寸:750 * 400"
                                        placement="top">
                                <el-button size="mini">选择图片</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-image mode="aspectFill" width='80px' height='80px' :src="form.pic_url"></app-image>
                    </el-form-item>
                    <el-form-item label="排序" prop="sort">
                        <el-input  v-model="form.sort"></el-input>
                    </el-form-item>
                    <el-form-item label="详情介绍" prop="content">
                        <el-input type="textarea" :rows="4" v-model="form.content"></el-input>
                    </el-form-item>
                </el-form>
            </el-card>
        </div>
        <el-button type="primary" class="button-item" @click="onSubmit" size="small">保存</el-button>
    </el-card>
</section>
<style>
.form_box {
    width: 50%;
}
</style>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            form: {
                title: '',
                type: 0,
                url: '',
                pic_url: '',
                sort: '',
                content: '',
            },
            loading: false,
            FormRules: {
                title: [
                    { required: true, message: '标题不能为空', trigger: 'blur' },
                    { min: 1, max: 30, message: "标题长度在1-30个字符内" },
                ],
                type: [
                    { required: true, message: '视频来源', trigger: 'blur' },
                ],
                pic_url: [
                    { required: true, message: '封面图', trigger: 'blur' },
                ],
                sort: [
                    { required: false, pattern: /^[0-9]\d{0,8}$/, message: '排序必须在9位整数内' }
                ]
            },
        };
    },
    methods: {
        // 视频
        videoUrl(e) {
            if (e.length) {
                this.form.url = e[0].url;
            }
        },
        // 图片
        picUrl(e) {
            if (e.length) {
                this.form.pic_url = e[0].url;
            }
        },
        onSubmit() {
            this.$refs.form.validate((valid) => {
                if (valid) {
                    let para = Object.assign({}, this.form);
                    request({
                        params: {
                            r: 'mall/video/edit',
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            navigateTo({ r: 'mall/video/index' });
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {});
                }
            });
        },

        getList() {
            this.loading = true;
            request({
                params: {
                    r: 'mall/video/edit',
                    id: getQuery('id'),
                },
            }).then(e => {
                this.loading = false;
                if (e.data.code == 0) {
                    if (e.data.data.list) {
                        this.form = e.data.data.list;
                    }
                }
            }).catch(e => {
                this.loading = false;
            });
        },
    },
    created() {
        this.getList();
    }
})
</script>