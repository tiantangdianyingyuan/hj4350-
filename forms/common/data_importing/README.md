## 迁移数据
### 主要说明

BaseImporting：迁移数据基类（包含一些主要操作方法）

Demo：迁移数据测试入口 （仅供测试使用）

DemoImportIng：迁移数据处理子类（需要继承BaseImporting类）

**注：创建子类时，子类的命名最好以表名+Importing来进行；
例如：导入视频表hjmall_video，则类命名为VideoImporting**

v3Video.json：测试数据json，仅供测试使用，为最终数据；


