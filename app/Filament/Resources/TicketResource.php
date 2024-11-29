<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Support\Facades\Storage;
use Parallax\FilamentComments\Infolists\Components\CommentsEntry;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\Actions;
use Filament\Tables\Actions\SelectAction;
use App\Models\TicketStatus;
use Filament\Support\Colors\Color;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;



class TicketResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationLabel = 'Tugas';

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'MANAGEMENT';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('User', 'name')
                    ->label('Pemberi Tugas')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Project'),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('responsible_id')
                    ->relationship('User', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Ditugaskan Kepada')
                    ->required(),
                Forms\Components\Select::make('status_id')
                    ->relationship('status', 'name')
                    ->label('Status')
                    ->required(),
                Forms\Components\Select::make('priority_id')
                    ->relationship('priority', 'name')
                    ->label('Priority')
                    ->required(),
                Forms\Components\Select::make('type_id')
                    ->relationship('type', 'name')
                    ->label('Type')
                    ->required(),
                Forms\Components\DatePicker::make('due_date')
                    ->label('Batas Waktu'),
                Forms\Components\TextArea::make('description')
                    ->label('Deskripsi')
                    ->rows(10)
                    ->cols(20),
                Forms\Components\FileUpload::make('attachments')
                    ->label('Attachment')
                    ->directory('attachments')
                    ->preserveFilenames(),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (Builder $query) {
            // Hardcode ID staff tertentu, misalnya ID 3
            return $query->where('responsible_id', 3);
        })
        


            ->columns([
                Tables\Columns\TextColumn::make('responsible.name')
                    ->label('Ditugaskan Kepada')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\SelectColumn::make('status_id')
                    ->options(
                        TicketStatus::all()->pluck('name', 'id')->toArray()
                    )
                    ->label('Status')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('priority.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Batas Waktu')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
            SelectFilter::make('Ditugaskan Kepada')
                    ->relationship('responsible', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter Ditugaskan Kepada')
                    ->indicator('Ditugaskan Kepada'),
            SelectFilter::make('Status')
                    ->relationship('status', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter Status')
                    ->indicator('Status'),
            SelectFilter::make('Type')
                    ->relationship('type', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter Type')
                    ->indicator('Type'),
                
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
                
                Tables\Actions\Action::make('attachments')
                ->label('Upload File')
                ->form([
                    Forms\Components\FileUpload::make('attachments')
                        ->label('Attachment')
                        ->directory('attachments')
                        ->preserveFilenames()
                        ->required()
                ])
                ->action(function (array $data, $record) {
                    if (isset($data['attachments'])) {
                        $filePath = $data['attachments'];
                        
                        // Simpan path file ke dalam database
                        $record->update([
                            'attachments' => $filePath
                        ]);
            
                        // Opsional: Jika Anda ingin memindahkan file dari temporary storage ke permanent storage
                        // Storage::move('livewire-tmp/' . $filePath, 'attachments/' . $filePath);
            
                        // Return URL file untuk konfirmasi
                        return Storage::url($filePath);
                    }
                })
            ])
            ->recordUrl(function ($record) {
                return Pages\ViewTicket::getUrl([$record->id]);
                })
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Project')
                ->schema([
                    TextEntry::make('project.name')->weight(FontWeight::Bold)->label('Nama Project'),
                    TextEntry::make('user.name')->label('Owner')->weight(FontWeight::Bold),
                    TextEntry::make('responsible.name')->weight(FontWeight::Bold)->label('Ditugaskan Kepada'),
                    TextEntry::make('due_date')->weight(FontWeight::Bold)->label("Batas Waktu"),
                    TextEntry::make('status.name')  
                    ->badge()
                    ->color(fn ($record) => Color::hex($record->status->color)), 
                    TextEntry::make('priority.name')  
                    ->badge()
                    ->color(fn ($record) => Color::hex($record->priority->color)), 
                    TextEntry::make('type.name')  
                    ->badge()
                    ->color(fn ($record) => Color::hex($record->type->color)),
                ])->columns(4),
                Section::make('Task')
                ->schema([
                    TextEntry::make('name')->weight(FontWeight::Bold)->label('Nama Tugas'),
                    TextEntry::make('attachments')->label('attach')->columnSpan(6) ->placeholder('none') ->listWithLineBreaks() ->bulleted() ->formatStateUsing(function ($state) { return sprintf('<span style="--c-50:var(--primary-50);--c-400:var(--primary-400);--c-600:var(--primary-600);"  class="text-xs rounded-md mx-1 font-medium px-2 min-w-[theme(spacing.6)] py-1  bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30"> <a href="%s"  target="_blank">%s</a></span>', '/storage/'.$state, basename($state)); }) ->html(), 
                    TextEntry::make('description'),
                    CommentsEntry::make('filament_comments'),
                ])
            ]);
    }
 
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }
}
