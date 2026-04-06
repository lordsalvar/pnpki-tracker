<?php

namespace App\Filament\Widgets;

use App\Models\FormSubmission;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class FormSubmissionsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Form submissions (last 30 days)';

    protected ?string $description = 'New registrations per day.';

    protected ?string $maxHeight = '240px';

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $days = 30;
        $start = now()->subDays($days - 1)->startOfDay();

        /** @var \Illuminate\Support\Collection<string, int> $byDay */
        $byDay = FormSubmission::query()
            ->where('created_at', '>=', $start)
            ->selectRaw(sprintf('%s as d', $this->dateColumnExpression('created_at')))
            ->selectRaw('count(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('M j');
            $values[] = (int) ($byDay[$key] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Submissions',
                    'data' => $values,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function dateColumnExpression(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "to_char($column::date, 'YYYY-MM-DD')",
            default => "date($column)",
        };
    }
}
