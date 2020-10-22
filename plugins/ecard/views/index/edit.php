<?php
/**
 * Created by PhpStorm.
 * User: 符俊涛
 * Date: 2020/3/11
 * Time: 11:41
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('app-rich-text');

?>
<style>
    .table-body {
        background-color: #fff;
        margin-bottom: 20px;
    }

    .card-name.el-input {
        width: 600px;
    }
    .poster-form-title {
        padding: 24px 25% 24px 32px;
        border-bottom: 1px solid #ebeef5;
    }
    .explanation {
        background-color: rgba(255, 255, 204, 1);
        padding: 20px 20px 26px 20px;
        margin-bottom: 15px;
    }
    .el-popover.el-popper {
        padding: 0;
    }
    .input .el-input__inner {
        border-color: #ff4544;
    }

</style>

<section id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="loading">
        <div slot="header">
            <div>
                <span style="cursor: pointer;color: #409eff;" @click="route">卡密列表</span>
                <span>/{{ecard_id ? '编辑卡密' : '新增卡密'}}</span>
            </div>
        </div>
        <el-form label-width="120px" :model="form" :rules="rules" ref="form" @submit.native.prevent>
            <div class="table-body">
                <div class="poster-form-title" style="margin-bottom: 24px;">基础设置</div>
                <div style="padding: 20px;">
                    <el-form-item label="卡密名称" prop="name">
                        <el-input class="card-name" @keyup.enter.native="submit" v-model="form.name" placeholder="最多输入10个字" maxlength="10"></el-input>
                    </el-form-item>
                    <el-form-item label="使用说明">
                        <div style="width: 600px; min-height: 458px;">
                            <app-rich-text v-model="form.content"></app-rich-text>
                        </div>
                    </el-form-item>
                </div>
            </div>
            <div class="table-body">
                <div class="poster-form-title" style="margin-bottom: 24px;">卡密数据结构
                <span style="font-size: 10px;color: #ff6666;">创建后不可更改</span>
                </div>
                <div style="padding:0 20px 20px 20px;">
                    <div class="explanation">
                        <p style="margin: 0;font-size: 21px;">什么是字段？</p>
                        <p style="font-size: 13px;margin: 0;">
                            若出售的电子卡密仅有一条数据组成,例如"激活码",则此处只需设置1条字段,显示名称为"激活码"即可,此后在此卡密库中录入激活码一种数据;<br/>
                            若出售的电子卡密由多条数据组成,例如"账号"密码""邮箱",则此处需要设置3条字段,显示名字逐一写入"账号""密码""邮箱",此后录入数据时,则需要由这3种数据共同组成一条虚拟卡密。
                        </p>
                    </div>
                    <el-table
                        border
                        :data="form.list"
                        max-height="240px"
                        style="width: 45%">
                        <el-table-column
                                width="150"
                            label="字段">
                            <template slot-scope="scope">
                                字段{{scope.$index + 1}}
                            </template>
                        </el-table-column>
                        <el-table-column
                            prop="key"
                            label="名称（限制10个字）">
                            <template slot-scope="scope">
                                <span v-if="ecard_id">{{scope.row.key}}</span>
                                <el-input v-else :class="scope.row.dis ? 'input' : ''" type="text" @change="change_ladder" maxlength="10" v-model="scope.row.key"></el-input>
                            </template>
                        </el-table-column>
                        <el-table-column
                            v-if="!ecard_id"
                            width="100"
                            label="操作">
                            <template slot-scope="scope">
                                <el-button  type="text"  circle size="mini" @click="deleteItem(scope.$index)">
                                    <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                        <img src="statics/img/mall/del.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                    <el-button type="primary" v-if="!ecard_id" size="small" @click="addField" style="margin-top: 20px;" :disabled="!field_bool">
                        <i class="el-icon-plus" style="font-weight: bolder;margin-left: 5px;"></i>
                        <span style="font-size: 14px">新增一个字段</span>
                    </el-button>
                </div>
            </div>
            <el-button @click="save('form')" type="primary" size="small" :disabled="!field_bool">保存</el-button>
        </el-form>
    </el-card>
</section>

<script>
    const app = new Vue({
        el: '#app',

        data() {
            return {
                form: {
                    name: '',
                    content: '',
                    is_unique: 1,
                    list: []
                },
                loading: false,
                rules: {
                    name: [
                        { required: true, message: '请输入卡密名称', trigger: 'blur' },
                        { min: 1, max: 10, message: '长度在 1 到 10 个字符', trigger: 'blur' }
                    ],
                },
                field_bool: true,
                ecard_id: 0,
            }
        },

        methods: {

            addField() {
                if (!this.field_bool) return;
                this.form.list = this.form.list ? this.form.list : [];
                this.form.list.push({
                    key: '',
                    dis: false,
                });
                setTimeout(() => {
                    let scrollDom = document.getElementsByClassName('el-table__body-wrapper')[0];
                    scrollDom.scrollTop = scrollDom.scrollHeight;
                }, 100);
            },

            async save(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        let { name, content, is_unique, list }  = this.form;
                        if (list.length === 0) {
                            this.$message({
                                message: '请至少添加一个字段',
                                type: 'warning'
                            });
                            return;
                        }
                        this.loading = true;
                        request({
                            params: {
                                r: 'plugin/ecard/mall/index/edit'
                            },
                            method: 'post',
                            data: {
                                name,
                                content,
                                is_unique,
                                list: JSON.stringify(list),
                                id: this.ecard_id
                            }
                        }).then(e => {
                            this.loading = false;
                            if (e.data.code === 0) {
                                this.$message({
                                    type: 'success',
                                    message: e.data.msg
                                });
                                this.$navigate({
                                    r: 'plugin/ecard/mall/index/index'
                                });
                            } else {
                                this.$message({
                                    type: 'warning',
                                    message: e.data.msg
                                });
                            }
                        }).catch(() => {
                            this.loading = false;
                        })
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },

            deleteItem(index) {
                let obj = {};
                let that = this;
                this.$delete(this.form.list, index);
                that.field_bool = true;
                this.form.list = this.form.list.reduce((item, next) => {
                    if (!obj[next.key]) {
                        obj[next.key] = true;
                        next.dis = false;
                        item.push(next);
                    } else {
                        next.dis = true;
                        that.field_bool = false;
                        item.push(next);
                    }
                    return item;
                }, []);
            },

            change_ladder() {
                let obj = {};
                let that = this;
                that.field_bool = true;
                this.form.list = this.form.list.reduce((item, next) => {
                    if (!obj[next.key]) {
                        obj[next.key] = true;
                        next.dis = false;
                        item.push(next);
                    } else {
                        next.dis = true;
                        that.field_bool = false;
                        item.push(next);
                        if (next.key !== '') {
                            const h = that.$createElement;
                            that.$msgbox({
                                title: '重复数据提示',
                                message: h('p', null, [
                                    h('span', null, '新增的'),
                                    h('span', { style: 'color: #409eff;font-weight: bold' }, next.key),
                                    h('span', null, '是重复数据，请修改'),
                                ]),
                                showCancelButton: true,
                                confirmButtonText: '确定',
                                cancelButtonText: '取消',
                                type: 'warning',
                                beforeClose: (action, instance, done) => {
                                    if (action === 'confirm') {
                                        done();
                                    } else {
                                        done();
                                    }
                                }
                            }).then(() => {
                            }).catch(() => {
                            })
                        }
                    }
                    return item;
                }, []);
            },

            async getInform() {
                const e = await request({
                    params: {
                        r: '/plugin/ecard/mall/index/edit',
                        id: this.ecard_id
                    }
                });
                if (e.data.code === 0 ) {
                    this.form = e.data.data;
                }
            },

            submit() {
            },
            route() {
                this.$navigate({
                    r: `plugin/ecard/mall/index/index`,
                });
            }
        },
        mounted: function () {
            this.ecard_id = getQuery('id');
            if (this.ecard_id) {
                this.getInform();
            }
        }
    });
</script>
