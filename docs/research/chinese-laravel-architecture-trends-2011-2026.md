# Chinese-speaking Laravel/PHP developer architecture trends, 2011–2026

**Scope/method:** This covers Laravel's yearly release history alongside the architecture patterns favored by prominent Chinese-speaking PHP/Laravel developers and communities (Laravel China / LearnKu, Hyperf, Swoole, webman, EasySwoole, laravel-admin/dcat-admin). Every claim is sourced to an official repo, changelog, release note, or a first-party author post; where evidence is thin or absent for a given year, that is stated directly rather than invented. Laravel version dates are from the [official Laravel release notes](https://laravel.com/docs/13.x/releases) and the [Laravel Wikipedia version table](https://en.wikipedia.org/wiki/Laravel), cross-checked against [laravelupdates.com](https://laravelupdates.com/).

## 2011

- **Laravel release:** Laravel 1.0 (June 2011) and Laravel 2.0 (September 1, 2011) — [Wikipedia version table](https://en.wikipedia.org/wiki/Laravel), [laravelupdates.com](https://laravelupdates.com/).
- **Dominant architecture:** No distinct "Chinese Laravel community" architecture pattern exists yet — Laravel had negligible mindshare in China; the dominant Chinese PHP frameworks at the time were homegrown (e.g. ThinkPHP, authored by 刘晨 "OnceGeek"/"Loogn" era) discussed on forums like bbs.phpchina.com, per a first-person account by LearnKu founder Summer describing 2011-era Chinese PHP forum culture ([LearnKu 诞生的故事](https://learnku.com/articles/28735)).
- **Named projects/devs:** None Laravel-specific yet. Uncertain/no primary-source evidence of notable Chinese Laravel adopters this year.
- **Inflection point:** None.

## 2012

- **Laravel release:** Laravel 3.0 (Feb 22, 2012), 3.1 (Mar 27, 2012), 3.2 (May 22, 2012) — [Wikipedia](https://en.wikipedia.org/wiki/Laravel).
- **Dominant architecture:** Same as 2011 — thin/no distinct Chinese-community data. Swoole itself was created this year (repo created July 21, 2012 per [swoole/swoole-src GitHub](https://github.com/swoole/swoole)), but it had no Laravel integration at this point; it targeted raw async PHP servers, not Laravel apps.
- **Named projects/devs:** Swoole (创建 2012-07-21, author 韩天峰/matyhtf) — [swoole/swoole-src](https://github.com/swoole/swoole). Not yet connected to the Laravel ecosystem.
- **Inflection point:** Swoole's founding — relevant only retrospectively; no Laravel tie-in yet.

## 2013

- **Laravel release:** Laravel 4.0 (May 28, 2013) — [Wikipedia](https://en.wikipedia.org/wiki/Laravel).
- **Dominant architecture:** Uncertain. Summer (later LearnKu founder) states he began following Laravel "from Laravel 3" ([about-author, L01 course](https://learnku.com/courses/laravel-essential-training/11.x/about-author/16772)), implying early individual Chinese adopters existed, but no community-level architecture pattern is documented in primary sources for this year.
- **Named projects/devs:** Summer (summerblue) — early Laravel adopter in China, per his own author bio ([Laravel China 关于作者](https://learnku.com/courses/laravel-essential-training/11.x/about-author/16772)).
- **Inflection point:** None documented.

## 2014

- **Laravel release:** Laravel 4.1 (Dec 12, 2013, carries into 2014 usage) and Laravel 4.2 (June 1, 2014) — [Wikipedia](https://en.wikipedia.org/wiki/Laravel).
- **Dominant architecture:** Monolithic Laravel MVC (classic controller/Blade/Eloquent app) — Summer built and open-sourced the forum app **PHPHub** in Laravel 4.2 in August 2014, which became the seed of the Laravel China community — [author bio](https://learnku.com/courses/laravel-essential-training/11.x/about-author/16772), [Laravel China 社区突破 10000 用户](https://learnku.com/articles/3661/written-in-the-laravel-china-community-officially-break-10000-users), [LearnKu 诞生的故事](https://learnku.com/articles/28735).
- **Named projects/devs:** PHPHub (Summer, github.com/summerblue) — community began operating August 17, 2014 ([社区突破 10000 用户](https://learnku.com/articles/3661/written-in-the-laravel-china-community-officially-break-10000-users)). Community's other early leaders per Summer's own retrospective: overtrue (超哥) and monkey (铧哥) — [10000 用户文章](https://learnku.com/articles/3661/written-in-the-laravel-china-community-officially-break-10000-users).
- **Inflection point:** Founding of PHPHub / Laravel China community — the origin point of a distinct Chinese Laravel developer community.

## 2015

- **Laravel release:** Laravel 5.0 (Feb 4, 2015), 5.1 LTS (June 9, 2015) — [Wikipedia](https://en.wikipedia.org/wiki/Laravel).
- **Dominant architecture:** Monolithic MVC, server-rendered Blade apps — the standard pattern for Chinese Laravel learning content at this stage (e.g. the "L01 Laravel 教程" book by Summer and Aufree, originally written against Laravel 5.1 — [about-author page](https://learnku.com/courses/laravel-essential-training/5.1/about-author/142)).
- **Named projects/devs:** z-song's **laravel-admin** repo was created Dec 30, 2015 ([z-song/laravel-admin GitHub](https://www.github.com/z-song/laravel-admin)), becoming the dominant Chinese-authored Laravel admin-panel scaffold (CRUD backend builder). Author z-song (github.com/z-song).
- **Inflection point:** laravel-admin's creation — start of the "admin panel scaffold" pattern that stayed dominant in Chinese Laravel backend projects for years.

## 2016

- **Laravel release:** Laravel 5.2 (Dec 21, 2015 → used through 2016), 5.3 (Aug 23, 2016) — [Wikipedia](https://en.wikipedia.org/wiki/Laravel).
- **Dominant architecture:** Monolithic MVC + admin-panel scaffolding continued. Community-level shift: PHPHub was renamed to "**Laravel China 社区**" in September 2016 after running two years, per Summer's own account ([LearnKu 诞生的故事](https://learnku.com/articles/28735)).
- **Named projects/devs:** laravel-admin (z-song) in early growth. Laravel China renamed/rebranded — Summer (founder), overtrue, monkey as the "three admins" ([社区文章](https://learnku.com/articles/3661/written-in-the-laravel-china-community-officially-break-10000-users)).
- **Inflection point:** PHPHub → Laravel China rename (Sept 2016), cementing Laravel (not general PHP) as the community's core identity.

## 2017

- **Laravel release:** Laravel 5.4 (Jan 24, 2017), 5.5 LTS (Aug 30, 2017) — [Wikipedia](https://en.wikipedia.org/wiki/Laravel).
- **Dominant architecture:** **API-first + Vue.js SPA** pattern took hold immediately on Laravel 5.5's release, driven by the new first-party API Resources feature. Same-day/near-same-day Chinese community coverage: a LearnKu translation of "Creating APIs with Laravel API Resources" was published "2 hours after Laravel 5.5 shipped" per the translator's own account ([使用 Laravel 的 API 资源功能来构建你的 API](https://learnku.com/laravel/t/7528/use-the-api-resource-function-of-laravel-to-build-your-api)); official Chinese docs for the feature followed ([Laravel 5.5 中文文档 - API 资源](https://learnku.com/docs/laravel/5.5/eloquent-resources/1336?show_current_version=yes)). A concrete Laravel 5.5 + Vue 2 + axios + vue-router + vuex SPA build was documented by a community blogger ([Laravel5.5 + Vue 开发单页应用](https://learnku.com/articles/9054/laravel55-vue-development-single-page-application)).
- **Named projects/devs:** EasySwoole repo created March 15, 2017 ([easy-swoole/easyswoole GitHub](https://github.com/easy-swoole/easyswoole?files=1)) — a Swoole-based persistent-process framework, independent of Laravel, gaining traction among Chinese backend devs wanting higher throughput than PHP-FPM.
- **Inflection point:** Laravel 5.5's API Resources landing directly triggered the API+SPA architecture wave in the Chinese community; EasySwoole's founding also began the "Swoole long-running process framework" alternative track running in parallel to mainstream Laravel MVC.

## 2018

- **Laravel release:** Laravel 5.6 (Feb 7, 2018), 5.7 (Sept 4, 2018) — [Wikipedia](https://en.wikipedia.org/wiki/Laravel).
- **Dominant architecture:** API-first + Vue SPA matured into full multi-part tutorial series. A 40-article Chinese series "基于 Laravel 5.6 + Vue2 构建前后端分离应用" by 学院君 (Laravel Academy) documents this explicitly, including Passport auth, CSRF token handling via Axios headers, and `auth:api` middleware ([初始化 Laravel 单页面应用](https://laravel.geekai.co/post/9498.html), [目录索引](https://laravel.geekai.co/post/20917) listing GraphQL and JWT-auth variants of the same pattern).
- **Named projects/devs:** 学院君 (Laravel Academy) as a major Chinese Laravel educator; z-song's laravel-admin continued as the standard admin scaffold.
- **Inflection point:** Front-end/back-end separation (前后端分离) became the named, explicitly-taught default architecture for new Chinese Laravel projects by this point, per the tutorial series above.

## 2019

- **Laravel release:** Laravel 5.8 (Feb 26, 2019), Laravel 6 LTS (Sept 3, 2019) — [Wikipedia](https://en.wikipedia.org/wiki/Laravel).
- **Dominant architecture:** **Swoole-based coroutine microservices** emerged as a distinct, high-visibility alternative to Laravel MVC. **Hyperf** was publicly released June 20, 2019 (repo created Nov 29, 2018, per [hyperf/hyperf GitHub](https://github.com/hyperf/hyperf)), explicitly marketed as "building microservice or middleware with ease" using coroutines/DI/AOP/annotations, and stated it had already been running in production at "medium and large Internet companies" before public release ([hyperf/hyperf README](https://github.com/hyperf/hyperf)). Hyperf shipped 1.0.x/1.1.x releases through the year, reaching v1.1.9 by Dec 5, 2019 ([Hyperf CHANGELOG](https://github.com/hyperf/hyperf/blob/master/CHANGELOG.md)).
- **Named projects/devs:** Hyperf (organization: hyperf, formerly hyperf-cloud; contributor limingxinleo prominent in early releases per [v1.0.6 release notes](https://github.com/hyperf-cloud/hyperf/releases/tag/v1.0.6)). LearnKu itself turned 5 years old (counting from phphub.org's 2014 founding) by end of 2019 per Summer's own retrospective ([复盘 LK 社区公告](https://learnku.com/lk/t/38634)).
- **Inflection point:** **Hyperf's public launch (June 2019)** — the clearest, most citable inflection point in this whole period: it gave the Chinese PHP community a native coroutine microservice framework built specifically for the "beat Laravel/PHP-FPM performance" use case, distinct from and complementary to mainstream Laravel MVC work.

## 2020

- **Laravel release:** Laravel 7 (March 3, 2020), Laravel 8 (Sept 8, 2020) — [Wikipedia](https://en.wikipedia.org/wiki/Laravel).
- **Dominant architecture:** Hyperf matured rapidly — Hyperf 2.0 shipped June 2020 with a rewritten DI/AOP engine and added Coroutine Server support ([CHANGELOG-2.0.md](https://github.com/hyperf/hyperf/blob/master/CHANGELOG-2.0.md), [2.0 升级指南](https://geekdaxue.co/read/hyperf-3.0-doc/docs-zh-cn-upgrade-2.0.md)). In parallel, **webman** (by 洪光/walkor, the Workerman author) launched — repo created April 26, 2020 ([walkor/webman GitHub](https://github.com/walkor/webman)) — positioning itself as "probably the fastest PHP web framework in the world," built on Workerman's resident-memory model as a simpler alternative to Hyperf.
- **Named projects/devs:** Hyperf 2.0 (limingxinleo and core team); webman (walkor — also author of Workerman, a long-established Chinese async PHP socket framework).
- **Inflection point:** webman's launch — a second, lighter-weight resident-memory/coroutine framework option next to Hyperf, both explicitly pitched at replacing PHP-FPM-bound Laravel deployments for performance-sensitive services.

## 2021

- **Laravel release:** Laravel 8 continued (released Sept 2020, used through 2021; no new major version in 2021 — Laravel 9 shipped Feb 2022) — [Wikipedia](https://en.wikipedia.org/wiki/Laravel).
- **Dominant architecture:** **Laravel Octane** — Taylor Otwell's first-party package for running Laravel on Swoole or RoadRunner — went to beta in April 2021 and stable 1.0 on May 11, 2021 ([Laravel Octane v1.0 is Here, Laravel News](https://laravel-news.com/laravel-octane-1-0-0), [Octane Beta announcement](https://laravel-news.com/laravel-octane-beta)). This gave mainstream Laravel devs (including Chinese ones) an official path to Swoole-based long-running-process performance without leaving the Laravel framework, competing conceptually with Hyperf/webman for the same "beat PHP-FPM" use case. Hyperf 2.1 added the `hyperf/engine` abstraction allowing Hyperf apps to run on Swoole or the new Swow project ([Release v2.1.0](https://github.com/hyperf/hyperf/releases/tag/v2.1.0), [2.1 升级指南](https://geekdaxue.co/read/hyperf-3.0-doc/docs-zh-cn-upgrade-2.1.md)).
- **Named projects/devs:** Laravel Octane (Taylor Otwell, first-party); Hyperf 2.1 (Swow support added, per official upgrade guide).
- **Inflection point:** Octane's release (May 2021) — the moment mainstream Laravel itself absorbed the Swoole-performance architecture pattern that Chinese devs had already been exploring via Hyperf/webman/EasySwoole since 2017-2019; it gave Chinese teams the option to stay on stock Laravel rather than adopt a separate coroutine framework.

## 2022

- **Laravel release:** Laravel 9 (Feb 8, 2022) — [Wikipedia](https://en.wikipedia.org/wiki/Laravel).
- **Dominant architecture:** **dcat-admin** (by jqhph) rose as the dominant Chinese Laravel admin-panel package, built as a rewrite of z-song's laravel-admin, adding a "高颜值" (visually polished) component library — [jqhph/dcat-admin GitHub](https://github.com/jqhph/dcat-admin/). Exact creation-date confirmation for dcat-admin's launch is uncertain from the sources gathered here; what is confirmed is that it explicitly positions itself as laravel-admin's successor and documents its Chinese-language docs on LearnKu ([dcat-admin 中文文档 via LearnKu](https://learnku.com/docs/dcat-admin)).
- **Named projects/devs:** dcat-admin (jqhph); z-song's original laravel-admin still widely used but showing early signs of falling behind on Laravel-version compatibility.
- **Inflection point:** Uncertain/no single clear inflection documented for 2022 specifically in the primary sources gathered; treat as a continuation year for Octane/Hyperf/webman coexistence plus the admin-panel-tooling shift toward dcat-admin.

## 2023

- **Laravel release:** Laravel 10 (Feb 14, 2023) — [official 13.x release notes support table](https://laravel.com/docs/13.x/releases), [Wikipedia](https://en.wikipedia.org/wiki/Laravel).
- **Dominant architecture:** Continued coexistence of (a) stock Laravel 10 + Octane for performance-sensitive teams, (b) Hyperf for microservice-style Chinese backend teams, (c) webman for lighter resident-memory services, (d) dcat-admin as the default admin scaffold. Hyperf's 3.0 changelog (spanning 2022-2024) shows continued expansion of Swow support (UDP server, HTTP/WebSocket dual-port, metrics) — [CHANGELOG-3.0.md](https://github.com/hyperf/hyperf/blob/master/CHANGELOG-3.0.md).
- **Named projects/devs:** No new distinctly-2023 named project confirmed from primary sources gathered here; this is a thin year for fresh architecture-level evidence beyond continuation of the above tools' changelogs.
- **Inflection point:** None distinctly identified for 2023 in the sources gathered; mark as uncertain/needs further primary-source verification (e.g. direct Laracon-related or LearnKu 2023 annual posts were not retrieved in this pass).

## 2024

- **Laravel release:** Laravel 11 (March 12, 2024) — [official 13.x release notes support table](https://laravel.com/docs/13.x/releases).
- **Dominant architecture:** Hyperf 3.0.x continued shipping through 2024 (e.g. v3.0.49 dated 2024-05-16, per [CHANGELOG-3.0.md](https://github.com/hyperf/hyperf/blob/master/CHANGELOG-3.0.md)), adding RPC/tracer/aspect refinements — consistent with coroutine-microservice architecture staying mainstream for that segment of Chinese backend teams. webman's overtrue/laravel-wechat integration continued supporting Laravel 11 within weeks of its release (`support Laravel 11.0` PR merged by contributor hihuangwei, per [overtrue/laravel-wechat releases](https://github.com/overtrue/laravel-wechat/releases)), indicating fast Chinese-maintainer adoption of new Laravel majors for integration packages.
- **Named projects/devs:** Hyperf 3.0.x continued development; overtrue/laravel-wechat (maintainer overtrue) added Laravel 11 support.
- **Inflection point:** None singularly distinct for 2024 beyond routine version-compatibility churn; z-song's laravel-admin began accumulating unresolved Laravel-11-compatibility issues and security advisories around this period, foreshadowing its decline relative to dcat-admin ([laravel-admin issue #5888](https://github.com/z-song/laravel-admin/issues/5888) — filed Dec 2025 but referencing the ongoing Laravel 11 incompatibility problem).

## 2025

- **Laravel release:** Laravel 12 (Feb 24, 2025) — [official 13.x release notes support table](https://laravel.com/docs/13.x/releases). Laravel 12 was described by its own release notes as "a relatively minor maintenance release" upgrading dependencies without major breaking changes ([Laravel 12.x release notes](https://laravel.com/docs/12.x/releases)).
- **Dominant architecture:** z-song's laravel-admin (encore/laravel-admin) was flagged with a security advisory ("PKSA-fwvh-pm3c-1m7b") blocking installation under Composer's audit feature, and remained incompatible with Laravel 11/12 without unsafe workarounds (`dev-master`) — [laravel-admin issue #5888](https://github.com/z-song/laravel-admin/issues/5888) (filed Dec 2025, describing the ongoing state). This accelerated the community's shift toward dcat-admin or forks (e.g. `php-panel/laravel-admin`, mentioned by a community member in that same issue thread) as the practical default admin-panel choice for new Chinese Laravel 11/12 projects. EasySwoole (jqhph-unrelated; maintainer YF/kiss291323003 et al.) shipped v3.7.2 on Sept 19, 2025, still adapting to PHP 8.x annotation syntax and Swoole 5.x — [EasySwoole update log](https://www.easyswoole.com/Update/main.html), [easy-swoole/easyswoole releases](https://github.com/easy-swoole/easyswoole/releases).
- **Named projects/devs:** dcat-admin/php-panel forks (community-driven laravel-admin successors); EasySwoole 3.7.2.
- **Inflection point:** Effective end-of-life of the original z-song/laravel-admin as a viable choice for new Laravel 11+ Chinese projects, cementing dcat-admin (and its forks) as the practical successor.

## 2026

- **Laravel release:** Laravel 13 (March 17, 2026) — [official Laravel 13.x release notes](https://laravel.com/docs/13.x/releases), which describe it as focused on "AI-native workflows, stronger defaults, and more expressive developer APIs," adding first-party AI primitives, JSON:API resources, and semantic/vector search.
- **Dominant architecture:** overtrue/laravel-wechat shipped v8.0.0 on 2026-03-19, restricted to "Laravel 13+ only, minimum PHP 8.3" ([overtrue/laravel-wechat releases](https://github.com/overtrue/laravel-wechat/releases), [Packagist metadata](https://root.packagist.org/packages/overtrue/laravel-wechat)) — showing the Chinese-maintainer package ecosystem (WeChat integration, a domain-specific but extremely common requirement for China-market Laravel apps) tracking new Laravel majors within days of release. Swoole itself continued active development independent of Laravel/Hyperf, with v6.2.0 released April 7, 2026, adding coroutine-based FTP/SSH clients and io_uring support ([swoole/swoole-src README](https://github.com/swoole/swoole), [Releases](https://github.com/swoole/swoole-src/releases)). webman's core framework (workerman/webman-framework) reached v2.2.4 on 2026-08-24 ([Packagist](https://packagist.org/packages/workerman/webman-framework)).
- **Named projects/devs:** overtrue/laravel-wechat v8 (overtrue); Swoole v6.2.0 (matyhtf/NathanFreeman and core team); webman-framework v2.2.4 (walkor).
- **Inflection point:** No single 2026-specific architectural inflection is confirmed by primary sources gathered here beyond routine version-tracking; Laravel 13's "AI-native" framing (per its own release notes) is the newest first-party direction, but no primary-source evidence yet ties this specifically to a Chinese-developer architecture shift — mark as uncertain / to be revisited once more of 2026 has elapsed.

## Overall arc

- **2011–2013:** No distinct Chinese Laravel architecture pattern exists; ThinkPHP and homegrown PHP frameworks dominate Chinese PHP forums, while Laravel itself is a minority interest picked up by a handful of early individual adopters (Summer).
- **2014–2016:** Community formation phase — PHPHub (2014) → renamed Laravel China (2016) — establishes monolithic Blade/Eloquent MVC as the baseline pattern, with z-song's laravel-admin (2015) as the first widely-adopted Chinese-authored architectural building block (rapid CRUD scaffolding).
- **2017–2018:** Laravel 5.5's API Resources feature triggers an immediate, well-documented shift to **API-first backend + Vue.js SPA frontend** as the new default architecture for Chinese Laravel teams, driven by both official and community tutorials.
- **2017–2020 (parallel track):** A separate performance-oriented track emerges outside mainstream Laravel MVC: Swoole (2012, dormant until reused) → EasySwoole (2017) → **Hyperf** (2019, the single clearest inflection point in this whole survey) → **webman** (2020). These give Chinese backend teams coroutine-based, resident-memory alternatives to PHP-FPM-bound Laravel for high-throughput/microservice workloads.
- **2021:** Laravel absorbs the Swoole-performance pattern into the framework itself via **Octane**, letting teams get most of the coroutine-performance benefit without leaving mainstream Laravel — this narrows (but doesn't eliminate) the gap that drove Hyperf/webman adoption.
- **2022–2025:** Steady coexistence of four tracks — stock Laravel+Octane, Hyperf, webman, and admin-scaffold tooling migrating from the aging laravel-admin to dcat-admin (and its forks) as Laravel version compatibility and security advisories force the community off the original package.
- **2026:** Laravel 13 pivots toward "AI-native" first-party features; the Chinese-maintainer package ecosystem (overtrue's WeChat SDKs, Swoole, webman) continues tracking new Laravel majors quickly, but no primary source yet documents a distinctly new Chinese-community architecture pattern tied to this AI-native direction — this is the thinnest/most speculative year in the survey and should be revisited later in 2026.
- **Throughout:** No primary source in this research names a "DDD/hexagonal architecture" movement specific to the Chinese Laravel community; where DDD is mentioned near Hyperf, it is as an application-level design choice developers may adopt, not a framework-mandated pattern — treat any claim of "DDD is dominant in Chinese Laravel" as unverified.

## Sources

- https://laravel.com/docs/13.x/releases
- https://laravel.com/docs/12.x/releases
- https://laravel.com/framework/docs/11.x/releases
- https://laravelupdates.com/
- https://en.wikipedia.org/wiki/Laravel
- https://learnku.com/courses/laravel-essential-training/5.1/about-author/142
- https://learnku.com/courses/laravel-essential-training/11.x/about-author/16772
- https://learnku.com/articles/3661/written-in-the-laravel-china-community-officially-break-10000-users
- https://learnku.com/articles/28735
- https://learnku.com/lk/t/38634
- https://github.com/hyperf/hyperf
- https://hyperf.wiki/3.0/
- https://github.com/hyperf-cloud/hyperf/releases/tag/v1.0.6
- https://www.bookstack.cn/read/Hyperf-1.1.1/changelog.md
- https://github.com/hyperf/hyperf/blob/master/CHANGELOG.md
- https://github.com/hyperf/hyperf/blob/master/CHANGELOG-2.0.md
- https://geekdaxue.co/read/hyperf-3.0-doc/docs-zh-cn-upgrade-2.0.md
- https://github.com/hyperf/hyperf/releases/tag/v2.1.0
- https://geekdaxue.co/read/hyperf-3.0-doc/docs-zh-cn-upgrade-2.1.md
- https://github.com/hyperf/hyperf/blob/master/CHANGELOG-3.0.md
- https://github.com/walkor/webman
- https://webman.workerman.net/
- https://github.com/walkor/webman-framework
- https://packagist.org/packages/workerman/webman-framework
- https://explore.market.dev/ecosystems/php/projects/webman/releases
- https://github.com/swoole/swoole
- https://www.bookstack.cn/read/SwooleDoc/156.md
- https://github.com/swoole/swoole-src/releases/tag/v2.0.10-stable
- https://github.com/swoole/swoole-src/releases
- https://github.com/swoole/swoole-src/releases/tag/1.8.9-stable
- https://www.github.com/z-song/laravel-admin
- https://stackoverflow.com/questions/78545338/is-laravel-admin-1-8-19-not-compatible-with-laravel-11-8-0-your-requirements-c
- https://packagist.org/packages/encore/laravel-admin
- https://github.com/z-song/laravel-admin/issues/5888
- https://github.com/jqhph/dcat-admin/
- https://learnku.com/docs/dcat-admin
- https://laravel-news.com/laravel-octane-1-0-0
- https://laravel-news.com/laravel-octane-beta
- https://laravel-news.com/laravel-octane
- https://github.com/overtrue/laravel-wechat
- https://github.com/overtrue/laravel-wechat/blob/8.x/README_EN.md
- https://github.com/overtrue/laravel-wechat/blob/8.x/README.md
- https://root.packagist.org/packages/overtrue/laravel-wechat
- https://github.com/overtrue/laravel-wechat/releases
- https://github.com/easy-swoole/easyswoole/releases
- https://github.com/easy-swoole/easyswoole?files=1
- https://packagist.org/packages/easyswoole/easyswoole?type=composer
- https://www.easyswoole.com/Update/main.html
- https://packagist.org/packages/easyswoole/easyswoole
- https://learnku.com/laravel/t/7528/use-the-api-resource-function-of-laravel-to-build-your-api
- https://learnku.com/docs/laravel/5.5/eloquent-resources/1336?show_current_version=yes
- https://learnku.com/articles/9054/laravel55-vue-development-single-page-application
- https://laravel.geekai.co/post/9498.html
- https://laravel.geekai.co/post/20917
