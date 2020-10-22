<style>
    .app-preview .el-scrollbar__wrap {
        overflow-x: hidden;
    }

    .app-preview .mobile {
        width: 377px;
        height: 670px;
        background-color: #fff;
        margin: 0 auto;
    }

    .app-preview .screen {
        border: 1px solid #F3F5F6;
        height: 100%;
        width: 100%;
        margin: 0 auto;
        position: relative;
        background-color: #F7F7F7;
    }

    .app-preview .top-bar {
        width: 375px;
        height: 64px;
        position: relative;
        background: url('statics/img/mall/home_block/head.png') center no-repeat;
    }

    .app-preview .top-bar div {
        position: absolute;
        text-align: center;
        width: 378px;
        font-size: 16px;
        font-weight: 600;
        height: 64px;
        line-height: 88px;
    }

    .app-preview .content {
        position: absolute;
        top: 60px;
        bottom: 0;
        width: 100%;
    }

    .app-preview .tab {
        min-height: 50px;
        background: #ffffff;
        padding: 0 12px;
        font-size: 15px;
        margin-top: 12px;
        color: #353535;
    }

    .app-preview .goods-end {
        position: absolute;
        bottom: 0;
        height: 54px;
    }

    .app-preview .cart {
        position: absolute;
        right: 12px;
        top: 30px;
        background: rgba(255, 255, 255, 0.5);
        font-size: 0;
        padding: 10px;
        border-radius: 999px;
        -webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        z-index: 100;

        height: 42px;
        width: 42px;
    }

    .app-preview .text-price::before {
        content: '￥';
        font-size: 60%;
    }

    .app-preview .text-price-all::before {
        content: '￥';
        font-size: 100%;
    }

    .app-preview .share {
        margin-left: auto
    }

    .app-preview .share .el-image {
        height: 20px;
        width: 20px
    }

    .app-preview .share > div {
        color: #666666;
        margin-top: 6px
    }

    .app-preview .services {
        margin: 6px 5px;
    }

    .app-preview .services .el-image {
        height: 12px;
        width: 12px;
    }

    .app-preview .services > span {
        color: #666666;
        margin-left: 6px;
    }

    .app-preview .goods {
        background: #FFFFFF;
        color: #353535;
        padding: 16px 12px;
    }

    .app-preview .goods .origin-price {
        color: #999999;
        text-decoration: line-through;
        margin-left: 12px
    }

    .app-preview .goods .virtual-sales {
        color: #999999;
        margin-top: 6px
    }
    .app-preview .goods .goods-name {
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
        font-size:18px;
        white-space: normal !important;
    }

    .app-preview .detail {
        background: #ffffff;
    }

    .app-preview .detail img {
        width: 100%;
        height: 100%;
    }

    .app-preview .detail p {
        margin: 0;
        padding-bottom:0.3em;
    }

    .app-preview .detail table {
        width: 100% !important;
    }
