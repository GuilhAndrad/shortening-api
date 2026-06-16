<?php

declare(strict_types=1);

namespace App\Actions\Url;

use App\Models\Url;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class GetUrlStatisticsAction
{
    /**
     * @return array{
     *     total_clicks: int,
     *     daily: array<int, array{date: string, clicks: int}>,
     *     weekly: array<int, array{week: string, clicks: int}>,
     *     monthly: array<int, array{month: string, clicks: int}>
     * }
     */
    public function execute(Url $url): array
    {
        return [
            'total_clicks' => $url->clicks_count,
            'daily' => $this->groupBy($url, "strftime('%Y-%m-%d', accessed_at)", 'date', days: 30),
            'weekly' => $this->groupBy($url, "strftime('%Y-%W', accessed_at)", 'week', days: 90),
            'monthly' => $this->groupBy($url, "strftime('%Y-%m', accessed_at)", 'month', days: 365),
        ];
    }

    /**
     * @return array<int, array{string, clicks: int}>
     */
    private function groupBy(Url $url, string $expression, string $alias, int $days): array
    {
        return $url->accesses()
            ->select(DB::raw("{$expression} as {$alias}"), DB::raw('COUNT(*) as clicks'))
            ->where('accessed_at', '>=', Carbon::now()->subDays($days))
            ->groupBy($alias)
            ->orderBy($alias)
            ->get()
            ->map(fn ($row) => [
                $alias => (string) $row->{$alias},
                'clicks' => (int) $row->clicks,
            ])
            ->toArray();
    }
}