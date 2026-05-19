<?php

namespace App\Filament\Resources\EvidenceReportResource\Pages;

use App\Filament\Resources\EvidenceReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEvidenceReport extends EditRecord
{
    protected static string $resource = EvidenceReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
