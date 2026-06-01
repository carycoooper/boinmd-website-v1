# Knowledge Article Publishing SOP

## 1. 目标与适用范围
本 SOP 适用于 `knowledge_article` 的批量发布与维护，目标是保证文章在 SEO/GEO、模板展示、Schema 输出、内链转化、合规表达上保持一致。

适用分类：
- `tinnitus`
- `hearing-loss`
- `hearing-aids`
- `ai-hearing`
- `care`
- `fitting`
- `stories`

---

## 2. 发布顺序（强制）
1. 先完成内容草稿（标题、正文、FAQ、内链）
2. 在 WordPress 后台录入结构化字段
3. 预览桌面和移动端
4. 检查可见 FAQ 与 Schema 一致
5. 发布后抽查页面源码和链接可用性

---

## 3. 文章基础字段填写规范

### 3.1 标题（Title）
- 优先使用“问题型标题”
- 直接覆盖用户搜索意图
- 避免纯品牌宣传口吻

示例：
- `晚上耳鸣特别明显是怎么回事？`
- `电视声音越开越大，可能说明什么？`

### 3.2 Slug
- 必须英文小写 + 短横线
- 禁止中文 slug、日期 slug、随机数字 slug

示例：
- `night-tinnitus`
- `hearing-aid-vs-amplifier`
- `tv-volume-louder`
- `ai-noise-reduction`

### 3.3 分类（knowledge_category）
- 每篇只选 1 个主分类
- 避免多分类导致主题和内链混乱

### 3.4 Summary / Excerpt
- `summary`：1-2 句直接回答
- `excerpt`：用于列表与摘要展示，建议 60-120 字
- `summary` 为空时允许回退到 `excerpt`

### 3.5 时间与优先级
- 发布时间：按实际上线时间
- 更新时间：内容有实质变更才更新
- `featured_priority`：按当前代码逻辑执行（数值越大越靠前）

---

## 4. 正文结构规范（建议模板）
每篇正文建议包含：
1. 引言（用户场景）
2. 直接回答（核心结论）
3. 原因解释（分点）
4. 场景说明（老人/夜间/嘈杂环境等）
5. 建议做法（可执行）
6. FAQ（3-5 条）
7. 内链推荐（2 条以上）

要求：
- H2/H3 层级清楚（用于自动 TOC）
- 每个小节尽量单一主题
- 避免过长大段无结构文本

---

## 5. FAQ 录入规范
- 每篇建议 3-5 个 FAQ
- FAQ 必须页面可见
- FAQ 答案简短、清晰、合规
- FAQ Schema 仅可使用页面可见问答

字段建议：
- `question`
- `answer`

---

## 6. 内链规范（按分类）

### 6.1 耳鸣类（tinnitus）
- 必链：`/knowledge/tinnitus/`
- 建议链：`/knowledge/hearing-loss/`

### 6.2 听力下降类（hearing-loss）
- 必链：`/knowledge/hearing-loss/`
- 建议链：`/knowledge/hearing-aids/`

### 6.3 助听器类（hearing-aids）
- 必链：`/knowledge/hearing-aids/`
- 建议链：产品页（由系统 CTA 引导）

### 6.4 AI 助听类（ai-hearing）
- 必链：`/knowledge/ai-hearing/`
- 建议链：`/knowledge/hearing-aids/`

---

## 7. 视频字段规范
预留字段：
- `bilibili_video_url`
- `youtube_video_url`

规则：
- 国内站优先展示 B 站链接
- YouTube 作为海外 SEO 备用
- 无视频时不强行展示空模块

---

## 8. 相关推荐与热门优先级
- 相关推荐按同分类优先
- 排除当前文章
- 排序：先 `_bkh_featured_priority`，再发布时间
- 运营需定期维护优先级，保证重点内容持续曝光

---

## 9. 合规表达规范

### 9.1 禁用词
- 治愈
- 根治
- 恢复听力
- 保证听清
- 保证改善
- 适合所有人
- 一定有效
- 立刻听清
- 消除所有噪音

### 9.2 推荐表达
- 可能
- 有助于
- 因人而异
- 建议结合听力测试判断
- 仅作科普参考
- 不替代专业诊断或治疗
- 具体适配效果因人而异

---

## 10. 发布前检查清单（逐项勾选）
- [ ] 标题是否问题型
- [ ] slug 是否英文短横线
- [ ] 分类是否正确且仅 1 个
- [ ] summary 是否填写（或可用 excerpt 回退）
- [ ] FAQ 是否可见且 3-5 条
- [ ] 是否有至少 2 个有效内链
- [ ] 是否无禁用词表达
- [ ] 图片是否有 alt
- [ ] 视频链接是否可访问
- [ ] 移动端预览是否正常
- [ ] 页面源码是否无明显错误

---

## 11. 首批试发布建议（8篇）
- 耳鸣：2 篇
- 听力下降：2 篇
- 助听器：2 篇
- AI 助听：2 篇

试发布重点核验：
- 模板加载
- TOC 生成
- FAQ 可见与 Schema 一致
- 相关推荐
- CTA 路径

---

## 12. 服务器侧上线前技术检查（待执行）
在有 PHP 环境的服务器或本地执行：

```bash
php -l boinmd-backup/boin-knowledge-hub.bak.20260522-154902/includes/functions.php
php -l boinmd-backup/boin-knowledge-hub.bak.20260522-154902/includes/class-bkh-schema.php
php -l boinmd-backup/boin-knowledge-hub.bak.20260522-154902/templates/single-knowledge_article.php
php -l boinmd-backup/boin-knowledge-hub.bak.20260522-154902/templates/single-knowledge_topic.php
```

预期结果：
- `No syntax errors detected`
