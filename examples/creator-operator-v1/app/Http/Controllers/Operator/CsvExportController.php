<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Creator;
use App\Services\CsvExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportController extends Controller
{
    public function __construct(
        protected CsvExportService $csvExport,
    ) {}

    public function publishLog(Creator $creator): StreamedResponse
    {
        $this->authorize('view', $creator);

        return $this->csvExport->publishLogDownload($creator);
    }

    public function settlement(Creator $creator): StreamedResponse
    {
        $this->authorize('view', $creator);

        return $this->csvExport->settlementDownload($creator);
    }
}
