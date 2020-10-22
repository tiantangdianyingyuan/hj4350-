<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/6/14
 * Time: 11:22
 */
?>
<style>
    .container {
        position: relative;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('statics/img/mall/full-bg.png');
        background-size: 100% 100%;
    }

    .content {
        color: #2e92ff;
        height: 470px;
        position: absolute;
        right: 50%;
        top: 50%;
        margin-top: -235px;
    }

    .content-tip {
        margin-bottom: 90px;
    }

    @media screen and (min-width:1670px){
        .content {
            font-size: 28px;
        }

        .content-tip {
            font-size: 66px;
        }
    }

    @media screen and (max-width:1300px){
        .content {
            font-size: 23px;
        }

        .content-tip {
            font-size: 38px;
        }
    }

    @media screen and (max-width:1669px) and (min-width:1301px) {
        .content {
            font-size: 24px;
        }

        .content-tip {
            font-size: 52px;
        }
    }
</style>
<div id="app">
    <div class="container">
        <div class="content" flex="dir:top main:center cross:center">
            <div class="content-tip" flex="dir:top main:center cross:center">
                <div>您可创建的小程序数量已满!</div>
                <div>请联系官方购买</div>
            </div>
            <div>您已开通小程序数量<?php echo $count ?>个, 总授权小程序数量<?php echo $authorize_count ?>个</div>
        </div>
    </div>
</div>
