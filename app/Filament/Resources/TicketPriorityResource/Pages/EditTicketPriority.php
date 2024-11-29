<?php

namespace App\Filament\Resources\TicketPriorityResource\Pages;

use App\Filament\Resources\TicketPriorityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTicketPriority extends EditRecord
{
    protected static string $resource = TicketPriorityResource::class;
    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
