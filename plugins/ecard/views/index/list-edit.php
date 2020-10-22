<?php
/**
 * Created by PhpStorm.
 * User: fjt
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
        width: 350px;
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

    .name {
        color: #409eff;
        font-size: 16px;
    }
    .route .el-breadcrumb__inner {
        color: #409eff;
        cursor: pointer;
    }
</style>

<section id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="loading">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item class="route" @click.native="route('plugin/ecard/mall/index/index')">卡密列表</el-breadcrumb-item>
                <el-breadcrumb-item class="route"  @click.native="route('plugin/ecard/mall/index/list', {id: ecard_id})">卡密管理</el-breadcrumb-item>
                <el-breadcrumb-item>添加卡密数据</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <el-form label-width="120px" :model="form" :rules="rules" ref="form">
            <div class="table-body">
                <div class="poster-form-title" style="margin-bottom: 24px;">
                    向
                    <span class="name">{{form.ecard.name}}</span>
                    添加卡密数据
                    <span style="color: #999999;">（限制50个字）</span>
                </div>
                <div style="padding: 20px;">
                    <el-table
                        :data="form.list"
                        border
                        v-loading="tableLoading"
                    >
                        <el-table-column
                            v-for="(item, index) in form.ecard.list"
                            :key="index"
                            :prop="'key' + (index + 1)"
                            :label="item.key"
                        >
                            <template slot-scope="scope">
                                <el-input maxlength="50" @focus="input(scope.row)" :class="scope.row.is_repeat ? 'input' : ''" v-model="scope.row['key' + (index + 1)]"></el-input>
                                <span v-if="scope.row.is_repeat" style="color: #ff4544;font-size: 10px;">重复数据，请修改</span>
                            </template>
                        </el-table-column>
                        <el-table-column
                            prop="address"
                            fixed="right"
                            width="240"
                            label="操作">
                            <template slot-scope="scope">
                                <el-button type="text" circle size="mini" @click="deleteItem(scope.$index)">
                                    <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                        <img src="statics/img/mall/del.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                   <div flex="main:justify">
                       <el-button type="primary" size="small" @click="addField" style="margin-top: 20px;" >
                           <i class="el-icon-plus" style="font-weight: bolder;margin-left: 5px;"></i>
                           <span style="font-size: 14px">新增一条数据</span>
                       </el-button>
                   </div>
                </div>
            </div>
            <el-button @click="save" type="primary" size="small" >保存</el-button>
        </el-form>
    </el-card>
</section>

<script>
    const app = new Vue({
        el: '#app',

        data() {
            return {
                form: {
                    list: [],
                    ecard: {
                        list: []
                    }
                },
                loading: false,
                rules: {
                },
                field_bool: true,
                ecard_id: 0,
                tableLoading: false
            }
        },

        methods: {

            addField() {
                if (!this.field_bool) return;
                this.form.list = this.form.list ? this.form.list : [];
                let obj = {
                    disabled: false
                };
                for (let i = 0; i < this.form.ecard.list.length; i++) {
                    obj['key' + (i+1)] = '';
                }
                this.form.list.push(obj);
            },

            async save() {
                this.loading = true;
                let {list}  = this.form;
                const e = await request({
                    params: {
                        r: 'plugin/ecard/mall/index/list-edit'
                    },
                    method: 'post',
                    data: {
                        list: JSON.stringify(list),
                        ecard_id: this.ecard_id
                    }
                });

                if (e.data.code === 0) {
                    this.$message({
                        type: 'success',
                        message: e.data.msg
                    });
                    this.$historyGo(-1);
                    this.loading = false;
                    this.form.list = [];
                } else {
                    this.loading = false;
                    this.form.list = e.data.data.data;
                    this.$message({
                        type: 'warning',
                        message: e.data.msg
                    });
                }

            },

            deleteItem(index) {
                this.$delete(this.form.list, index);
            },

            async getInform() {
                this.loading = true;
                const e = await request({
                    params: {
                        r: 'plugin/ecard/mall/index/list',
                        ecard_id: this.ecard_id
                    }
                });
                if (e.data.code === 0 ) {
                    let {  ecard,  } = e.data.data;
                    this.form.list = [];
                    this.form.ecard = ecard;
                }
                this.loading = false;
            },
            route(r, data) {

                if (data) {
                    this.$navigate({
                        r,
                        id: data.id
                    });
                } else {
                    this.$navigate({
                        r
                    });
                }
            },

            input(data) {
                data.is_repeat = false;
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
