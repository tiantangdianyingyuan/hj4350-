<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .form-body {
        padding: 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 40%;
    }

    .form-button {
        margin: 0 !important;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .add-image-btn {
        width: 100px;
        height: 100px;
        color: #419EFB;
        border: 1px solid #e2e2e2;
        cursor: pointer;
    }

    .pic-url-remark {
        font-size: 13px;
        color: #c9c9c9;
        margin-bottom: 12px;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .price-item {
        margin-bottom: 15px;
    }
    .price-item .el-radio {
        margin-right: 15px;
    }
    .input-item {
        width: 100%;
    }

    .goods-images {
        width: 50px;
        height: 50px;
        margin-right: 10px;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="cardLoading">
        <div slot="header">
            <div>
                <span></span>
            </div>
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'mall/live/index'})">
                        直播间管理
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>创建直播间</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="130px">
                <el-form-item label="直播间名称" prop="name">
                    <el-input v-model="ruleForm.name" placeholder="请输入直播间名称,字数限制3-17" max="250"></el-input>
                </el-form-item>
                <el-form-item label="主播昵称" prop="anchor_name">
                    <el-input v-model="ruleForm.anchor_name" placeholder="请输入主播昵称,字数限制2-15" max="250"></el-input>
                </el-form-item>
                <el-form-item label="主播微信号" prop="anchor_wechat">
                    <el-input v-model="ruleForm.anchor_wechat" placeholder="请输入主播微信号" max="250"></el-input>
                    <template v-if="is_show_code">
                        <div style="color: red;">
                            <p style="margin-top: 0;margin-bottom: 10px;">主播未验证，请扫描下方二位码进行身份验证</p>
                        </div>
                        <img style="width: 120px;height: 120px;" src="https://res.wx.qq.com/op_res/BbVNeczA1XudfjVqCVoKgfuWe7e3aUhokktRVOqf_F0IqS6kYR--atCpVNUUC3zr">
                    </template>
                </el-form-item>
                <el-form-item prop="date_time">
                    <template slot='label'>
                        <span>计划直播时间</span>
                        <el-tooltip effect="dark" placement="top">
                            <div slot="content">
                                开播时间需要在当前时间的10分钟后，并且开始时间不能在6个月后<br/>开播时间和结束时间间隔不得短于30分钟，不得超过12小时
                            </div>
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </template>
                    <el-date-picker
                      v-model="ruleForm.date_time"
                      type="datetimerange"
                      value-format="yyyy-MM-dd HH:mm:ss"
                      range-separator="至"
                      start-placeholder="开始日期"
                      end-placeholder="结束日期">
                    </el-date-picker>
                </el-form-item>
                <el-form-item label="直播类型" prop="type">
                    <el-radio v-model="ruleForm.type" :label="1">推流</el-radio>
                    <el-radio v-model="ruleForm.type" :label="0">手机直播</el-radio>
                </el-form-item>
                <el-form-item label="屏幕旋转" prop="screen_type">
                    <el-radio v-model="ruleForm.screen_type" :label="1">横屏</el-radio>
                    <el-radio v-model="ruleForm.screen_type" :label="0">竖屏</el-radio>
                </el-form-item>
                <el-form-item label="开启评论" prop="close_comment">
                    <el-switch v-model="ruleForm.close_comment" :active-value="0" :inactive-value="1"></el-switch>
                </el-form-item>
                <el-form-item label="允许点赞" prop="close_like">
                    <el-switch v-model="ruleForm.close_like" :active-value="0" :inactive-value="1"></el-switch>
                </el-form-item>
                <el-form-item label="开启货架" prop="close_goods">
                    <el-switch v-model="ruleForm.close_goods" :active-value="0" :inactive-value="1"></el-switch>
                </el-form-item>
                <el-form-item label="直播背景图" prop="cover_img" >
                    <app-attachment :multiple="false" :max="1" v-model="ruleForm.cover_img">
                        <el-tooltip effect="dark" content="建议像素1080*1920,大小不超过2M" placement="top">
                            <el-button style="margin-bottom: 10px;" size="mini">选择图片</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <app-gallery :url="ruleForm.cover_img" :show-delete="true" @deleted="ruleForm.cover_img = ''"
                                 width="80px" height="80px">
                    </app-gallery>
                </el-form-item>
                <el-form-item label="直播分享图" prop="share_img" >
                    <app-attachment :multiple="false" :max="1" v-model="ruleForm.share_img">
                        <el-tooltip effect="dark" content="建议像素800*640,大小不超过1M" placement="top">
                            <el-button style="margin-bottom: 10px;" size="mini">选择图片</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <app-gallery :url="ruleForm.share_img" :show-delete="true" @deleted="ruleForm.share_img = ''"
                                 width="80px" height="80px">
                    </app-gallery>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">    保存
        </el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    name: '',
                    cover_img: '',
                    anchor_name: '',
                    anchor_wechat: '',
                    share_img: '',
                    type: 0,
                    screen_type: 0,
                    close_like: 0,
                    close_goods: 0,
                    close_comment: 0,
                    date_time: [],
                },
                rules: {
                    name: [
                        {required: true, message: '请输入直播间名称', trigger: 'change'},
                    ],
                    anchor_name: [
                        {required: true, message: '请输入主播昵称', trigger: 'change'},
                    ],
                    anchor_wechat: [
                        {required: true, message: '请输入主播微信号', trigger: 'change'},
                    ],
                    date_time: [
                        {required: true, message: '请添加直播时间', trigger: 'change'},
                    ],
                    cover_img: [
                        {required: true, message: '请选择直播背景图', trigger: 'blur'},
                    ],
                    share_img: [
                        {required: true, message: '请选择直播分享图', trigger: 'blur'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
                is_show_code: false,
            };
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    self.ruleForm.start_time = self.ruleForm.date_time[0];
                    self.ruleForm.end_time = self.ruleForm.date_time[1];
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/live/live-edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'mall/live/index',
                                })
                            } else {
                                if (e.data.msg == '无效的微信号') {
                                    self.is_show_code = true;
                                } else {
                                    self.$message.error(e.data.msg);
                                }
                            }
                        }).catch(e => {
                            self.$message.error(e.data.msg);
                            self.btnLoading = false;
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
        },
        mounted() {

        }
    });
</script>
