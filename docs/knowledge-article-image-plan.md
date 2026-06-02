# 首批知识文章配图规划文档（试运行版）

## 一、文档目标

本文件用于为 `knowledge_article` 类型文章建立统一的配图规划规则。

当前阶段只做“配图规划”，不直接生成图片，不上传 WordPress，不自动插图。

目标：

- 每篇文章固定规划 3 张图。
- 图片内容必须服务于文章理解，而不是单纯装饰。
- 所有图片统一采用温暖柔和的水彩插画风格。
- 图片适合中老年用户和子女用户阅读。
- 每张图都需要明确用途、插入位置、文件名、alt、caption 和 prompt。
- 后续可继续扩展为图片生成、图片压缩、WordPress 上传和自动插图流程。

## 二、统一配图规则

每篇文章固定 3 张图。

### 图 1：主场景图（hero_scene）

- 作用：让用户一眼理解文章讨论的生活场景。
- 默认位置：`summary` 后，`TOC` 前。
- 风格：生活化、温和、中老年友好。
- 注意：不要做复杂信息图，不要放过多文字。

### 图 2：核心说明图（explanation）

- 作用：解释文章最重要的知识点、原因、对比逻辑或概念关系。
- 默认位置：第一个核心解释型 H2 后。
- 风格：轻信息图、对比图、4 卡片说明图。
- 注意：帮助理解即可，不要堆太多信息。

### 图 3：建议步骤图（steps）

- 作用：把“可以怎么做 / 如何选择 / 建议步骤”表达清楚。
- 默认位置：建议类 H2 后。
- 风格：4 步图、清单图、选购步骤图。
- 注意：简洁、易懂、适合手机端阅读。

## 三、统一视觉风格规范

所有文章配图统一采用：

- 温暖柔和的水彩插画风格。
- 白底或浅色背景。
- 家庭生活感。
- 中老年友好。
- 医疗可信，但不过度医疗化。
- 配色克制：深蓝、浅灰、米白、淡紫点缀。
- 画面清爽，适当留白。
- 少文字或无文字。
- 不要夸张痛苦表情。
- 不要恐怖医学图。
- 不要花哨营销海报风。
- 不要复杂解剖图。
- 不要强烈红色警示风格。

## 四、插图位置规则

默认插图位置：

- 图 1：`summary` 后，`TOC` 前。
- 图 2：第一个核心解释型 H2 后。
- 图 3：建议类 H2 后。

不要自动插在以下位置：

- FAQ 前。
- FAQ 中间。
- 相关推荐前。
- CTA 前。
- 目录和快速了解之间。

## 五、分类配图逻辑

### tinnitus（耳鸣）

- 图 1：耳鸣生活场景图。
- 图 2：原因说明图 / 白天夜晚对比图 / 耳鸣与听力变化关系图。
- 图 3：应对步骤图。

### hearing-loss（听力下降）

- 图 1：听不清生活场景图。
- 图 2：常见表现说明图。
- 图 3：建议步骤图 / 听力评估建议图。

### hearing-aids（助听器百科）

- 图 1：家庭助听器使用场景图。
- 图 2：助听器对比说明图。
- 图 3：选购步骤图。

### ai-hearing（AI 智能助听）

- 图 1：智能助听生活场景图。
- 图 2：AI 能力说明图。
- 图 3：选择建议图。

## 六、图片命名规范

统一命名：

```text
{slug}-hero.webp
{slug}-explanation.webp
{slug}-steps.webp
```

示例：

```text
night-tinnitus-hero.webp
night-tinnitus-explanation.webp
night-tinnitus-steps.webp
```

## 七、图片元信息规范

每张图片都需要包含：

- `image_slot`
- `image_type`
- `image_title`
- `insert_after`
- `filename`
- `alt`
- `caption`
- `prompt`

## 八、首批 8 篇文章配图规划

## 1. 晚上耳鸣特别明显是怎么回事？

- `title`：晚上耳鸣特别明显是怎么回事？
- `slug`：night-tinnitus
- `category`：tinnitus

### 图 1