</style>
<template id="app-preview">
    <div class="app-preview">
        <el-dialog title="手机端预览" :visible.sync="dialogGoodsVisible" top="5vh">
            <div class="mobile">
                <div class="screen">
                    <div class="top-bar" flex="main:center cross:center">
                        <div>商品详情</div>
                    </div>
                    <div class="content">
                        <el-scrollbar style="height: 100%">
                            <template v-if="previewInfo.is_pic!=false">
                                <el-carousel :autoplay="false" indicator-position="none">
                                    <el-carousel-item v-for="(item,index) in ruleForm['pic_url']" :key="index">
                                        <el-image style="height:375px;width:100%;" :src="item['pic_url']"></el-image>
                                    </el-carousel-item>
                                </el-carousel>
                            </template>

                            <template v-if="previewInfo.is_cart!=false">
                                <div class="cart">
                                    <el-image src="statics/img/mall/goods/nav-icon-cart.png"></el-image>
                                </div>
                            </template>

                            <template v-if="previewInfo.is_head!=false">
                                <div flex="dir:top" class="goods">
                                    <div class="goods-name">{{ruleForm.name}}</div>
                                    <div flex="dir:left" style="font-size:14px">
                                        <div flex="dir:top">
                                            <div flex="dir:left" style="align-items:flex-end;">
                                                <div style="font-size:26px;color:#ff4544;"
                                                     :class="actual.type">{{actual.price}}
                                                </div>
                                                <div class="origin-price">￥{{ruleForm.original_price}}</div>
                                            </div>
                                            <div class="virtual-sales">销量{{ruleForm.virtual_sales}}{{ruleForm.unit}}
                                            </div>
                                        </div>
                                        <div class="share" flex="dir:top main:center cross:center">
                                            <el-image src="statics/img/mall/goods/icon-share.png"></el-image>
                                            <div>分享</div>
                                        </div>
                                    </div>
                                </div>
                            </template>


                            <slot name="preview"></slot>

                            <template v-if="previewInfo.is_services!= false">
                                <div v-if="ruleForm.services && ruleForm.services.length"
                                     flex="dir:left" class="tab" style="flex-wrap: wrap">
                                    <div class="services" flex="dir:left cross:center"
                                         v-for="(v,k) in ruleForm.services">
                                        <el-image src="statics/img/mall/goods/yes.png"></el-image>
                                        <span>{{v.name}}</span>
                                    </div>
                                </div>
                            </template>

                            <template v-if="previewInfo.is_attr!= false">
                                <div class="tab" flex="dir:left cross:center">
                                    <div style="width:65px">选择</div>
                                    <div style="color:#666666">规格</div>
                                    <i class="el-icon-arrow-right" style="margin-left:auto"></i>
                                </div>
                            </template>

                            <slot name="preview_end"></slot>

                            <template v-if="previewInfo.is_mch">
                                <div class="tab" style="height:100%;padding:0">
                                    <el-image style="height:104px" src="statics/img/mall/26565.png"></el-image>
                                </div>
                            </template>

                            <template v-if="previewInfo.is_content!==false">
                                <div class="tab" flex="cross:center">
                                    暂无评价
                                </div>
                            </template>

                            <div class="tab">
                                <el-image src="statics/img/mall/goods/goods-detail.png"></el-image>
                            </div>
                            <div v-if="false" class="goods-end">
                                <el-image src="statics/img/mall/goods/goods-end.png"></el-image>
                            </div>
                            <div class="detail" v-html="ruleForm.detail"></div>
                        </el-scrollbar>
                    </div>
                </div>
            </div>

            <span slot="footer" class="dialog-footer">
                <el-button size='small' @click="dialogGoodsVisible = false">继续编辑</el-button>
                <el-button size='small' :loading="submitLoading" type="primary" @click="dialogSubmit">保存</el-button>
            </span>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('app-preview', {
        template: '#app-preview',
        props: {
            ruleForm: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            previewInfo: {
                type:Object,
                default: function(){
                    return {
                        is_head: true,
                        is_cart: true,
                        is_attr: true,
                        is_services: true
                    }
                }
            },
        },
        data() {
            return {
                dialogGoodsVisible: false,
                submitLoading: false,
            };
        },
        computed: {
            actual() {
                const price = Number(this.ruleForm.price);
                const attr = this.ruleForm.attr;

                let arr = [];
                attr.map(v => {
                    arr.push(Number(v.price));
                });
                let max = Math.max.apply(null, arr);
                let min = Math.min.apply(null, arr);

                let actualPrice = -1;
                let type = 'text-price';
                if (max > min && min >= 0) {
                    actualPrice = min + '-' + max;
                } else if (max == min && min >= 0) {
                    actualPrice = min;
                } else if (price > 0) {
                    actualPrice = price;
                } else if (price == 0) {
                    actualPrice = '免费';
                    type = '';
                }
                return {
                    price: actualPrice,
                    type: type
                };
            }
        },
        methods: {
            previewGoods() {
                this.dialogGoodsVisible = true;
            },
            dialogSubmit() {
                this.submitLoading = true;
                this.$emit('submit');
            },
        }
    });
</script>
