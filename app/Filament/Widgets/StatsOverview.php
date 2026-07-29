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
use Illuminate\Database\Eloquent\Builder;
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
        $isRepresentative = $this->isRepresentative();
        $officeDescription = $isRepresentative ? 'Your office' : 'Last 7 days';

        $formSubmissionsQuery = $this->scopedQuery(FormSubmission::query());
        $batchesQuery = $this->scopedQuery(Batch::query());
        $employeeFormsQuery = $this->scopedQuery(EmployeeForm::query());
        $usersQuery = $this->scopedQuery(User::query());
        $officesQuery = $this->scopedOfficesQuery();

        return [
            Stat::make('Form submissions', number_format($formSubmissionsQuery->count()))
                ->description($officeDescription)
                ->descriptionIcon(Heroicon::ArrowTrendingUp)
                ->color('primary')
                ->chart($this->dailyCountsForSparkline($formSubmissionsQuery, 7)),

            Stat::make('Batches', number_format($batchesQuery->count()))
                ->description($officeDescription)
                ->descriptionIcon(Heroicon::ArrowTrendingUp)
                ->color('success')
                ->chart($this->dailyCountsForSparkline($batchesQuery, 7)),

            Stat::make('Employee forms', number_format($employeeFormsQuery->count()))
                ->description($isRepresentative ? 'Templates for your office' : 'Active templates')
                ->descriptionIcon(Heroicon::OutlinedDocumentText)
                ->color('warning'),

            Stat::make('Users', number_format($usersQuery->count()))
                ->description($isRepresentative ? 'Staff in your office' : 'Staff accounts')
                ->descriptionIcon(Heroicon::OutlinedUsers)
                ->color('info'),

            Stat::make('Offices', number_format($officesQuery->count()))
                ->description($isRepresentative ? 'Your office' : 'Registered offices')
                ->descriptionIcon(Heroicon::OutlinedBuildingOffice2)
                ->color('gray'),

            $this->employeeHeadcountStat(),
        ];
    }

    private function employeeHeadcountStat(): Stat
    {
        if ($this->isRepresentative()) {
            $officeId = auth()->user()?->office_id;
            $total = $officeId
                ? (int) (Office::query()->whereKey($officeId)->value('number_of_employees') ?? 0)
                : 0;

            return Stat::make('Employees', number_format($total))
                ->description('Headcount for your office')
                ->descriptionIcon(Heroicon::OutlinedUserGroup)
                ->color('success');
        }

        $total = (int) (Office::query()->sum('number_of_employees') ?? 0);

        return Stat::make('Employees (all offices)', number_format($total))
            ->description('Sum of headcount from each office')
            ->descriptionIcon(Heroicon::OutlinedUserGroup)
            ->color('success');
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function scopedQuery(Builder $query): Builder
    {
        $user = auth()->user();

        if ($user?->role === UserRole::REPRESENTATIVE->value) {
            $query->where('office_id', $user->office_id);
        }

        return $query;
    }

    /**
     * @return Builder<Office>
     */
    private function scopedOfficesQuery(): Builder
    {
        $query = Office::query();
        $user = auth()->user();

        if ($user?->role === UserRole::REPRESENTATIVE->value) {
            $query->whereKey($user->office_id);
        }

        return $query;
    }

    private function isRepresentative(): bool
    {
        return auth()->user()?->role === UserRole::REPRESENTATIVE->value;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return array<float>
     */
    private function dailyCountsForSparkline(Builder $query, int $days): array
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
