<?php

namespace App\Filament\Resources\Batches;

use App\Enums\UserRole;
use App\Filament\Resources\Batches\Pages\EditBatch;
use App\Filament\Resources\Batches\Pages\ListBatches;
use App\Filament\Resources\Batches\Pages\ViewBatch;
use App\Filament\Resources\Batches\Schemas\BatchForm;
use App\Filament\Resources\Batches\Schemas\BatchInfolist;
use App\Filament\Resources\Batches\Tables\BatchesTable;
use App\Models\Batch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class BatchResource extends Resource
{
    private const LAST_VIEWED_BATCH_SESSION_KEY = 'filament.last_viewed_batch_id';

    protected static ?string $model = Batch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'batch_name';

    public static function form(Schema $schema): Schema
    {
        return BatchForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BatchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
            \App\Filament\Resources\Batches\RelationManagers\FormSubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBatches::route('/'),
            'view' => ViewBatch::route('/{record}'),
            'edit' => EditBatch::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {

        $user = Auth::user();

        if ($user->role === UserRole::ADMIN->value) {
            return parent::getEloquentQuery()
                ->where('status', 'finalized');
        }

        return parent::getEloquentQuery()
            ->where('office_id', Auth::user()->office_id);
    }

    public static function getNavigationUrl(): string
    {
        $batchId = session(self::LAST_VIEWED_BATCH_SESSION_KEY);

        if (! filled($batchId)) {
            return static::getUrl('index');
        }

        if (static::getEloquentQuery()->whereKey($batchId)->exists()) {
            return static::getUrl('view', [
                'record' => $batchId,
                'from_nav' => 1,
            ]);
        }

        session()->forget(self::LAST_VIEWED_BATCH_SESSION_KEY);

        return static::getUrl('index');
    }
}
