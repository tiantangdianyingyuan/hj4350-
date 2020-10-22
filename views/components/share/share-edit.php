<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/3
 * Time: 15:24
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<template id="share-edit">
    <el-dialog :visible.sync="edit.visible" width="20%" title="添加分销商">
        <div>
            <el-form @submit.native.prevent size="small" label-width="150px">
                <el-form-item label="用户昵称">
                    <el-autocomplete size="small" v-model="edit.nickname" value-key="nickname" @keyup.enter.native="keyUp"
                                     :fetch-suggestions="querySearchAsync" placeholder="请输入用户昵称"
                                     @select="shareClick"></el-autocomplete>
                </el-form-item>
            </el-form>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="editCancel" type="default" size="small">取消</el-button>
            <el-button type="primary" :loading="edit.btnLoading" style="margin-bottom: 10px;" size="small" @click="editSave">保存</el-button>
        </span>
    </el-dialog>
</template>
<script>
    Vue.component('share-edit', {
        template: '#share-edit',
        props: {
            value: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                edit: {
                    visible: false,
                    keyword: '',
                    nickname: '',
                    id: '',
                    btnLoading: false,
                }
            };
        },
        watch: {
            value() {
                if (this.value) {
                    this.edit.visible = true;
                } else {
                    this.edit.id = '';
                    this.edit.nickname = '';
                    this.edit.keyword = '';
                    this.edit.visible = false;
                }
            },
            'edit.visible'() {
                if (!this.edit.visible) {
                    this.editCancel();
                }
            }
        },
        methods: {
            querySearchAsync(queryString, cb) {
                this.edit.keyword = queryString;
                this.get_user(cb);
            },
            get_user(cb) {
                request({
                    params: {
                        r: 'mall/share/user',
                        keyword: this.edit.keyword
                    }
                }).then(response => {
                    if (response.data.code == 0) {
                        cb(response.data.data.list)
                    } else {
                        this.$message.error(response.data.msg);
                    }
                });
            },
            shareClick(row) {
                this.edit.id = row.id
            },
            editCancel() {
                this.$emit('input', false);
            },
            editSave() {
                this.edit.btnLoading = true;
                request({
                    params: {
                        r: 'mall/share/edit',
                    },
                    method: 'post',
                    data: {
                        id: this.edit.id,
                    }
                }).then(response => {
                    this.edit.btnLoading = false;
                    if (response.data.code == 0) {
                        this.$message.success('添加成功');
                        this.editCancel();
                        this.$emit('save');
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(response => {
                    this.edit.btnLoading = false;
                });
            },
            keyUp() {
                console.log('key up')
            }
        }
    });
</script>
