<?php
defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-rich-text');
Yii::$app->loadViewComponent('app-topic-detail');
?>
<style>
    .pic {
        height: 100px;
        width: 100px;
        border: 1px solid #e3e3e3;
        border-radius: 2px;
        overflow: hidden;
        margin: 5px 0;
        font-size: 12px;
        display: inline-block;
    }

    .el-button + .el-button {
        margin-left: 0;
    }

    .pic span {

    }

    .pic img {
        max-width: 100%;
        max-height: 100%;
        margin: auto auto;
    }

    #edui1 {
        width: 100% !important;
    }

    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
        min-width: 1000px;
    }

    .form-button {
        margin: 0 !important;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button {
        position: fixed;
        bottom: 0;
        width: 100%;
        background: #FFFFFF;
        text-align: center;
        padding: 10px;
    }

    .button-item {
        padding: 9px 25px;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }
</style>
<section id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'mall/topic/index'})">专题</span></el-breadcrumb-item>
                <el-breadcrumb-item>专题编辑</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <el-tabs v-model="activeName" @tab-click="handleClick" style="background-color: #FFFFFF;padding: 20px;">
            <el-tab-pane label="基础设置" name="first">
                <div class="form-body">
                    <el-form :model="form" label-width="10rem" v-loading="loading" :rules="FormRules" ref="form">
                        <el-form-item label="标题" prop="title" size="small">
                            <el-input v-model="form.title" auto-complete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="专题列表布局方式" prop="layout" size="small">
                            <el-radio v-model="form.layout" :label="0">小图模式</el-radio>
                            <el-radio v-model="form.layout" :label="1">大图模式</el-radio>
                            <el-radio v-model="form.layout" :label="2">多图模式</el-radio>
                            <div style="color: #636C72;font-size: 12px;margin-top: -0.5rem;">
                                小图模式建议封面图片大小：268×202；大图模式建议封面图片大小：702×350；多图模式建议封面图片大小：268×202，最多上传3张图片
                            </div>
                        </el-form-item>
                        <el-form-item label="封面图" prop="cover_pic" size="small">
                            <app-attachment @selected="coverUrl" :multiple="form.layout == 2">
                                <el-tooltip class="item" :max="3"
                                            effect="dark"
                                            :content="form.layout == 1 ? '建议尺寸: 702 * 350' : '建议尺寸: 268 * 202'"
                                            placement="top">
                                    <el-button size="mini">选择图片</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <app-image width="80px" height="80px" mode="aspectFill" :src="form.cover_pic"
                                       v-if="form.layout != 2"></app-image>
                            <draggable v-model="form.pic_list" flex="dif:left" v-else>
                                <div v-for="(item,index) in form.pic_list" :key="index"
                                     style="margin-right: 20px;position: relative;cursor: move;">
                                    <app-image mode="aspectFill"
                                               width="100px"
                                               height='100px'
                                               :src="item.url">
                                    </app-image>
                                    <el-button class="del-btn"
                                               size="mini" type="danger" icon="el-icon-close"
                                               circle style="padding: 4px 4px;"
                                               @click="deletePic(index)"></el-button>
                                </div>
                            </draggable>
                        </el-form-item>
                        <el-form-item prop="abstract" size="small">
                            <template slot='label'>
                                <span>摘要</span>
                                <el-tooltip effect="dark" content="专题内容的简介，用于列表上显示"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input type="textarea" v-model="form.abstract" auto-complete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="自定义分享标题" prop="app_share_title" size="small">
                            <el-input v-model="form.app_share_title" auto-complete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="自定义分享图片">
                            <app-attachment v-model="form.qrcode_pic" :multiple="false" :max="1">
                                <el-tooltip class="item"
                                            effect="dark"
                                            content="建议尺寸:420 * 336"
                                            placement="top">
                                    <el-button size="mini">选择图片</el-button>
                                </el-tooltip>
                            </app-attachment>

                            <div style="margin-top: 10px;width: 80px;height:80px;position: relative;cursor: move;">
                                <app-image mode="aspectFill" width='80px' height='80px'
                                           :src="form.qrcode_pic"></app-image>
                                <el-button v-if="form.qrcode_pic" class="del-btn" size="mini"
                                           type="danger" icon="el-icon-close" circle
                                           @click="form.qrcode_pic = ''"></el-button>
                            </div>
                        </el-form-item>
                        <el-form-item label="是否精选" prop="is_chosen" size="small">
                            <el-switch v-model="form.is_chosen" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>
                        <el-form-item label="分类" prop="type" size="small">
                            <el-select style="width: 180px;" size="small" v-model="form.type">
                                <el-option v-for="item in type" :key="item.id" :value="item.id" :label="item.name">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item prop="virtual_read_count" size="small">
                            <template slot='label'>
                                <span>虚拟阅读量</span>
                                <el-tooltip effect="dark" content="手机端显示的阅读量=实际阅读量+虚拟阅读量"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input v-model="form.virtual_read_count" auto-complete="off"></el-input>
                        </el-form-item>
                        <el-form-item prop="virtual_favorite_count" size="small" hidden>
                            <template slot='label'>
                                <span>虚拟收藏量</span>
                                <el-tooltip effect="dark" content="手机端显示的收藏量=实际收藏量+虚拟收藏量"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input v-model="form.virtual_favorite_count" auto-complete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="排序" prop="sort" size="small">
                            <el-input v-model="form.sort" auto-complete="off"></el-input>
                        </el-form-item>
                    </el-form>
                </div>
            </el-tab-pane>
            <el-tab-pane label="详情设置" name="second">
                <div style="padding-bottom: 40px;" v-if="form.detail">
                    <app-topic-detail v-model="form.detail"></app-topic-detail>
                </div>
            </el-tab-pane>
        </el-tabs>
        <div class="button">
            <el-button class="button-item" type="primary" @click="onSubmit" :loading=btnLoading size="small">提交
            </el-button>
        </div>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {
                    layout: 0,
                    is_chosen: '0',
                    cover_pic: '',
                    qrcode_pic: '',
                    app_share_title: '',
                    pic_list: [],
                    abstract: '',
                    detail: null,
                },
                type: [],
                loading: false,
                btnLoading: false,
                FormRules: {
                    title: [
                        {required: true, message: '标题不能为空', trigger: 'blur'},
                        {min: 1, max: 30, message: "标题长度在1-30个字符内"},
                    ],
                    cover_pic: [
                        {required: true, message: '封面图片不能为空。', trigger: 'blur'},
                    ],
                    qrcode_pic: [
                        {required: true, message: '海报分享图不能为空。', trigger: 'blur'},
                    ],
                    type: [
                        {required: true, message: '请选择专题分类。', trigger: 'blur'},
                    ],
                },
                activeName: 'first',
            };
        },
        methods: {
            resetForm(formName) {
                this.$refs[formName].resetFields();
            },
            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign(this.form);
                        request({
                            params: {
                                r: 'mall/topic/edit',
                            },
                            data: {
                                data: JSON.stringify(para)
                            },
                            method: 'post'
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                const h = this.$createElement;
                                this.$message({
                                    message: e.data.msg,
                                    type: 'success'
                                });
                                setTimeout(function () {
                                    navigateTo({r: 'mall/topic/index'});
                                }, 300);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.btnLoading = false;
                            this.$alert(e.data.msg, '提示', {
                                confirmButtonText: '确定'
                            })
                        });
                    }
                });
            },

            coverUrl(e) {
                if (e.length) {
                    this.form.cover_pic = e[0].url;
                    if (this.form.layout == 2) {
                        if (this.form.pic_list.length >= 3) {
                            this.$message.error('图片最多上传三张');
                        } else {
                            for (let i in e) {
                                if (this.form.pic_list.length < 3) {
                                    this.form.pic_list.push({
                                        url: e[i].url
                                    });
                                }
                            }
                        }
                    }
                }
            },

            deletePic(item, index) {
                this.form.pic_list.splice(index, 1)
            },

            qrcodeUrl(e) {
                if (e.length) {
                    this.form.qrcode_pic = e[0].url;
                }
            },

            getTopicForm() {
                this.loading = true;
                let _this = this;
                request({
                    params: {
                        r: 'mall/topic/edit',
                        id: getQuery('id'),
                    },
                    method: 'get'
                }).then(e => {
                    _this.loading = false;
                    if (e.data.code == 0) {
                        _this.type = e.data.data.select;
                        if (e.data.data.topic && e.data.data.topic.id > 0) {
                            _this.form = e.data.data.topic;
                            for (let i in _this.form.detail) {
                                _this.form.detail[i].active = false;
                                _this.form.detail[i].key = randomString();
                            }
                        } else {
                            _this.form.detail = [];
                        }
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            handleClick(e) {
                console.log(e)
            }

        },
        mounted() {
            this.getTopicForm();
        }
    })
</script>