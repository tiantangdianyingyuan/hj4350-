<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/6/17
 * Time: 10:11
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }


</style>
<div id="app">
    <el-card class="box-card" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>商品分类转移</span>
        </div>
        <div class="table-body">

        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {},
                btnLoading: false,
                selectCat: '',
                list: {
                    before: {
                        keyword: '',
                        selectCat: 0,
                        list: [],
                        pagination: null,
                        loading: false,
                        parent: null,
                        grandParent: null,
                    },
                    middle: {

                    },
                    after: {
                        keyword: '',
                        selectCat: 0,
                        list: [],
                        pagination: null,
                        loading: false,
                        parent: null,
                        grandParent: null,
                    },
                },
                url: 'mall/cat/transfer-cat',
            };
        },
        created() {
            this.getCatList({
                r: this.url,
                id: 0
            });
        },
        methods: {
            getCatList(params) {
                if (this.selectCat == '') {
                    for (let i in this.list) {
                        this.list[i].loading = true;
                        this.list[i].list = [];
                    }
                } else {
                    this.list[this.selectCat].loading = true;
                    this.list[this.selectCat].list = [];
                }
                request({
                    params: params,
                    method: 'get',
                }).then(response => {
                    if (this.selectCat == '') {
                        for (let i in this.list) {
                            this.list[i].loading = false;
                        }
                    } else {
                        this.list[this.selectCat].loading = false;
                    }
                    if (response.data.code == 0) {
                        if (this.selectCat == '') {
                            for (let i in this.list) {
                                this.list[i].list = response.data.data.list;
                                this.list[i].pagination = response.data.data.pagination;
                                if (typeof params.keyword === 'undefined') {
                                    this.list[i].parent = response.data.data.parent;
                                    this.list[i].grandParent = response.data.data.grandParent;
                                }
                            }
                        } else {
                            this.list[this.selectCat].list = response.data.data.list;
                            this.list[this.selectCat].pagination = response.data.data.pagination;
                            if (typeof params.keyword === 'undefined') {
                                this.list[this.selectCat].parent = response.data.data.parent;
                                this.list[this.selectCat].grandParent = response.data.data.grandParent;
                            }
                        }
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(response => {
                    console.log(response);
                });
            },
            submit() {
                this.btnLoading = true;
                request({
                    params: {
                        r: 'mall/goods/transfer',
                    },
                    method: 'post',
                    data: {
                        before: this.list.before.selectCat,
                        after: this.list.after.selectCat,
                    }
                }).then(response => {
                    this.btnLoading = false;
                    if (response.data.code == 0) {
                        this.$message.success(response.data.msg);
                    } else {
                        this.$message.error(response.data.msg);
                    }
                });
            },
            search(param) {
                let id = this.list[param].grandParent ?
                    this.list[param].grandParent.id :
                    (this.list[param].parent ? this.list[param].parent.id : 0);
                this.list[param].selectCat = 0;
                this.selectCat = param;
                this.getCatList({
                    r: this.url,
                    id: id,
                    keyword: this.list[param].keyword
                });
            },
            next(id, param) {
                this.selectCat = param;
                this.list[param].keyword = '';
                this.list[param].selectCat = 0;
                this.getCatList({
                    r: this.url,
                    id: id
                });
            },
            middleStyle() {
                if (this.list.before.selectCat > 0 && this.list.after.selectCat > 0) {
                    return `background-color: #409EFF;color: #fff`;
                } else {
                    return `background-color: #f7f5fa;color: rgba(0, 0, 0, 0.5);`;
                }
            }
        }
    });
</script>
