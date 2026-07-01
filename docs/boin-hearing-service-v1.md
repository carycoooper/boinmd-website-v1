# Boin Hearing Service 插件 V1 使用说明

插件目录：oinmd-backup/boin-hearing-service/

上线目标目录：/www/wwwroot/www.boinmd.com.cn/blog/wp-content/plugins/boin-hearing-service/

## 已实现能力

- 设备型号管理：hearing_device
- 用户需求管理：hearing_request
- 听力测试记录：hearing_test
- REST API：
  - GET /wp-json/boin/v1/devices
  - POST /wp-json/boin/v1/request
  - POST /wp-json/boin/v1/test
- 企业微信 Webhook 通知配置
- 前端 shortcode：
  - [boin_home]
  - [boin_test]
  - [boin_request]

## 安全说明

- 提交接口需要 WordPress REST nonce。
- 每个 IP 10 分钟最多提交 10 次。
- 企业微信 Webhook 只保存在后台 option，不输出到前端。
- 短信能力当前为占位，不会实际发送。

## 上线后检查

在服务器插件目录执行：

`ash
php -l boin-hearing-service.php
php -l includes/functions.php
php -l includes/class-device-cpt.php
php -l includes/class-request-cpt.php
php -l includes/class-test-cpt.php
php -l includes/class-rest-api.php
php -l includes/class-wecom.php
php -l admin/admin-settings.php
php -l frontend/shortcode-home.php
php -l frontend/shortcode-test.php
php -l frontend/shortcode-request.php
`

## WordPress 后台操作

1. 启用插件：Boin Hearing Service
2. 进入「悦听礼赠款系统」
3. 检查默认设备「悦听礼赠款助听器」是否已创建
4. 配置企业微信 Webhook
5. 新建测试页面，插入：

`	ext
[boin_home]
[boin_test]
[boin_request]
`

## 后续建议

- 增加后台列表列：手机号、设备、状态、提交时间
- 增加一键重发企业微信通知
- 增加短信服务商配置
- 增加表单验证码或 Turnstile
- 增加手机号登录态和用户历史记录查询