- `image_slot`：1
- `image_type`：hero_scene
- `image_title`：夜晚耳鸣主场景图
- `insert_after`：summary
- `filename`：night-tinnitus-hero.webp
- `alt`：夜晚安静卧室中，中老年人因耳鸣难以入睡的温和生活场景
- `caption`：夜晚环境更安静时，耳鸣更容易被注意到。
- `prompt`：温暖柔和的水彩插画风格，夜晚卧室生活场景，一位中老年人躺在床上，表情略显困扰但不过度痛苦，环境安静，柔和灯光，浅色床品，画面干净简洁，中老年友好，家庭生活感，白色和浅灰背景，深蓝与淡紫色点缀，不要夸张表情，不要恐怖医学元素。

### 图 2

- `image_slot`：2
- `image_type`：explanation
- `image_title`：白天与夜晚耳鸣感受对比图
- `insert_after`：section-why-night-more-obvious
- `filename`：night-tinnitus-explanation.webp
- `alt`：白天和夜晚环境差异导致耳鸣更容易被感知的水彩示意图
- `caption`：夜晚更明显，不一定代表突然变严重。
- `prompt`：温暖柔和的水彩插画风格，对比示意图，左侧为白天环境，有说话声、电视声、交通声；右侧为夜晚安静环境，耳鸣更容易被注意到。画面清晰、浅色背景、轻信息图感觉，中老年友好，简洁克制，不要过多文字。

### 图 3

- `image_slot`：3
- `image_type`：steps
- `image_title`：耳鸣明显时的建议步骤图
- `insert_after`：section-what-to-do
- `filename`：night-tinnitus-steps.webp
- `alt`：耳鸣明显时可先尝试的 4 步建议水彩示意图
- `caption`：可以先从生活场景和听力情况开始观察。
- `prompt`：温暖柔和的水彩插画风格，四步建议信息图，内容包括：减少完全安静、调整作息、记录出现时间、观察是否伴随听不清。版式简洁，图标化表达，浅色背景，中老年友好，清晰、克制、医疗可信。

---

## 2. 一只耳朵耳鸣正常吗？

- `title`：一只耳朵耳鸣正常吗？
- `slug`：one-ear-ringing
- `category`：tinnitus

### 图 1

- `image_slot`：1
- `image_type`：hero_scene
- `image_title`：单侧耳鸣生活场景图
- `insert_after`：summary
- `filename`：one-ear-ringing-hero.webp
- `alt`：中老年人注意到一侧耳朵有异常声音的温和生活场景
- `caption`：单侧耳鸣更容易让人担心，需要结合具体情况判断。
- `prompt`：温暖柔和的水彩插画风格，中老年人在居家环境中轻触一侧耳朵，表情温和思考，不夸张，不痛苦，白色与浅灰背景，家庭感，深蓝淡紫点缀，简洁生活场景。

### 图 2

- `image_slot`：2
- `image_type`：explanation
- `image_title`：单侧耳鸣常见情况说明图
- `insert_after`：section-why-one-ear
- `filename`：one-ear-ringing-explanation.webp
- `alt`：单侧耳鸣常见原因和需要关注情况的水彩说明图
- `caption`：单侧耳鸣不一定严重，但持续存在时应关注变化。
- `prompt`：温暖柔和的水彩插画风格，轻信息图，说明单侧耳鸣可能与安静环境、疲劳、压力、听力变化等有关，画面分区清晰，浅色背景，简洁，适合中老年阅读。

### 图 3

- `image_slot`：3
- `image_type`：steps
- `image_title`：单侧耳鸣观察建议图
- `insert_after`：section-what-to-observe
- `filename`：one-ear-ringing-steps.webp
- `alt`：单侧耳鸣时可先观察和记录的建议步骤图
- `caption`：先记录变化，再判断是否需要进一步了解听力情况。
- `prompt`：温暖柔和的水彩插画风格，四步步骤图，内容包括：记录出现时间、观察是否持续、关注是否伴随听不清、必要时做听力评估。布局简洁，图标化，中老年友好。

---

## 3. 老人听不清别人说话怎么办？

