<?php defined('YII_ENV') or exit('Access Denied');
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author xay
 * @link http://www.zjhejiang.com/
 */
?>
<section id="app" v-cloak>
    <el-card class="box-card">
        <div slot="header" class="clearfix">
            <span>轮播图</span>
        </div>
        <div class="text item">
            <el-form :model="form" label-width="100px" :rules="FormRules" ref="form">
                <el-form-item label="标题" prop="title">
                    <el-input v-model="form.title" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="跳转链接" prop="page_url">
                    <el-input v-model="form.page_url" class="input-with-select" autocomplete="off">
                        <app-pick-link slot="append" @selected="selectAdvertUrl">
                            <el-button size="mini">选择链接</el-button>
                        </app-pick-link>
                    </el-input>
                </el-form-item>
                <el-form-item label="排序" prop="sort">
                    <el-input v-model="form.sort" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="图片" prop="pic_url">
                    <app-attachment @selected="singleSelected">
                        <el-button type="text" icon="el-icon-picture-outline">选择图片</el-button>
                    </app-attachment>
                    <app-gallery v-if="form.pic_url" :list="[form.picUrl]"></app-gallery>
                </el-form-item>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="onSubmit">提交</el-button>
                    <el-button onclick="javascript:history.back(-1)">返回</el-button>
                </el-form-item>
            </el-form>
        </div>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {},
                loading: false,
                FormRules: {
                    title: [
                        {min: 0, max: 30, message: "标题长度在0-30个字符内"},
                    ],
                    sort: [
                        {required: false, pattern: /^[0-9]\d{0,8}$/, message: '排序必须在9位整数内'},
                    ],
                },
            };
        },
        methods: {
            selectAdvertUrl(e) {
                let self = this;
                e.forEach(function (item, index) {
                    self.form.page_url = item.new_link_url;
                    self.form.open_type = item.open_type;
                })
            },

            singleSelected(list) {
                this.form.picUrl = list[0];
            },
            //提交
            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({type: getQuery('type')}, this.form);
                        request({
                            params: {
                                r: 'mall/banner/edit'
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            if (e.data.code === 0) {
                                navigateTo({r: 'mall/banner/index', type: getQuery('type')});
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                        });
                    }
                });
            },

            //列表
            getList() {
                request({
                    params: {
                        r: 'mall/banner/edit',
                        id: getQuery('id'),
                        type: getQuery('type'),
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.form = e.data.data;
                    }
                }).catch(e => {
                });
            },
        },
        created() {
            this.getList();
        }
    })
</script>