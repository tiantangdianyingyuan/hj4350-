## DIY组件说明

一个组件数据包含两个字段id和data，id为组件的名称也表示组件类型，data为组件的详细数据。



### 空白块

空白块只有展示作用，无其它功能，可自定义高度和颜色，宽度为屏幕100%；

- id: empty

- data:
```
{
  height: 10, // 高度
  background: "#ffffff", // 颜色
}
```


### 搜索

搜索是搜索页面的链接；

- id: search

- data:
```
{
  "color": "#f2f2f2", // 搜索框颜色
  "background": "#ffffff" // 背景颜色
  "raduis": 4 // 背景颜色
  "placeholder": "搜索" // 提示文字
  "textColor": "#555555" // 提示文字颜色
  "textPosition": "left|center" // 提示文字位置
}
```