- `title`：老人听不清别人说话怎么办？
- `slug`：cannot-hear-speech
- `category`：hearing-loss

### 图 1

- `image_slot`：1
- `image_type`：hero_scene
- `image_title`：老人听不清对话场景图
- `insert_after`：summary
- `filename`：cannot-hear-speech-hero.webp
- `alt`：老人和家人面对面交流时听不清说话的温和生活场景
- `caption`：很多听力变化，最早体现在“听不清别人说话”。
- `prompt`：温暖柔和的水彩插画风格，家庭客厅场景，老人和家人面对面交流，老人略显听不清，家人耐心沟通，画面温暖自然，中老年友好，白底浅色背景，深蓝与淡紫点缀。

### 图 2

- `image_slot`：2
- `image_type`：explanation
- `image_title`：听力下降常见表现说明图
- `insert_after`：section-common-signs
- `filename`：cannot-hear-speech-explanation.webp
- `alt`：听不清说话、总让别人重复、嘈杂环境更难交流等常见表现说明图
- `caption`：听不清说话，常常只是听力变化的一个表现。
- `prompt`：温暖柔和的水彩插画风格，四卡片说明图，内容包括：听不清说话、总让别人重复、嘈杂环境更难交流、电视音量变大。画面清晰，轻信息图感觉，适合中老年阅读。

### 图 3

- `image_slot`：3
- `image_type`：steps
- `image_title`：听不清说话时的建议步骤图
- `insert_after`：section-what-to-do
- `filename`：cannot-hear-speech-steps.webp
- `alt`：老人听不清别人说话时可先采取的建议步骤图
- `caption`：先从具体场景观察，再判断是否需要听力评估。
- `prompt`：温暖柔和的水彩插画风格，四步建议图，内容包括：先观察具体场景、记录是否持续、和家人确认、必要时做听力评估。版式简洁，生活化，中老年友好。

---

## 4. 电视声音越开越大，是听力下降吗？

- `title`：电视声音越开越大，是听力下降吗？
- `slug`：tv-volume-louder
- `category`：hearing-loss

### 图 1

- `image_slot`：1
- `image_type`：hero_scene
- `image_title`：电视音量变大生活场景图
- `insert_after`：summary
- `filename`：tv-volume-louder-hero.webp
- `alt`：老人看电视时不断调大音量的家庭生活场景
- `caption`：电视音量越来越大，是常见的听力变化信号之一。
- `prompt`：温暖柔和的水彩插画风格，居家客厅场景，老人看电视并调节遥控器音量，家人陪伴，画面自然温和，中老年友好，浅色背景，简洁。

### 图 2

- `image_slot`：2
- `image_type`：explanation
- `image_title`：电视音量变大的常见原因图
- `insert_after`：section-why-tv-volume
- `filename`：tv-volume-louder-explanation.webp
- `alt`：电视音量变大可能与听力变化和环境因素有关的示意图
- `caption`：电视音量变大，不一定只有一个原因。
- `prompt`：温暖柔和的水彩插画风格，说明图，展示听力变化、背景噪音、说话声分辨困难、使用习惯等因素，轻信息图风格，适合中老年用户。

### 图 3

- `image_slot`：3
- `image_type`：steps
- `image_title`：电视音量变大后的建议图
- `insert_after`：section-what-to-do
- `filename`：tv-volume-louder-steps.webp
- `alt`：电视音量变大时可以先观察和评估的步骤图
- `caption`：如果频繁出现，建议进一步了解听力情况。
- `prompt`：温暖柔和的水彩插画风格，四步建议图，内容包括：观察是否长期如此、关注家人反馈、留意是否伴随听不清说话、必要时做听力评估。画面清晰，简洁。

---

## 5. 第一次给父母买助听器怎么选？

- `title`：第一次给父母买助听器怎么选？
- `slug`：first-hearing-aid-for-parents
- `category`：hearing-aids

### 图 1

