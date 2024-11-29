<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\TicketStatus as ModelsTicketStatus;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
{

    return [
    'all' => Tab::make()
        ->badgeColor('gray')
        ->badge($this->getAllCount()),
    'Menunggu' => Tab::make()
       ->badge($this->getMenungguCount())
       ->badgeColor('danger')
       ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('status', fn ($query) => $query->where('name', 'Menunggu'))),
    'Sedang Dikerjakan' => Tab::make()
        ->badgeColor('info')
       ->badge($this->getDikerjakanCount())
       ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('status', fn ($query) => $query->where('name', 'Sedang Dikerjakan'))),
    'Selesai' => Tab::make()
       ->badge($this->getSelesaiCount())
       ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('status', fn ($query) => $query->where('name', 'Selesai'))),
    ];
}

protected function getAllCount(): int
{
    return Ticket::count();
}

protected function getMenungguCount(): int
{
    return Ticket::whereHas('status', fn ($query) => $query->where('name', 'Menunggu'))->count();
}

protected function getDikerjakanCount(): int
{
    return Ticket::whereHas('status', fn ($query) => $query->where('name', 'Sedang Dikerjakan'))->count();
}

protected function getSelesaiCount(): int
{
    return Ticket::whereHas('status', fn ($query) => $query->where('name', 'Selesai'))->count();
}
}
