<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/11
 * Time: 10:55
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
$urlManager = Yii::$app->urlManager;
$baseUrl = Yii::$app->request->baseUrl;
$this->title = "商城导入";
?>
<style>
    .danger {
        background-color: #fce9e6;
        width: 100%;
        border-color: #edd7d4;
        color: #e55640;
        border-radius: 2px;
        padding: 15px;
        margin-bottom: 20px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never">
        <div slot="header">
            <span>商城导入</span>
        </div>
        <div>
            <div class="danger">
                尽量在服务器空闲时间来操作，会占用大量内存与带宽，在获取过程中，请不要进行任何操作！！！
            </div>
            <div class="danger">
                请使用新建的商城进行导入数据操作，否则会产生不必要的影响！！！
            </div>
            <el-card shadow="never">
                <div>功能介绍：可将商城数据快速导入至商城,节约您的大量时间!
                </div>
                <div>说明：商城导入中，请不要关闭页面，导入时间可能会很长，请耐心等待
                </div>
            </el-card>
            <el-col :lg="12" :xl="24">
                <el-form :model="ruleForm" ref="ruleForm" size="small" label-width="120px" style="margin-top: 24px;"
                         enctype="multipart/form-data">
                    <el-form-item label="商城数据导入码">
                        <el-input v-model="ruleForm.code"></el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="submit" :loading="btnLoading">确定导入</el-button>
                    </el-form-item>
                </el-form>
            </el-col>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    code: '',
                },
                btnLoading: false,
            };
        },
        methods: {
            submit() {
                this.btnLoading = true;
                request({
                    params: {
                        r: 'mall/import/index'
                    },
                    data: this.ruleForm,
                    method: 'post'
                }).then(e => {
                    this.btnLoading = false;
                    if (e.data.code == 1) {
                        this.$message.error(e.data.msg);
                    } else {
                        this.$message.success(e.data.msg)
                    }
                }).catch(e => {
                    this.btnLoading = false;
                });
            },
        }
    });
</script>
