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
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .customize-share-title {
        margin-top: 10px;
        width: 80px;
        height: 80px;
        position: relative;
        cursor: move;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }
     .app-share-bg {
        position: relative;
        width: 280px;
        height: 500px;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: center
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" class="box-card" body-style="background-color: #f3f3f3;padding: 10px 0 0;" class="box-card" v-loading="cardLoading">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'mall/service/index'})">服务</span></el-breadcrumb-item>
                <el-breadcrumb-item v-if="edit">服务编辑</el-breadcrumb-item>
                <el-breadcrumb-item v-else>添加服务</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="120px">
                <el-form-item prop="name">
                    <template slot='label'>
                        <span>服务名称</span>
                        <el-tooltip effect="dark" content="例如：正品保障|极速发货|7天退换货"
                                placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </template>
                    <el-input  maxlength="30"
                               show-word-limit v-model="ruleForm.name"></el-input>
                </el-form-item>
                <el-form-item  label="商品服务标识">
                    <el-row>
                        <el-col :span="4">
                            <app-attachment v-model="ruleForm.pic" :multiple="false" :max="1">
                                <el-tooltip class="item" effect="dark" content="建议尺寸:28 * 28"
                                            placement="top">
                                    <el-button size="mini">选择图片</el-button>
                                </el-tooltip>
                            </app-attachment>
                        </el-col>
                        <el-col :span="4">
                            <el-button size="mini" type="primary" @click="setPic">恢复默认</el-button>
                        </el-col>
                    </el-row>


                    <div class="customize-share-title">
                        <app-image mode="aspectFill" width='80px' height='80px'
                                   :src="ruleForm.pic ? ruleForm.pic : ''"></app-image>
                        <el-button v-if="ruleForm.pic" class="del-btn" size="mini"
                                   type="danger" icon="el-icon-close" circle
                                   @click="ruleForm.pic = ''"></el-button>
                    </div>
                    <el-button @click="app_share.dialog = true;app_share.type = 'pic_bg'"
                               type="text">查看图例
                    </el-button>
                </el-form-item>
                <el-form-item label="排序" prop="sort">
                    <el-input type="number" v-model="ruleForm.sort"></el-input>
                </el-form-item>
                <el-form-item label="备注" prop="remark">
                    <el-input :autosize="{ minRows: 6, maxRows: 6 }" type="textarea" v-model="ruleForm.remark"></el-input>
                </el-form-item>
                <el-form-item label="是否默认" prop="is_default">
                    <el-switch v-model="ruleForm.is_default" active-value="1" inactive-value="0"></el-switch>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
    </el-card>
    <!-- 自定义 -->
    <el-dialog title="查看商品服务标识图例"
               :visible.sync="app_share.dialog" width="30%">
        <div flex="dir:left main:center" class="app-share">
            <div class="app-share-bg"
                 :style="{backgroundImage: 'url('+service_pic_example+')'}"></div>
        </div>
        <div slot="footer" class="dialog-footer">
            <el-button @click="app_share.dialog = false" type="primary">我知道了</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    is_default: '0',
                    remark: '',
                    sort: 100,
                    pic: "<?= \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl?>/statics/img/mall/goods/guarantee/service-pic.png",
                },
                rules: {
                    name: [
                        {required: true, message: '请输入服务名称', trigger: 'change'},
                    ],
                    sort: [
                        {required: true, message: '请输入排序', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
                app_share:{
                    dialog: false
                },
                edit: false,
                service_pic: "<?= \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl?>/statics/img/mall/goods/guarantee/service-pic.png",
                service_pic_example: "<?= \Yii::$app->request->hostInfo .
                \Yii::$app->request->baseUrl?>/statics/img/mall/goods/guarantee/service-pic-example.png",
            };
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/service/edit'
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
                                    r: 'mall/service/index'
                                })
                            } else {
                                self.$message.error(e.data.msg);
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
            getDetail() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'mall/service/edit',
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code === 0) {
                        this.ruleForm = e.data.data.detail;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },
            setPic() {
                this.ruleForm.pic = this.service_pic;
            }
        },
        mounted: function () {
            if (getQuery('id')) {
                this.edit = true;
                this.getDetail();
            }
        }
    });
</script>
