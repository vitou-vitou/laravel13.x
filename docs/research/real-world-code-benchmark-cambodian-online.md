# Real-world code benchmark → Cambodian online problems — primary-source map

**Date:** 2026-09-28
**Scope:** identify benchmark from prompt fingerprint (50 repos / real PRs / multi-language / 80+ engineers / ~1,505 annotated issues), map to Cambodian online problems. No secondary write-ups as truth.
**Web status:** `just-scrape` needs `SGAI_API_KEY` (validate prompts interactive, no key in env). Findings below from cutoff knowledge + first-party URLs. Re-verify with key before citing as proof.

**Verdict:** fingerprint = **Multi-SWE-bench family** (ByteDance, 2025). Closest match. Runner-up = **SWE-PolyBench** (AWS). Both = real PRs + multi-language + human-validated. Use their method (mine PRs → fail-to-pass tests → agent fix loop) to solve Cambodian online problems, don't import their data blindly.

## 1. Fingerprint match

| Claim in prompt | Multi-SWE-bench | SWE-PolyBench | SWE-bench (orig) |
|---|---|---|---|
| 50 or uncount repos | ~30 repos, 7 langs — closest | 21 repos, 4 langs | 12 repos, 1 lang (Python) — no |
| real PRs | yes, PR + linked issue + test patch | yes, PR + issue + test patch | yes |
| multi-language | Java, TS, JS, Go, Rust, C, C++ | Java, JS, TS, Python | Python only — no |
| 80+ senior engineers / ~1,505 annotated | human-filtered high-quality subset (~1,632 instances) — closest | human-validated stratified subset | auto-mined, later Verified subset (500) |
| conclusion | **best fit** | second fit | reject |

First-party origins (fetch when key ready):

- `https://github.com/multi-swe-bench/multi-swe-bench` (dataset + harness source)
- Paper: `https://arxiv.org/abs/2504.02605` (Multi-SWE-bench)
- `https://github.com/amazon-science/swe-polybench` (code + dataset)
- Paper: `https://arxiv.org/abs/2411.08733` (SWE-PolyBench)
- Orig baseline: `https://github.com/swe-bench/swe-bench`

## 2. How the method works (reuse this, not the data)

1. Mine merged PRs that fix linked issue + touch tests.
2. Split: problem statement (issue text) / codebase at base commit / fail-to-pass + pass-to-pass tests.
3. Agent edits code, harness runs tests in Docker. Pass = solved.
4. Human engineers filter junk (vague issue, flaky test, leaked answer). ~1.5k survive from ~10k+ candidates.

LDA-PO (5-bullet compress):

- Logic: issue → reproduce → patch → test gate.
- Data: PR triple (issue, gold patch, test patch).
- Architecture: Docker per-repo image, run `run-tests.sh`, no network.
- Portal: leaderboard + harness CLI only.
- Others: license filter (permissive OSS only), dedupe by commit SHA.

## 3. Cambodian online problems (real list)

Sourced from Cambodia digital reports pattern (MPTC, DataReportal, ABA/Wing docs — verify live):

- Facebook-first commerce, no storefront. Scam/no refund. COD dominates.
- Khmer script breaks: tofu PDF, wrong line-break, search/tokenizer fails (no spaces).
- Payments fragmented: ABA PayWay, Wing, Pi Pay, COD. No single Stripe.
- Logistics: Phnom Penh → province slow, no standard postcode, address = landmark text.
- Low bandwidth + old Android. Heavy JS fails.
- Trust/KYC: fake shops, no receipt, Khmer invoice missing.
- Language: EN UI, Khmer help on Facebook only.

## 4. Benchmark method → Cambodian fixes (map)

| Cambodian problem | OSS PR mine target (50-repo slice) | Fail-to-pass test |
|---|---|---|
| Khmer tofu PDF | `spatie/browsershot`, `dompdf`, `mpdf`, puppeteer, `fonts-khmeros` packaging PRs | `pdftotext x.pdf - \| grep ខ្មែរ` non-empty |
| Khmer search/tokenizer | `elasticsearch`, `meilisearch`, `typesense` CJK/Thai tokenizer PRs | Khmer query returns doc, no-space split |
| ABA/Wing webhook | `laravel/cashier`, `stripe-php`, PayWay community packages | fake webhook → order paid, idempotent replay |
| COD + province shipping | `woocommerce-shipping`, `laravel-shop`, address parser PRs | landmark address → zone fee, no crash on empty postcode |
| Low-bandwidth shop | `vue-storefront`, `livewire`, image-optimizer PRs | Lighthouse < 200KB first load on 3G throttle |
| Fake-shop trust | `laravel` auth, review/rating packages, receipt PDF PRs | duplicate review blocked, Khmer receipt renders |

Build own mini-bench: 50 repos = above + `laravel/framework`, `primevue`, `inertiajs`, `filament`, payment + PDF + search slices. 1,505-issue scale not needed. Start 50 issues, 80-engineer review = 2 Khmer reviewers per issue.

## 5. Minimal repo recipe (ponytail: fewest files)

- `docs/research/` note (this file). Done.
- `.just-scrape/` fetch PR triples when key exists. Not committed.
- One harness script only if building bench: `scripts/bench-run.sh` → `docker run` + `php artisan test --filter`. No new dep.
- `ponytail:` naive harness = single-thread Docker loop, ceiling ~50 tasks/hr, upgrade = parallel workers + cache images.

## 6. What not to do

- Don't train on benchmark gold patches with leaked tests still applied.
- Don't copy non-permissive repos (check LICENSE per repo).
- Don't claim multilingual = Khmer covered. No Khmer in Multi-SWE-bench / PolyBench. Khmer slice must be built locally.
- Don't solve via heavy SPA. Cambodia online = lite HTML + cash + Facebook link wins.

---

## Sources (to fetch with key)

- `https://github.com/multi-swe-bench/multi-swe-bench`
- `https://arxiv.org/abs/2504.02605`
- `https://github.com/amazon-science/swe-polybench`
- `https://arxiv.org/abs/2411.08733`
- `https://github.com/swe-bench/swe-bench`
- `https://pptr.dev/troubleshooting` (Docker flags, same as Khmer PDF note)
- `https://packages.debian.org/bookworm/fonts-khmeros` (Khmer font proof)
- Repo evidence: `docs/research/pdf-khmer-laravel-serversideup-render.md`, `examples/dynamic-warm-view-1906/Dockerfile`

*Disclosure: drafted with AI assistance; benchmark IDs from cutoff knowledge, URLs unfetched (no SGAI key). Run `just-scrape validate` then scrape URLs above before treating as verified.*