- `image_slot`：1
- `image_type`：hero_scene
- `image_title`：子女陪父母选助听器场景图
- `insert_after`：summary
- `filename`：first-hearing-aid-for-parents-hero.webp
- `alt`：子女陪父母了解助听器的温和家庭场景
- `caption`：第一次给父母买助听器，更重要的是从真实使用场景出发。
- `prompt`：温暖柔和的水彩插画风格，子女陪伴父母了解助听器的家庭或门店场景，氛围温暖，人物自然，白底浅色背景，深蓝点缀，适合中老年用户。

### 图 2

- `image_slot`：2
- `image_type`：explanation
- `image_title`：助听器选购要点说明图
- `insert_after`：section-selection-points
- `filename`：first-hearing-aid-for-parents-explanation.webp
- `alt`：第一次选助听器时建议重点关注的几个要点说明图
- `caption`：第一次选购，不要只看价格。
- `prompt`：温暖柔和的水彩插画风格，说明图，展示听力情况、使用场景、佩戴舒适度、操作简单、降噪清晰度、售后服务等六个要点，画面简洁清晰。

### 图 3

- `image_slot`：3
- `image_type`：steps
- `image_title`：第一次选购助听器步骤图
- `insert_after`：section-how-to-choose
- `filename`：first-hearing-aid-for-parents-steps.webp
- `alt`：第一次给父母选助听器时的步骤图
- `caption`：先了解听力情况，再看场景和使用便利性。
- `prompt`：温暖柔和的水彩插画风格，四步步骤图，内容包括：了解听力情况、看主要使用场景、比较佩戴和操作便利性、确认售后服务。中老年友好，家庭感。

---

## 6. 助听器和普通扩音器有什么区别？

- `title`：助听器和普通扩音器有什么区别？
- `slug`：hearing-aid-vs-amplifier
- `category`：hearing-aids

### 图 1

- `image_slot`：1
- `image_type`：hero_scene
- `image_title`：助听器与扩音器对比场景图
- `insert_after`：summary
- `filename`：hearing-aid-vs-amplifier-hero.webp
- `alt`：家庭场景中对比了解助听器与扩音器的温和插画
- `caption`：助听器和扩音器看起来都与“听”有关，但作用逻辑不同。
- `prompt`：温暖柔和的水彩插画风格，生活场景中展示助听器和扩音器概念对比，画面简洁、生活化，中老年友好，不要商业海报感。

### 图 2

- `image_slot`：2
- `image_type`：explanation
- `image_title`：助听器 vs 扩音器对比图
- `insert_after`：section-difference
- `filename`：hearing-aid-vs-amplifier-explanation.webp
- `alt`：助听器与普通扩音器区别的对比说明图
- `caption`：一个更强调声音处理和适配，一个更偏整体放大。
- `prompt`：温暖柔和的水彩插画风格，对比信息图，左侧普通扩音器，右侧助听器，说明整体放大、环境噪音、声音处理、佩戴适配等差异，版式简洁清晰，适合中老年阅读。

### 图 3

- `image_slot`：3
- `image_type`：steps
- `image_title`：如何判断更适合哪类方案图
- `insert_after`：section-how-to-judge
- `filename`：hearing-aid-vs-amplifier-steps.webp
- `alt`：判断听力问题和选择助听方案的步骤图
- `caption`：如果已经影响沟通，建议先了解听力情况。
- `prompt`：温暖柔和的水彩插画风格，四步建议图，内容包括：观察沟通影响、看使用场景、了解听力情况、再判断是否需要助听方案。生活化、简洁。

---

## 7. AI助听器真的有用吗？

- `title`：AI助听器真的有用吗？
- `slug`：ai-hearing-aid-useful
- `category`：ai-hearing

### 图 1

- `image_slot`：1
- `image_type`：hero_scene
- `image_title`：AI 助听生活场景图
- `insert_after`：summary
- `filename`：ai-hearing-aid-useful-hero.webp
- `alt`：中老年人在家庭交流场景中使用智能助听设备的温和插画
- `caption`：AI 助听的价值，更多体现在真实交流场景里。
- `prompt`：温暖柔和的水彩插画风格，家庭交流场景，中老年人与家人沟通，智能助听元素轻微体现，画面自然，不过度科技化，浅色背景，中老年友好。

