<?php

namespace App\Filament\Resources\EvidenceReportResource\Pages;

use App\Filament\Resources\EvidenceReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEvidenceReports extends ListRecords
{
    protected static string $resource = EvidenceReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
