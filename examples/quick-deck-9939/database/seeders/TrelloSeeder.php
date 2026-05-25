<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Board;
use App\Models\BoardList;
use App\Models\Card;

class TrelloSeeder extends Seeder {
    public function run(): void {
        $boards = [
            ['Website Redesign', '#6366f1'],
            ['Mobile App', '#10b981'],
            ['Marketing Q3', '#f59e0b'],
        ];

        $listNames = ['Backlog', 'In Progress', 'Review', 'Done'];

        $cardSamples = [
            ['Fix login bug', 'Users report 500 error on login', 'bug', 3],
            ['Design homepage', 'New hero section with CTA', 'design', 5],
            ['Write API docs', null, 'docs', 7],
            ['Setup CI/CD', 'GitHub Actions pipeline', 'devops', 2],
            ['User testing', 'Schedule 5 sessions', 'research', 8],
            ['Update dependencies', null, 'chore', 10],
            ['Landing page copy', 'New messaging framework', 'content', 4],
            ['Performance audit', 'Lighthouse score > 90', 'task', 6],
        ];

        foreach ($boards as [$name, $color]) {
            $board = Board::create(['name' => $name, 'color' => $color]);
            foreach ($listNames as $pos => $listName) {
                $list = BoardList::create(['board_id' => $board->id, 'name' => $listName, 'position' => $pos]);
                $cards = array_slice($cardSamples, ($pos * 2) % count($cardSamples), 2);
                foreach ($cards as $ci => [$title, $desc, $label, $daysAhead]) {
                    Card::create([
                        'board_list_id' => $list->id,
                        'title' => $title,
                        'description' => $desc,
                        'label' => $label,
                        'due_date' => now()->addDays($daysAhead),
                        'position' => $ci,
                    ]);
                }
            }
        }
    }
}
