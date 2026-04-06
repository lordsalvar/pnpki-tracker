<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\EmployeeForm;
use App\Models\FormSubmission;
use App\Models\Office;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Overview';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        return [
            Stat::make('Form submissions', number_format(FormSubmission::query()->count()))
                ->description('Last 7 days')
                ->descriptionIcon(Heroicon::ArrowTrendingUp)
                ->color('primary')
                ->chart($this->dailyCountsForSparkline(FormSubmission::query(), 7)),

            Stat::make('Batches', number_format(Batch::query()->count()))
                ->description('Last 7 days')
                ->descriptionIcon(Heroicon::ArrowTrendingUp)
                ->color('success')
                ->chart($this->dailyCountsForSparkline(Batch::query(), 7)),

            Stat::make('Employee forms', number_format(EmployeeForm::query()->count()))
                ->description('Active templates')
                ->descriptionIcon(Heroicon::OutlinedDocumentText)
                ->color('warning'),

            Stat::make('Users', number_format(User::query()->count()))
                ->description('Staff accounts')
                ->descriptionIcon(Heroicon::OutlinedUsers)
                ->color('info'),

            Stat::make('Offices', number_format(Office::query()->count()))
                ->description('Registered offices')
                ->descriptionIcon(Heroicon::OutlinedBuildingOffice2)
                ->color('gray'),

            $this->employeeHeadcountStat(),
        ];
    }

    private function employeeHeadcountStat(): Stat
    {
        $user = auth()->user();

        if ($user?->role === UserRole::ADMIN->value) {
            $total = (int) (Office::query()->sum('number_of_employees') ?? 0);

            return Stat::make('Employees (all offices)', number_format($total))
                    ->description('Sum of headcount from each office')
                ->descriptionIcon(Heroicon::OutlinedUserGroup)
                ->color('success');
        }

        $officeId = $user?->office_id;
        $total = $officeId
            ? (int) (Office::query()->whereKey($officeId)->value('number_of_employees') ?? 0)
            : 0;

        return Stat::make('Employees', number_format($total))
            ->description('Headcount for your office')
            ->descriptionIcon(Heroicon::OutlinedUserGroup)
            ->color('success');
    }

    /**
     * @return array<float>
     */
    private function dailyCountsForSparkline(\Illuminate\Database\Eloquent\Builder $query, int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        /** @var \Illuminate\Support\Collection<string, int> $byDay */
        $byDay = $query
            ->clone()
            ->where('created_at', '>=', $start)
            ->selectRaw(sprintf('%s as d', $this->dateColumnExpression('created_at')))
            ->selectRaw('count(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $out[] = (float) ($byDay[$d] ?? 0);
        }

        return $out;
    }

    private function dateColumnExpression(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "to_char($column::date, 'YYYY-MM-DD')",
            default => "date($column)",
        };
    }
}
