# System Notebook — Phillip Insurance KH (`phillipinsurancekh`)

> **Copy this file to:** `D:\phillipinsurancekh\docs\BOSS-NOTEBOOK.md`  
> Update every **Friday** (15 min). Say the **one sentence** out loud once per week.

**Status:** Template — fill `[TBD]` from your local project.

---

## One sentence (say this first in meetings)

This admin web app lets **Phillip General Insurance (Cambodia) staff** manage **[policies / claims / quotes / customers — pick what’s true]** so that **the business can sell insurance and handle claims without manual spreadsheets**.

*(Edit after you confirm in code or with senior.)*

---

## Who uses it

| Item | Answer |
|------|--------|
| Users | Internal staff: [TBD — underwriters, CS, admin, agents?] |
| Approx count | [TBD] |
| Public or internal only | [TBD — admin URL usually internal/VPN] |
| Login | [TBD — email/password, SSO, AD?] |

---

## Top 5 features (boss language — not code names)

Write **user → action → result**. Replace `[TBD]` after scanning menus/routes.

| # | Feature (boss words) | Who clicks | What happens | Main risk if broken |
|---|----------------------|------------|--------------|---------------------|
| 1 | [TBD — e.g. Create policy / quote] | [TBD] | [TBD] | [TBD] |
| 2 | [TBD — e.g. Claims registration] | [TBD] | [TBD] | [TBD] |
| 3 | [TBD — e.g. Customer lookup] | [TBD] | [TBD] | [TBD] |
| 4 | [TBD — e.g. Reports / export] | [TBD] | [TBD] | [TBD] |
| 5 | [TBD — e.g. User roles / permissions] | [TBD] | [TBD] | [TBD] |

---

## Product types (if insurance app — link to business)

Phillip public site mentions (confirm which your **admin** app handles):

- [ ] Private vehicle insurance  
- [ ] Electric vehicle insurance  
- [ ] Personal accident  
- [ ] Home insurance  
- [ ] Motorcycle insurance  
- [ ] Other: _______________

*Boss question:* “Which products go through **this** system?” → list checked boxes + “others in [system name]”.

---

## Where data lives

| Item | Answer |
|------|--------|
| Database | [TBD — MySQL, SQL Server, PostgreSQL?] |
| Main tables / models | [TBD — paste from step 2 below] |
| Files / uploads | [TBD — S3, local disk, Cloudinary?] |
| Email / SMS | [TBD] |
| External APIs | [TBD — payment, government, partner?] |

---

## URLs & deploy

| Env | URL |
|-----|-----|
| Production admin | [TBD] |
| Staging | [TBD] |
| Last deploy (approx) | [TBD] |
| Who deploys | [TBD] |

---

## What breaks often

| Symptom | Usual cause | First check | Who else knows |
|---------|-------------|-------------|----------------|
| [TBD] | [TBD] | [TBD] | [TBD] |
| Login fails | [TBD] | logs / session | [TBD] |
| Report wrong numbers | [TBD] | date filter / job | [TBD] |

---

## Questions boss asked that I couldn’t answer yet

Copy from every bad meeting. **Pick one per day** to research.

| Date | Question | Answer (when found) | Verified by |
|------|----------|---------------------|-------------|
| | | | |
| | | | |

---

## 30-second verbal tour (practice aloud)

1. “This is the **Phillip Insurance internal admin** for [main purpose].”  
2. “Staff use it to **[top feature 1]** and **[top feature 2]**.”  
3. “Data is in **[DB name]**; we deploy to **[prod URL]**.”  
4. “Right now **[one honest status: stable / we’re fixing X / waiting on Y]**.”  
5. “If you need detail on **[topic]**, I’ll verify after this and reply by **[time]**.”

---

## How to fill this from `D:\phillipinsurancekh` (Windows)

Open **PowerShell** in the project folder:

```powershell
cd D:\phillipinsurancekh
```

### Step 1 — What kind of project?

```powershell
Get-ChildItem -Name composer.json, package.json, artisan, *.sln -ErrorAction SilentlyContinue
```

- `artisan` + `composer.json` → Laravel (like your laravel13.x workflow)  
- `package.json` only → Node frontend or full-stack  
- `.sln` → .NET  

### Step 2 — Laravel: list routes (menu map for boss)

```powershell
php artisan route:list --columns=method,uri,name,action
```

Copy interesting URIs into **Top 5 features** (use plain English names).

### Step 3 — Laravel: list models (database map)

```powershell
Get-ChildItem -Path app\Models -Name -ErrorAction SilentlyContinue
# or older Laravel:
Get-ChildItem -Path app -Filter *.php -Recurse | Select-String -Pattern "class \w+ extends Model"
```

Write model names → “likely tables for policies, claims, users…”

### Step 4 — Find admin menus (Filament / Nova / custom)

Search in Cursor or PowerShell:

```powershell
Select-String -Path .\app,.\routes,.\resources -Pattern "filament|nova|admin|Policy|Claim|Quote" -SimpleMatch -ErrorAction SilentlyContinue | Select-Object -First 30
```

### Step 5 — Save output for yourself

```powershell
php artisan route:list > docs\route-list.txt
```

---

## Cursor on your PC (best option)

1. **File → Open Folder** → `D:\phillipinsurancekh`  
2. New chat, paste:

```text
Read this repo. Fill docs/BOSS-NOTEBOOK.md using boss language (not code jargon):
- one sentence purpose
- top 5 features (user → action → result)
- main models/tables
- who likely uses each module
Do not guess — mark [TBD] if unclear.
```

3. Each Friday: add rows to **Questions boss asked**.

---

## Link to your other systems

If `phillipinsurancekh` is **1 of 5** admin apps, add a index file:

`D:\docs\MY-FIVE-SYSTEMS.md`

| # | Folder / name | One sentence | Notebook file |
|---|----------------|--------------|---------------|
| 1 | phillipinsurancekh | [above] | BOSS-NOTEBOOK.md |
| 2 | [TBD] | | |
| 3 | [TBD] | | |
| 4 | [TBD] | | |
| 5 | [TBD] | | |

---

*Template from laravel13.x `docs/MEDIUM-DEV-MANAGER-COMMUNICATION-GUIDE.md` — customize on your machine.*
