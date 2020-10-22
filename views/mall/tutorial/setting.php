<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
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
    <el-card shadow="never" style="border:0" v-loading="tutorialLoading" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>教程设置</span>
            </div>
        </div>
        <div class="form-body">
            <el-form @submit.native.prevent :model="form" :rules="rules" size="small" ref="form" label-width="120px">
                <el-form-item label="是否开启文档" prop="status">
                    <el-switch v-model="form.status" active-value="1" inactive-value="0"></el-switch>
                </el-form-item>
                <el-form-item label="文档链接" prop="url">
                    <el-input v-model="form.url"></el-input>
                    <div>请加上http://或者https://</div>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store" size="small">保存</el-button>
    </el-card>
</div>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            form: {},
            rules: {
                status: [
                    { required: true, message: '是否开启不能为空', trigger: 'blur' },
                ],
            },
            btnLoading: false,
            tutorialLoading: false,
        };
    },
    methods: {
        store() {
            let self = this;
            self.$refs.form.validate((valid) => {
                if (valid) {
                    self.btnLoading = true;
                    let para = Object.assign({}, this.form);
                    request({
                        params: {
                            r: 'mall/tutorial/setting'
                        },
                        method: 'post',
                        data: para,
                    }).then(e => {
                        self.btnLoading = false;
                        if (e.data.code == 0) {
                            self.$message.success(e.data.msg);
                            navigateTo({
                                r: 'mall/tutorial/setting'
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
        getList() {
            let self = this;
            self.tutorialLoading = true;
            request({
                params: {
                    r: 'mall/tutorial/setting',
                },
            }).then(e => {
                self.tutorialLoading = false;
                if (e.data.code == 0) {
                    self.form = e.data.data;
                    self.$message.success(e.data.msg);
                } else {
                    self.$message.error(e.data.msg);
                }
            }).catch(e => {
                console.log(e);
            });
        },
    },
    mounted: function() {
        this.getList();
    }
});
</script>