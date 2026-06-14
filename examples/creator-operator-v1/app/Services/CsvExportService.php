<?php

namespace App\Services;

use App\Models\Creator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    public function publishLogDownload(Creator $creator): StreamedResponse
    {
        $filename = 'publish-log-'.ltrim($creator->handle, '@').'.csv';

        return $this->streamDownload($filename, function ($handle) use ($creator): void {
            fputcsv($handle, [
                'date',
                'tiktok_url',
                'yt_url',
                'ig_url',
                'yt_video_id',
                'title_variant',
                'posted_time',
                'status',
                'views_yt_7d',
                'views_ig_7d',
                'notes',
            ]);

            $creator->publishLogEntries()
                ->orderByDesc('logged_on')
                ->orderByDesc('id')
                ->each(function ($entry) use ($handle): void {
                    fputcsv($handle, [
                        $entry->logged_on?->toDateString(),
                        $entry->tiktok_url,
                        $entry->yt_url,
                        $entry->ig_url,
                        $entry->yt_video_id,
                        $entry->title_variant,
                        $entry->posted_time?->toIso8601String(),
                        $entry->status->value,
                        $entry->views_yt_7d,
                        $entry->views_ig_7d,
                        $entry->notes,
                    ]);
                });
        });
    }

    public function settlementDownload(Creator $creator): StreamedResponse
    {
        $filename = 'monthly-settlement-'.ltrim($creator->handle, '@').'.csv';

        return $this->streamDownload($filename, function ($handle) use ($creator): void {
            fputcsv($handle, [
                'period_start',
                'period_end',
                'platform',
                'gross_payout_local',
                'currency',
                'payout_status',
                's_views',
                't_views',
                'attributed_revenue',
                'commission_rate_pct',
                'monthly_ops_fee',
                'commission_amount',
                'creator_net',
                'notes',
            ]);

            $creator->monthlySettlements()
                ->orderByDesc('period_end')
                ->orderByDesc('id')
                ->each(function ($row) use ($handle): void {
                    fputcsv($handle, [
                        $row->period_start?->toDateString(),
                        $row->period_end?->toDateString(),
                        $row->platform->value,
                        $row->gross_payout_local,
                        $row->currency,
                        $row->payout_status->value,
                        $row->s_views,
                        $row->t_views,
                        $row->attributed_revenue,
                        $row->commission_rate_pct,
                        $row->monthly_ops_fee,
                        $row->commission_amount,
                        $row->creator_net,
                        $row->notes,
                    ]);
                });
        });
    }

    /**
     * @param  callable(resource): void  $writer
     */
    private function streamDownload(string $filename, callable $writer): StreamedResponse
    {
        return response()->streamDownload(function () use ($writer): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            $writer($handle);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
