<?php

namespace App\Filament\Resources\ApprovedSubmissions\Pages;

use App\Filament\Resources\ApprovedSubmissions\ApprovedSubmissionResource;
use Filament\Resources\Pages\ListRecords;

class ListApprovedSubmissions extends ListRecords
{
    protected static string $resource = ApprovedSubmissionResource::class;
}
