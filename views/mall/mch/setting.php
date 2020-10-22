<?php
Yii::$app->loadViewComponent('app-setting');
?>
<style>
    .red {
        color: #ff4544;
        margin-left: 20px;
    }

    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
        min-width: 1200px;
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
<div id="app" v-cloak>
    <el-card v-loading="loading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">基础设置</div>
        <el-form v-if="is_show" class="form-body" ref="form" :model="form" :rules="rule" label-width="160px" size="small">
            <el-row>
                <el-col :span="24">
                    <app-setting sign="mch" :label_show="false" :is_discount="false" v-model="form" :is_share="mchMallSetting.is_share == 1" :is_payment="false" :is_surpport_city="false"></app-setting>
                    <el-form-item label="客服外链开关">
                        <el-switch
                                v-model="form.is_web_service"
                                :active-value="1"
                                :inactive-value="0"
                                active-color="#409EFF">
                        </el-switch>
                    </el-form-item>
                    <el-form-item label="客服外链" v-if="form.is_web_service == '1'"
                                  prop="web_service_url">
                        <el-input v-model="form.web_service_url"></el-input>
                    </el-form-item>
                    <el-form-item v-if="form.is_web_service == '1'">
                        <app-attachment :multiple="false" :max="1" v-model="form.web_service_pic">
                            <el-tooltip effect="dark"
                                        content="建议尺寸:100 * 100"
                                        placement="top">
                                <el-button size="mini">选择图标</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-image style="width: 80px; height: 80px;"
                                   :src="form.web_service_pic"></app-image>
                    </el-form-item>
                </el-col>
            </el-row>
        </el-form>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('form')" size="small">保存</el-button>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                form: {},
                rule: {},
                is_show: false,
                mchMallSetting: {},
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/mch/setting'
                            },
                            method: 'post',
                            data: this.form
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        });
                    } else {
                        this.btnLoading = false;
                        console.log('error submit!!');
                        return false;
                    }
                })
            },
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/mch/setting'
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    this.is_show = true;
                    if (e.data.code == 0) {
                        this.form = e.data.data.detail;
                        this.mchMallSetting = e.data.data.mchMallSetting;
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
        }
    });
</script>
