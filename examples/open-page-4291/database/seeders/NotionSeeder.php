<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotionSeeder extends Seeder {
    public function run(): void {
        $pages = [
            [
                'title' => 'Getting Started',
                'icon' => '🚀',
                'cover_color' => 'bg-blue-200',
                'type' => 'page',
                'content' => "Welcome to your workspace!\n\nThis is your first page. You can write anything here — notes, ideas, plans, or documentation.\n\n## What can you do?\n\n- Write structured notes with headings\n- Create databases to track tasks and projects\n- Build a personal wiki for your team\n\n## Tips\n\nUse the sidebar to navigate between pages. Click any page to open it. Database pages show a table view with filters and sorting.\n\nHappy writing! ✨",
                'sort_order' => 1,
            ],
            [
                'title' => 'Meeting Notes',
                'icon' => '📝',
                'cover_color' => 'bg-yellow-200',
                'type' => 'page',
                'content' => "# Weekly Sync — May 2026\n\n**Attendees:** Alice, Bob, Carol, Dave\n\n**Date:** Monday, May 25 2026\n\n## Agenda\n\n1. Review last week's progress\n2. Blockers and dependencies\n3. Goals for this week\n4. Any other business\n\n## Notes\n\nAlice presented the Q2 roadmap. The team agreed to prioritize the dashboard redesign before the API refactor. Bob will handle the deployment pipeline. Carol is blocked on the design system update — Dave to unblock by Thursday.\n\n## Action Items\n\n- Alice: finalize roadmap doc by Wednesday\n- Bob: set up staging environment\n- Carol: follow up with Dave on design tokens\n- Dave: share design system Figma link",
                'sort_order' => 2,
            ],
            [
                'title' => 'Product Roadmap',
                'icon' => '🗺️',
                'cover_color' => 'bg-purple-200',
                'type' => 'page',
                'content' => "# Product Roadmap 2026\n\n## Q1 — Foundation\n\nFocus on infrastructure, performance, and core UX improvements. Shipped new onboarding flow, reduced load time by 40%, and launched mobile app beta.\n\n## Q2 — Growth\n\nExpand integrations ecosystem. Launch API v2 with webhooks support. Improve collaboration features for teams. Target: 10k active teams.\n\n## Q3 — Scale\n\nEnterprise features: SSO, audit logs, advanced permissions, SLA guarantees. Launch partner program.\n\n## Q4 — Intelligence\n\nAI-powered features: smart summaries, auto-tagging, content suggestions. Launch analytics dashboard 2.0.\n\n---\n\n*Last updated: May 2026*",
                'sort_order' => 3,
            ],
            [
                'title' => 'Task Tracker',
                'icon' => '✅',
                'cover_color' => 'bg-green-200',
                'type' => 'database',
                'content' => null,
                'sort_order' => 4,
            ],
            [
                'title' => 'Content Calendar',
                'icon' => '📅',
                'cover_color' => 'bg-pink-200',
                'type' => 'database',
                'content' => null,
                'sort_order' => 5,
            ],
            [
                'title' => 'Team Directory',
                'icon' => '👥',
                'cover_color' => 'bg-orange-200',
                'type' => 'page',
                'content' => "# Team Directory\n\n## Engineering\n\n**Alice Chen** — Engineering Lead\nalice@company.com · Slack: @alice\n\n**Bob Martinez** — Senior Backend Engineer\nbob@company.com · Slack: @bob\n\n**Carol Kim** — Frontend Engineer\ncarol@company.com · Slack: @carol\n\n## Design\n\n**Dave Wilson** — Design Lead\ndave@company.com · Slack: @dave\n\n**Eve Johnson** — UX Researcher\neve@company.com · Slack: @eve\n\n## Product\n\n**Frank Lee** — Product Manager\nfrank@company.com · Slack: @frank",
                'sort_order' => 6,
            ],
            [
                'title' => 'Engineering Docs',
                'icon' => '⚙️',
                'cover_color' => 'bg-gray-300',
                'type' => 'page',
                'content' => "# Engineering Documentation\n\n## Architecture Overview\n\nThe application follows a layered architecture with clear separation between presentation, business logic, and data access layers.\n\n## Tech Stack\n\n- **Backend:** Laravel 13 (PHP 8.3)\n- **Frontend:** Tailwind CSS + Alpine.js\n- **Database:** PostgreSQL (prod), SQLite (dev)\n- **Cache:** Redis\n- **Queue:** Laravel Horizon\n- **Search:** Meilisearch\n\n## Development Setup\n\n```bash\ngit clone https://github.com/company/app\ncd app\ncomposer install\nnpm install && npm run dev\ncp .env.example .env\nphp artisan key:generate\nphp artisan migrate --seed\n```\n\n## Deployment\n\nWe use GitHub Actions for CI/CD. Merges to `main` auto-deploy to staging. Production deploys require manual approval.",
                'sort_order' => 7,
            ],
        ];

        foreach ($pages as $p) {
            DB::table('pages')->insert(array_merge($p, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Task Tracker rows (page 4)
        $taskPage = DB::table('pages')->where('title', 'Task Tracker')->value('id');
        $tasks = [
            ['title' => 'Design new dashboard layout', 'status' => 'done', 'assignee' => 'Dave', 'due_date' => '2026-05-10', 'priority' => 'high', 'tags' => 'design'],
            ['title' => 'Implement authentication flow', 'status' => 'done', 'assignee' => 'Alice', 'due_date' => '2026-05-12', 'priority' => 'high', 'tags' => 'backend'],
            ['title' => 'Write API documentation', 'status' => 'in_progress', 'assignee' => 'Bob', 'due_date' => '2026-05-28', 'priority' => 'medium', 'tags' => 'docs'],
            ['title' => 'Set up CI/CD pipeline', 'status' => 'in_progress', 'assignee' => 'Bob', 'due_date' => '2026-05-30', 'priority' => 'high', 'tags' => 'devops'],
            ['title' => 'Conduct user interviews', 'status' => 'in_progress', 'assignee' => 'Eve', 'due_date' => '2026-06-01', 'priority' => 'medium', 'tags' => 'research'],
            ['title' => 'Fix mobile nav bug', 'status' => 'not_started', 'assignee' => 'Carol', 'due_date' => '2026-05-27', 'priority' => 'high', 'tags' => 'bug'],
            ['title' => 'Localize app to French', 'status' => 'not_started', 'assignee' => 'Carol', 'due_date' => '2026-06-10', 'priority' => 'low', 'tags' => 'i18n'],
            ['title' => 'Optimize image loading', 'status' => 'not_started', 'assignee' => 'Alice', 'due_date' => '2026-06-05', 'priority' => 'medium', 'tags' => 'performance'],
            ['title' => 'Add dark mode support', 'status' => 'not_started', 'assignee' => 'Carol', 'due_date' => '2026-06-15', 'priority' => 'low', 'tags' => 'design'],
            ['title' => 'Launch beta to 100 users', 'status' => 'not_started', 'assignee' => 'Frank', 'due_date' => '2026-06-20', 'priority' => 'high', 'tags' => 'launch'],
        ];
        foreach ($tasks as $t) {
            DB::table('db_rows')->insert(array_merge($t, ['page_id' => $taskPage, 'created_at' => now(), 'updated_at' => now()]));
        }

        // Content Calendar rows (page 5)
        $calPage = DB::table('pages')->where('title', 'Content Calendar')->value('id');
        $posts = [
            ['title' => 'Q2 Product Update Blog Post', 'status' => 'done', 'assignee' => 'Alice', 'due_date' => '2026-05-01', 'priority' => 'high', 'tags' => 'blog'],
            ['title' => 'Twitter thread: AI in PM tools', 'status' => 'done', 'assignee' => 'Frank', 'due_date' => '2026-05-05', 'priority' => 'medium', 'tags' => 'social'],
            ['title' => 'Case study: Customer X', 'status' => 'in_progress', 'assignee' => 'Alice', 'due_date' => '2026-05-30', 'priority' => 'high', 'tags' => 'case-study'],
            ['title' => 'YouTube: Product demo walkthrough', 'status' => 'in_progress', 'assignee' => 'Dave', 'due_date' => '2026-06-02', 'priority' => 'medium', 'tags' => 'video'],
            ['title' => 'Newsletter: May edition', 'status' => 'not_started', 'assignee' => 'Frank', 'due_date' => '2026-05-29', 'priority' => 'high', 'tags' => 'email'],
            ['title' => 'LinkedIn: Engineering culture post', 'status' => 'not_started', 'assignee' => 'Alice', 'due_date' => '2026-06-07', 'priority' => 'low', 'tags' => 'social'],
            ['title' => 'Webinar: Getting started guide', 'status' => 'not_started', 'assignee' => 'Frank', 'due_date' => '2026-06-15', 'priority' => 'medium', 'tags' => 'webinar'],
        ];
        foreach ($posts as $p) {
            DB::table('db_rows')->insert(array_merge($p, ['page_id' => $calPage, 'created_at' => now(), 'updated_at' => now()]));
        }
    }
}