### 图 2

- `image_slot`：2
- `image_type`：explanation
- `image_title`：AI 助听常见能力说明图
- `insert_after`：section-ai-features
- `filename`：ai-hearing-aid-useful-explanation.webp
- `alt`：AI 降噪、场景识别、语音增强等能力说明图
- `caption`：AI 不是万能，但在部分场景下有助于改善听声体验。
- `prompt`：温暖柔和的水彩插画风格，四卡片说明图，内容包括 AI 降噪、场景识别、语音增强、智能调节，画面简洁，轻信息图风格，中老年友好。

### 图 3

- `image_slot`：3
- `image_type`：steps
- `image_title`：选择 AI 助听器时看什么图
- `insert_after`：section-how-to-choose
- `filename`：ai-hearing-aid-useful-steps.webp
- `alt`：选择 AI 助听器时建议重点关注的步骤图
- `caption`：看功能，也要看是否适合真实生活场景。
- `prompt`：温暖柔和的水彩插画风格，四步选择建议图，内容包括：看听力情况、看人声表现、看操作是否简单、看佩戴和售后支持。清晰克制，家庭感。

---

## 8. AI降噪助听器是什么意思？

- `title`：AI降噪助听器是什么意思？
- `slug`：ai-noise-reduction-hearing-aid
- `category`：ai-hearing

### 图 1

- `image_slot`：1
- `image_type`：hero_scene
- `image_title`：嘈杂环境中的智能助听场景图
- `insert_after`：summary
- `filename`：ai-noise-reduction-hearing-aid-hero.webp
- `alt`：在超市或公共环境中使用 AI 降噪助听的温和生活场景
- `caption`：AI 降噪更适合放在真实生活场景里理解。
- `prompt`：温暖柔和的水彩插画风格，超市或公共交流场景，中老年人面对复杂声音环境，画面自然，突出“人在说话、背景有环境声”的生活感，不要科技炫光风。

### 图 2

- `image_slot`：2
- `image_type`：explanation
- `image_title`：AI 降噪作用示意图
- `insert_after`：section-what-is-ai-noise-reduction
- `filename`：ai-noise-reduction-hearing-aid-explanation.webp
- `alt`：AI 降噪帮助减少部分背景噪音、让说话声更容易被关注的示意图
- `caption`：AI 降噪不是消除所有噪音，而是尝试减少部分干扰。
- `prompt`：温暖柔和的水彩插画风格，说明图，展示背景噪音、说话声、AI 降噪处理后的关注重点变化，版式简洁，轻信息图风格，适合中老年阅读。

### 图 3

- `image_slot`：3
- `image_type`：steps
- `image_title`：是否需要关注 AI 降噪功能建议图
- `insert_after`：section-when-is-it-useful
- `filename`：ai-noise-reduction-hearing-aid-steps.webp
- `alt`：判断是否需要 AI 降噪助听功能的建议步骤图
- `caption`：如果主要困难出现在嘈杂环境，可以重点了解这类能力。
- `prompt`：温暖柔和的水彩插画风格，四步建议图，内容包括：确认主要困难场景、区分人声和噪音干扰、看操作难度、结合实际听力情况判断。画面简洁、温和、可信。

## 九、后续执行建议

第一步：先使用本文档确认首批 8 篇文章的 3 图逻辑。

第二步：确认每篇文章的图片逻辑、插入位置、caption 与正文内容匹配。

第三步：进入图片生成流程。

第四步：图片生成完成后，再接 WordPress 自动上传与插图流程。

## 十、当前阶段暂不做

当前阶段先不做：

- 不直接生成图片。
- 不直接写入 WordPress 数据库。
- 不自动插图。
- 不处理图片压缩和 WebP 转换。
- 不处理 CDN 上传。

## 十一、后续可新增文件

后续可继续新增：

- `docs/knowledge-article-image-generation-prompts.md`：汇总图片生成提示词。
- `docs/knowledge-article-image-insert-rules.md`：说明图片如何自动插入文章。
- `scripts/import-knowledge-article-images.php`：后续自动把图片挂到对应文章。
