<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/16 12:05
 */
?>
<div id="app" v-cloak>
    <el-card v-loading="loading" shadow="hover">
        <div slot="header">表单名称</div>
        <el-form :model="form" :rules="rules" ref="form">
            <!-- 基本输入框 -->
            <el-form-item label="名称" prop="name">
                <el-input v-model="form.name"></el-input>
            </el-form-item>

            <!-- 单张图片选择 -->
            <el-form-item label="单张图片" prop="single_pic">
                <app-attachment @selected="singleSelected">
                    <el-button type="text" icon="el-icon-picture-outline">选择图片</el-button>
                </app-attachment>
                <app-gallery v-if="form.single_pic" :list="[form.single_pic]"></app-gallery>
            </el-form-item>

            <!-- 多张图片选择 -->
            <el-form-item label="多张图片" prop="multiple_pic">
                <app-attachment :multiple="true" :max="2" @selected="multipleSelected">
                    <el-button type="text" icon="el-icon-picture-outline">选择图片</el-button>
                </app-attachment>
                <app-gallery v-if="form.multiple_pic" :show-delete="true" @deleted="picDeleted"
                             :list="form.multiple_pic"></app-gallery>
            </el-form-item>

            <!-- 嵌套Dialog -->
            <el-form-item label="嵌套Dialog" prop="name">
                <el-button @click="dialogInDialogVisible = true">嵌套Dialog</el-button>
            </el-form-item>

            <!-- 视频选择 -->
            <el-form-item label="嵌套Dialog" prop="name">
                <app-attachment v-model="form.video_url" type="video">
                    <el-button type="text" icon="el-icon-picture-outline">选择视频</el-button>
                </app-attachment>
                <video style="border: 1px solid #ccc" controls height="240">
                    <source v-if="form.video_url && form.video_url != ''" :src="form.video_url" type="video/mp4">
                </video>
            </el-form-item>


            <!-- 简化版 -->
            <el-form-item label="简化版图片上传" prop="single_pic">
                <app-attachment @selected="singleSelected" :simple="true" v-model="form.simple_pic">
                    <el-button type="text" icon="el-icon-picture-outline">选择图片</el-button>
                </app-attachment>
                <img :src="form.simple_pic" style="width: 100px;height: 100px;border: 1px solid #e2e2e2">
            </el-form-item>

            <!-- 提交表单 -->
            <el-form-item>
                <el-button type="primary" @click="submit('form')" :loading="submitLoading">提交</el-button>
            </el-form-item>
        </el-form>
    </el-card>

    <el-dialog
            title="嵌套Dialog"
            :visible.sync="dialogInDialogVisible"
            width="30%">
        <div>
            <app-attachment>
                <el-button type="text" icon="el-icon-picture-outline">Dialog IN Dialog</el-button>
            </app-attachment>
        </div>
    </el-dialog>

</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                submitLoading: false,
                dialogInDialogVisible: false,
                form: {
                    name: '',
                    single_pic: null,
                    multiple_pic: [],
                    video_url: '',
                    simple_pic: '',
                },
                rules: {
                    name: [
                        {required: true, message: '请填写名称。'}
                    ]
                }
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'demo/form',
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.form = e.data.data;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            singleSelected(list) {
                this.form.single_pic = list.length ? list[0] : null;
            },
            multipleSelected(list) {
                this.form.multiple_pic = this.form.multiple_pic.concat(list);
            },
            picDeleted(item, index) {
                this.form.multiple_pic.splice(index, 1);
            },
            submit(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.submitLoading = true;
                        request({
                            params: {
                                r: 'demo/form',
                            },
                            method: 'post',
                            data: this.form,
                        }).then(e => {
                            this.submitLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                        });
                    } else {

                    }
                });
            },
        }
    });
</script>