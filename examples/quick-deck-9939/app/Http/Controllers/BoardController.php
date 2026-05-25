<?php
namespace App\Http\Controllers;
use App\Models\Board;

class BoardController extends Controller {
    public function index() {
        $boards = Board::withCount('lists')->get();
        return view('boards.index', compact('boards'));
    }

    public function show(Board $board) {
        $board->load('lists.cards');
        return view('boards.show', compact('board'));
    }
}
