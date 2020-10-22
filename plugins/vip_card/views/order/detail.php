<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/29 15:59
 */
Yii::$app->loadViewComponent('app-order-detail');
?>
<div id="app" v-cloak v-loading="loading">
    <app-order-detail get-order-list-url="plugin/vip_card/mall/order/index" :order="order" :active="active"></app-order-detail>
</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {
              loading: false,
              active:1,
              order:{},
            };
        },
        created() {
          this.getList();
        },
        methods: {
          //获取列表
          getList() {
              this.loading = true;
              request({
                  params: {
                      r: 'mall/order/detail',
                      order_id: getQuery('order_id'),
                  },
              }).then(e => {
                this.loading = false;
                if (e.data.code == 0) {
                    this.order = e.data.data.order;
                    if(this.order.cancel_status == 1) {
                        this.active = 5;
                    }
                    if(this.order.is_pay == 1){
                        this.active = 2;
                    }
                    if(this.order.is_send == 1){
                        this.active = 3;
                    }
                    if(this.order.is_confirm == 1){
                        this.active = 4;
                    }
                    if(this.order.is_sale == 1){
                        this.active = 5;
                    }
                }
              }).catch(e => {
              });
            }
        }
    })
</script>