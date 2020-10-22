<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>
    .form-body {
        display: flex;
        justify-content: center;
    }

    .form-body .el-form {
        width: 750px;
        margin-top: 10px;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" v-loading="loading">
        <div style="margin-bottom: 20px">域名设置</div>
        <div class='form-body' ref="body">
            <el-form label-position="left" label-width="180px" :model="form" ref="form">
                <el-form-item label="小程序业务域名校验文件">
                    <app-upload @complete="updateSuccess" :accept="'text/plain'" :params="params"
                                v-model="form.file" :simple="true">
                        <el-button size="small">上传文件</el-button>
                    </app-upload>
                    <div class="preview">仅支持上传 .txt 格式文件</div>
                </el-form-item>
            </el-form>
        </div>
    </el-card>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                form: {
                    file: '',
                },
                submitLoading: false,
                params: {
                    r: 'admin/setting/upload-file'
                }
            };
        },
        created() {
        },
        methods: {
            updateSuccess(e) {
                this.$message.success('上传成功')
            }
        }
    });
</script>
