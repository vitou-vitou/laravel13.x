<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Card extends Model {
    protected $fillable = ['board_list_id', 'title', 'description', 'label', 'due_date', 'position'];
    protected $casts = ['due_date' => 'date'];
    public function list() { return $this->belongsTo(BoardList::class, 'board_list_id'); }
}
