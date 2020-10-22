<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/23
 * Time: 16:15
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
$urlManager = Yii::$app->urlManager;
$baseUrl = Yii::$app->request->baseUrl;
$this->title = "淘宝CSV上传";
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
            <span>淘宝CSV上传</span>
        </div>
        <div>
            <div class="danger">
                尽量在服务器空闲时间来操作，会占用大量内存与带宽，在获取过程中，请不要进行任何操作!
            </div>
            <el-card shadow="never">
                <div slot="header">
                    <span>淘宝CSV上传助手</span>
                </div>
                <div>
                    <div>功能介绍：可将
                        <el-button type="text" @click="goto">淘宝助理</el-button>
                        以及其他途径获取的淘宝商品CSV文件快速上传至商城,节约您的大量时间!
                    </div>
                    <div flex="dir:left box:first">
                        <div>使用方法：</div>
                        <div>
                            <span> 1. 将您获取到的CSV文件转存为Excel格式,否则将无法识别</span>
                            <br>
                            <span>2. 将配套的图片文件包压缩为Zip格式压缩包并且导入(图片需在压缩包根目录下) </span>
                            <br>
                            <span>3. 确认上传即可</span>
                        </div>
                    </div>
                    <div>Excel示例文件：
                        <el-button type="text" size="mini"
                                   @click="download('<?= $baseUrl . '/test.xlsx' ?>')">Excel示例文件
                        </el-button>
                    </div>
                    <div>
                        <div>Zip示例文件：
                            <el-button type="text" size="mini"
                                       @click="download('<?= $baseUrl . '/test.zip' ?>')">Zip示例文件
                            </el-button>
                        </div>
                    </div>
                    <div>
                        <div style="color: #ff4544">注意：导入的商品尽量控制在10个以内</div>
                    </div>
                </div>
            </el-card>
            <el-col :span="12">
                <el-form :model="ruleForm" ref="ruleForm" size="small" label-width="120px" style="margin-top: 24px;"
                         enctype="multipart/form-data">
                    <el-form-item label="EXCEL">
                        <div flex>
                            <el-upload
                                    action=""
                                    :http-request="handleFile"
                                    :multiple="false"
                                    :limit="2"
                                    :on-change="excelChange"
                                    :on-exceed="handleExceed"
                                    :show-file-list="false">
                                <el-button size="mini" type="primary">点击上传</el-button>
                            </el-upload>
                            <label style="margin-left: 20px;">{{fileList.excel ? fileList.excel.name : ''}}</label>
                        </div>
                    </el-form-item>
                    <el-form-item label="ZIP">
                        <div flex>
                            <el-upload
                                    action=""
                                    :http-request="handleFile"
                                    :multiple="false"
                                    :limit="2"
                                    :on-change="zipChange"
                                    :on-exceed="handleExceed"
                                    :show-file-list="false">
                                <el-button size="mini" type="primary">点击上传</el-button>
                            </el-upload>
                            <label style="margin-left: 20px;">{{fileList.zip ? fileList.zip.name : ''}}</label>
                        </div>
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
                    excel: '',
                    zip: '',
                },
                btnLoading: false,
                fileList: {
                    excel: '',
                    zip: '',
                },
            };
        },
        methods: {
            submit() {
                this.btnLoading = true;
                let formData = new FormData();
                formData.append('excel', this.ruleForm.excel);
                formData.append('zip', this.ruleForm.zip);
                request({
                    header: {
                        'Content-Type': 'multipart/form-data'
                    },
                    params: {
                        r: 'mall/goods/taobao-csv'
                    },
                    data: formData,
                    method: 'post'
                }).then(e => {
                    this.btnLoading = false;
                    if (e.data.code == 1) {
                        this.$message.error(e.data.msg);
                    } else {
                        this.$message.success(e.data.msg);
                    }
                }).catch(e => {
                    this.btnLoading = false;
                });
            },
            handleFile() {
            },
            handleExceed(files, fileList) {
                this.$message.warning(`最多上传 ${files.length} 个文件`)
            },
            excelChange(file, fileList) {
                this.fileList.excel = file;
                this.ruleForm.excel = file.raw;
                fileList.splice(0, 1);
            },
            zipChange(file, fileList) {
                this.fileList.zip = file;
                this.ruleForm.zip = file.raw;
                fileList.splice(0, 1);
            },
            goto() {
                navigateTo('https://zhuli.taobao.com/', true);
            },
            download(url) {
                navigateTo(url, true);
            }
        }
    });
</script>
