<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>

<style>

</style>

<template id="app-edit-template">
    <div class="app-edit-template">
        <!-- 添加评价回复模板 -->
        <el-dialog title="添加模板" :visible.sync="dialogVisible" width="30%" @close="closeDialog">
            <el-form :model="ruleForm" ref="ruleForm" :rules="rules" size="small" label-width="80px">
                <el-form-item prop="title" label="模板名称">
                    <el-input type="text" v-model="ruleForm.title"></el-input>
                </el-form-item>
                <el-form-item prop="content" label="模板内容">
                    <el-input type="textarea" v-model="ruleForm.content" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item prop="type" label="模板类型">
                    <el-radio v-model="ruleForm.type" :label="1">好评回复</el-radio>
                    <el-radio v-model="ruleForm.type" :label="2">中评回复</el-radio>
                    <el-radio v-model="ruleForm.type" :label="3">差评回复</el-radio>
                </el-form-item>
                <el-form-item style="text-align: right">
                    <el-button size="small" @click="dialogVisible = false">取消</el-button>
                    <el-button size="small" type="primary" :loading="submitLoading" @click="toSumbit('ruleForm')">确定
                    </el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-edit-template', {
        template: '#app-edit-template',
        props: {
            isShow: {
                type: Boolean,
                default: false,
            },
            template: {
                type: Object,
                default: null
            },
            url: {
                type: String,
                default: 'mall/order-comment-templates/edit'
            }
        },
        watch: {
            isShow: function (newVal) {
                if (newVal) {
                    this.openDialog()
                }
            }
        },
        data() {
            return {
                dialogVisible: false,
                ruleForm: {
                    id: null,
                    title: '',
                    content: '',
                    type: 1,
                },
                submitLoading: false,
                rules: {
                    title: [
                        {required: true, message: '请输入模板名称', trigger: 'change'},
                    ],
                    content: [
                        {required: true, message: '请输入模板内容', trigger: 'change'},
                    ],
                    type: [
                        {required: true, message: '请选择模板类型', trigger: 'change'},
                    ],
                },
            }
        },
        methods: {
            // 打开备注
            openDialog(e) {
                if (this.template) {
                    this.ruleForm.id = this.template.id;
                    this.ruleForm.title = this.template.title;
                    this.ruleForm.content = this.template.content;
                    this.ruleForm.type = this.template.type;
                } else  {
                    this.ruleForm.id = null;
                    this.ruleForm.title = '';
                    this.ruleForm.content = '';
                    this.ruleForm.type = 1;
                }
                this.dialogVisible = true;
            },
            closeDialog() {
                this.$emit('close')
            },
            toSumbit(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.submitLoading = true;
                        request({
                            params: {
                                r: this.url
                            },
                            data: {
                                id: this.ruleForm.id,
                                title: this.ruleForm.title,
                                content:this.ruleForm.content,
                                type:this.ruleForm.type,
                            },
                            method: 'post'
                        }).then(e => {
                            this.submitLoading = false;
                            if (e.data.code === 0) {
                                this.dialogVisible = false;
                                this.$message.success(e.data.msg);
                                this.$emit('submit')
                            } else {
                                this.$message.error(e.data.msg);
                            }

                        }).catch(e => {
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            }
        }
    })
</script>