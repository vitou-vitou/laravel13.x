<?php
namespace App\Filament\Resources\IssueResource\Pages;

use App\Filament\Resources\IssueResource;
use Filament\Resources\Pages\ListRecords;

class ListIssues extends ListRecords
{
    protected static string $resource = IssueResource::class;
}