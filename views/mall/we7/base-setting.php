<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

?>
<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
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
</style>
<section id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>基础设置</span>
            </div>
        </div>
        <div class="form-body">
            <el-card v-loading="loading" shadow="never" style="border: none;">
                <el-form :model="form" :rules="formRules" size="small" ref="form" label-width="120px">
                    <el-form-item label="站点logo">
                        <app-upload @complete="updateSuccess" accept="image/vnd.microsoft.icon" :params="params"
                                    v-model="form.file" :simple="true">
                            <el-button size="small">上传文件</el-button>
                        </app-upload>
                        <div class="preview">仅支持上传 .ico 格式文件</div>
                    </el-form-item>
                </el-form>
            </el-card>
        </div>
<!--        <el-button type="primary" class="button-item" @click="onSubmit" size="small">保存</el-button>-->
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {},
                loading: false,
                params: {
                    r: 'mall/we7/upload-logo'
                },
                formRules: {

                }
            };
        },
        methods: {
            updateSuccess(e) {
                this.$message.success('上传成功')
            }
        },
        created() {

        }
    })
</script>