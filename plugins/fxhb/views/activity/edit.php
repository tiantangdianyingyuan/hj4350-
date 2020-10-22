<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>
<style>
    .form-body {
        margin-bottom: 20px;
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
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'plugin/fxhb/mall/activity/index'})">红包活动列表</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>活动编辑</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form v-loading="loading" :model="ruleForm" :rules="rules" ref="ruleForm" label-width="160px"
                     size="small">
                <el-row>
                    <el-card shadow="never">
                        <div slot="header">
                            <div>
                                <span>基础设置</span>
                            </div>
                        </div>
                        <el-col :span="12">
                            <el-form-item label="活动状态" prop="type">
                                <el-switch v-model="ruleForm.status" active-value="1" inactive-value="0">
                                </el-switch>
                            </el-form-item>
                            <el-form-item label="活动名称" prop="name">
                                <el-input type="text" v-model="ruleForm.name"></el-input>
                            </el-form-item>
                            <el-form-item label="活动时间" prop="start_end_time">
                                <el-date-picker
                                        v-model="ruleForm.start_end_time"
                                        type="daterange"
                                        value-format="yyyy-MM-dd"
                                        range-separator="至"
                                        start-placeholder="开始日期"
                                        end-placeholder="结束日期">
                                </el-date-picker>
                            </el-form-item>
                            <el-form-item label="首页弹框状态" prop="type">
                                <el-switch v-model="ruleForm.is_home_model" active-value="1" inactive-value="0">
                                </el-switch>
                            </el-form-item>
                            <el-form-item label="活动首页弹框图片" prop="pic_url">
                                <app-attachment :multiple="false" :max="1" v-model="ruleForm.pic_url">
                                    <el-tooltip class="item"
                                                effect="dark"
                                                content="建议尺寸:650 * 700"
                                                placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                </app-attachment>
                                <app-image mode="aspectFill" width='80px' height='80px' :src="ruleForm.pic_url">
                                </app-image>
                            </el-form-item>
                            <el-form-item label="活动分享标题" prop="share_title">
                                <el-input style="width: 220px"
                                          v-model="ruleForm.share_title"
                                          placeholder="请输入标题">
                                </el-input>
                            </el-form-item>
                            <el-form-item label="活动分享图片" prop="share_pic_url">
                                <app-attachment :multiple="false" :max="1" v-model="ruleForm.share_pic_url">
                                    <el-tooltip class="item"
                                                effect="dark"
                                                content="建议尺寸:420 * 336"
                                                placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                </app-attachment>
                                <app-image mode="aspectFill" width='80px' height='80px' :src="ruleForm.share_pic_url">
                                </app-image>
                            </el-form-item>
                            <el-form-item label="活动规则" prop="remark">
                                <el-input type="textarea" v-model="ruleForm.remark"></el-input>
                            </el-form-item>
                            <el-form-item label="红包分配方式" prop="type">
                                <el-radio v-model="ruleForm.type" label="1">随机</el-radio>
                                <el-radio v-model="ruleForm.type" label="2">平均</el-radio>
                            </el-form-item>
                            <el-form-item label="用户可发起活动次数" prop="sponsor_num">
                                <div flex="dir:left">
                                    <el-input :disabled="isSponsorNum"
                                              type="number"
                                              v-model.number="ruleForm.sponsor_num">
                                    </el-input>
                                    <el-checkbox style="margin-left: 20px" @change="itemChecked(1)"
                                                 v-model="isSponsorNum">无限制
                                    </el-checkbox>
                                </div>
                            </el-form-item>
                            <el-form-item label="用户可帮拆次数" prop="help_num">
                                <div flex="dir:left">
                                    <el-input :disabled="isHelpNum"
                                              type="number"
                                              v-model.number="ruleForm.help_num">
                                    </el-input>
                                    <el-checkbox style="margin-left: 20px" @change="itemChecked(2)" v-model="isHelpNum">
                                        无限制
                                    </el-checkbox>
                                </div>
                            </el-form-item>
                            <el-form-item label="活动可发红包总次数" prop="sponsor_count">
                                <div flex="dir:left">
                                    <el-input :disabled="isSponsorCount"
                                              type="number"
                                              v-model.number="ruleForm.sponsor_count">
                                    </el-input>
                                    <el-checkbox style="margin-left: 20px" @change="itemChecked(3)"
                                                 v-model="isSponsorCount">无限制
                                    </el-checkbox>
                                </div>
                            </el-form-item>
                        </el-col>
                    </el-card>
                    <el-card shadow="never" style="margin-top: 10px;">
                        <div slot="header">
                            <div>
                                <span>规则设置</span>
                            </div>
                        </div>
                        <el-col :span="12">
                            <el-form-item label="活动次数扣除方式" prop="sponsor_count_type">
                                <el-radio v-model="ruleForm.sponsor_count_type" label="0">活动成功扣除</el-radio>
                                <el-radio v-model="ruleForm.sponsor_count_type" label="1">活动发起扣除</el-radio>
                            </el-form-item>
                            <el-form-item label="拆包人数" prop="number">
                                <el-input type="number" v-model.number="ruleForm.number"></el-input>
                            </el-form-item>
                            <el-form-item label="红包总金额" prop="count_price">
                                <el-input type="number" v-model.number="ruleForm.count_price"></el-input>
                            </el-form-item>
                            <el-form-item label="代金券最低消费金额" prop="least_price">
                                <el-input type="number" v-model.number="ruleForm.least_price"></el-input>
                            </el-form-item>
                            <el-form-item label="代金券有效天数" prop="effective_time">
                                <el-input type="number" v-model.number="ruleForm.effective_time">
                                    <template slot="append">天</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="拆红包有效时间" prop="open_effective_time">
                                <el-input type="number" v-model.number="ruleForm.open_effective_time">
                                    <template slot="append">时</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="指定商品类别或商品" prop="coupon_type">
                                <el-radio v-model="ruleForm.coupon_type" @click.native="catsListVisible = true"
                                          label="1">
                                    指定商品类别
                                </el-radio>
                                <el-radio v-model="ruleForm.coupon_type" @click.native="goodsListVisible = true"
                                          label="2">
                                    指定商品
                                </el-radio>
                                <el-radio v-model="ruleForm.coupon_type" label="3">全场通用</el-radio>
                                <el-radio v-if="isScanCodePayShow" v-model="ruleForm.coupon_type" label="4">当面付
                                </el-radio>
                            </el-form-item>
                        </el-col>
                    </el-card>
                </el-row>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存
        </el-button>
        <!--指定商品-->
        <el-dialog title="已指定商品" :visible.sync="goodsListVisible">
            <el-dialog title="添加商品" :visible.sync="goodsAddVisible" append-to-body>
                <el-form>
                    <el-form-item>
                        <el-input size="small" v-model="keyword" autocomplete="off" placeholder="商品名称"
                                  style="width: 40%"></el-input>
                        <el-button :loading="dialogBtnLoading" size="small" @click="pageChange(1)">
                            查找商品
                        </el-button>
                        <el-button size="small" style="float:right" @click="goodsBatchAdd">批量添加</el-button>
                    </el-form-item>
                </el-form>
                <!--待添加的商品 -->
                <el-table v-loading="dialogLoading" :data="goods_list" @selection-change="selsChange"
                          style="width: 100%">
                    <el-table-column :selectable="selection" type="selection" width="55"></el-table-column>
                    <el-table-column prop="id" label="ID" width="80"></el-table-column>
                    <el-table-column prop="cover_pic" label="商品图" width="180">
                        <template slot-scope="scope">
                            <app-image mode="aspectFill" :src="scope.row.cover_pic"></app-image>
                        </template>
                    </el-table-column>
                    <el-table-column prop="name" label="商品名称">
                        <template slot-scope="scope">
                            <app-ellipsis :line="2">{{scope.row.name}}</app-ellipsis>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-button v-if="ruleForm.goods_id_list.indexOf(scope.row.id) == -1"
                                       plain size="mini"
                                       type="primary" @click="goodsAdd(scope.$index, scope.row.id)">添加
                            </el-button>
                            <el-button v-else plain size="mini" type="primary" disabled>已添加</el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div slot="footer" class="dialog-footer">
                    <el-pagination
                            v-if="pagination"
                            background
                            @current-change="pageChange"
                            layout="prev, pager, next, jumper"
                            :total="pagination.total_count">
                    </el-pagination>
                </div>
            </el-dialog>
            <el-button style="float: right" type="primary" size="small" @click="addGoods">新增</el-button>
            <!--已添加商品 -->
            <el-table :data="select_goods_list" style="width: 100%">
                <el-table-column prop="id" label="ID" width="80"></el-table-column>
                <el-table-column prop="cover_pic" label="商品图" width="80">
                    <template slot-scope="scope">
                        <app-image mode="aspectFill" :src="scope.row.cover_pic"></app-image>
                    </template>
                </el-table-column>
                <el-table-column prop="name" label="商品名称" width="180">
                    <template slot-scope="scope">
                        <app-ellipsis :line="2">{{scope.row.name}}</app-ellipsis>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="220">
                    <template slot-scope="scope">
                        <el-button plain size="mini" type="danger" @click="goodsDestroy(scope.$index, scope.row.id)">
                            删除
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
        </el-dialog>
        <!--指定商品分类-->
        <el-dialog title="已指定商品类别" :visible.sync="catsListVisible" width="35%">
            <el-tree
                    style="margin-left:30px"
                    :indent="40"
                    :data="cats"
                    show-checkbox
                    default-expand-all
                    :default-checked-keys="ruleForm.cat_id_list"
                    node-key="id"
                    ref="tree"
                    highlight-current
                    :props="defaultProps"
            ></el-tree>
            <div slot="footer" class="dialog-footer">
                <el-button plain type="submit" @click="catsListVisible = false">取消</el-button>
                <el-button plain type="primary" @click="catsAdd">确定</el-button>
            </div>
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                //添加商品
                pagination: null,
                goodsListVisible: false,
                goodsAddVisible: false,
                goodsBatchList: [],
                //添加分类
                catsListVisible: false,
                cats: [],
                defaultProps: {
                    children: 'child',
                    label: 'name'
                },
                cats_list: [],
                goods_list: [],
                select_goods_list: [],
                cat: [],
                keyword: '',
                shareTitle: '',

                ruleForm: {
                    cat_id_list: [],
                    goods_id_list: [],
                    status: '0',
                    type: '1',
                    number: 4,
                    effective_time: 30,
                    open_effective_time: 24,
                    coupon_type: '3',
                    sponsor_num: -1,
                    help_num: -1,
                    sponsor_count: -1,
                    sponsor_count_type: '0',
                    share_title: '',
                    pic_url: '',
                    start_end_time: [],
                    name: ''
                },
                rules: {
                    type: [
                        {required: true, message: '请选择红包分配方式', trigger: 'change'},
                    ],
                    number: [
                        {required: true, message: '请填写拆包人数', trigger: 'change'},
                    ],
                    count_price: [
                        {required: true, message: '请填写红包总金额', trigger: 'change'},
                    ],
                    least_price: [
                        {required: true, message: '请填写代金券最低消费金额', trigger: 'change'},
                    ],
                    effective_time: [
                        {required: true, message: '请填写代金券有效期', trigger: 'change'},
                    ],
                    open_effective_time: [
                        {required: true, message: '请填写拆包活动有效期', trigger: 'change'},
                    ],
                    coupon_type: [
                        {required: true, message: '请选择代金券使用场景', trigger: 'change'},
                    ],
                    sponsor_num: [
                        {required: true, message: '请填写用户可必起的活动次数', trigger: 'change'},
                    ],
                    help_num: [
                        {required: true, message: '请填写用户可帮拆次数', trigger: 'change'},
                    ],
                    sponsor_count: [
                        {required: true, message: '请填写活动发红包总次数', trigger: 'change'},
                    ],
                    sponsor_count_type: [
                        {required: true, message: '请选择活动次数扣除方式', trigger: 'change'},
                    ],
                    start_end_time: [
                        {required: true, message: '请选择活动时间', trigger: 'change'},
                    ],
                    remark: [
                        {required: true, message: '请填写活动规则', trigger: 'change'},
                    ],
                    pic_url: [
                        {required: true, message: '请选择活动图片', trigger: 'change'},
                    ],
                    share_title: [
                        {required: true, message: '请添加活动分享标题', trigger: 'change'},
                    ],
                    share_pic_url: [
                        {required: true, message: '请选择首页活动封面图片', trigger: 'change'},
                    ],
                    name: [
                        {required: true, message: '请输入活动名称', trigger: 'change'},
                    ],
                },
                loading: false,
                btnLoading: false,
                dialogLoading: false,
                dialogBtnLoading: false,
                isSponsorNum: true,
                isHelpNum: true,
                isSponsorCount: true,
                isScanCodePayShow: false,
            };
        },
        methods: {
            getDetail() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/fxhb/mall/activity/edit',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        let data = e.data.data.detail;
                        data.start_end_time = [e.data.data.detail.start_time, e.data.data.detail.end_time];
                        this.ruleForm = data;
                        this.select_goods_list = e.data.data.detail.goods;

                        this.isSponsorNum = this.ruleForm.sponsor_num == -1;
                        this.isHelpNum = this.ruleForm.help_num == -1;
                        this.isSponsorCount = this.ruleForm.sponsor_count == -1;
                    }
                }).catch(e => {
                });
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/fxhb/mall/activity/edit'
                            },
                            method: 'post',
                            data: {
                                form: JSON.stringify(self.ruleForm),
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'plugin/fxhb/mall/activity/index'
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
            //分页
            pageChange(page) {
                this.dialogLoading = true;
                this.dialogBtnLoading = true;
                this.page = page;
                this.search('plugin/fxhb/mall/activity/goods', 'goods', page);
            },

            addGoods() {
                this.goodsAddVisible = true;
                this.pageChange(1);
            },

            catsAdd() {
                let cat_id_list = this.$refs.tree.getCheckedKeys();
                this.ruleForm.cat_id_list = cat_id_list;
                this.$message({
                    message: '添加成功',
                    type: 'success'
                });

                this.catsListVisible = false;
            },
            //指定商品
            goodsDestroy(index, row) {
                this.ruleForm.goods_id_list.splice(index, 1);
                this.select_goods_list.splice(index, 1);
            },
            selsChange(row) {
                this.goodsBatchList = row;
            },
            goodsBatchAdd() {
                this.goodsBatchList.forEach(goods => {
                    this.setGoods(goods);
                })
            },
            goodsAdd(index, row) {
                let goods = this.goods_list[index];
                this.setGoods(goods);
            },

            setGoods(goods) {
                let sentinel = false;
                this.goods_list.forEach(v => {
                    if (v.id == goods.id) {
                        sentinel = true;
                    }
                });
                if (sentinel) {
                    this.select_goods_list.push(goods);
                    if (this.ruleForm.goods_id_list.indexOf(goods.id) == -1) {
                        this.ruleForm.goods_id_list.push(goods.id);
                    }
                    this.$message({
                        message: '添加成功',
                        type: 'success'
                    });
                }
            },
            //分类
            catsDestroy(index, row) {
                this.ruleForm.cat.splice(index, 1);
            },
            // 点击获取商品分类列表或者商品列表
            search(url, attr, page = 1) {
                let self = this;
                request({
                    params: {
                        r: url,
                        keyword: this.keyword,
                        page: page,
                    },
                }).then(e => {
                    self.dialogLoading = false;
                    self.dialogBtnLoading = false;
                    if (e.data.code == 0) {
                        if (attr == 'cats') {
                            self.cats_list = e.data.data.list;
                        } else if (attr == 'goods') {
                            self.goods_list = e.data.data.list;
                            self.pagination = e.data.data.pagination;
                        }
                    }
                }).catch(e => {

                });
            },
            // 获取分类列表
            getCatList() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/fxhb/mall/activity/cats',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.cats = e.data.data.cats;
                    }
                }).catch(e => {
                });
            },
            addShareTitle() {
                let self = this;
                if (self.shareTitle) {
                    if (self.ruleForm.share_title.indexOf(self.shareTitle) === -1) {
                        self.ruleForm.share_title.push(self.shareTitle);
                        self.shareTitle = '';
                    }
                }
            },
            deleteShareTitle(index) {
                this.ruleForm.share_title.splice(index);
            },
            itemChecked(type) {
                if (type === 1) {
                    this.ruleForm.sponsor_num = this.isSponsorNum ? -1 : 0
                } else if (type === 2) {
                    this.ruleForm.help_num = this.isHelpNum ? -1 : 0
                } else if (type === 3) {
                    this.ruleForm.sponsor_count = this.isSponsorCount ? -1 : 0
                } else {
                }
            },

            getPermsssions() {
                let self = this;
                request({
                    params: {
                        r: 'mall/index/mall-permissions'
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        self.isScanCodePayShow = 0;
                        e.data.data.permissions.forEach(function (item) {
                            if (item == 'scan_code_pay') {
                                self.isScanCodePayShow = 1;
                            }
                        })
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            selection(row) {
                if (this.ruleForm.goods_id_list.indexOf(row.id) == -1) {
                    return true;
                } else {
                    return false;
                }
            }
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getDetail();
            }
            this.getCatList();
            this.getPermsssions();
        }
    });
</script>
