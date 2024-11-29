<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Parallax\FilamentComments\Infolists\Components\CommentsEntry;
use Illuminate\Support\Facades\Storage;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class ProjectResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'MANAGEMENT';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Project Owner')
                    ->required(),
                Forms\Components\DatePicker::make('due_date')
                    ->label('Batas Waktu'),
                Forms\Components\FileUpload::make('attachments')
                    ->label('Attachment')
                    ->directory('attachments')
                    ->preserveFilenames(),
                Forms\Components\TextArea::make('description')
                    ->label('Deskripsi')
                    ->rows(10)
                    ->cols(20),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Batas Waktu')
                    ->searchable(),
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
                //
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
                return Pages\ViewProject::getUrl([$record->id]);
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
                    TextEntry::make('name')->weight(FontWeight::Bold)->label('Project Name'),
                    TextEntry::make('user.name')->label('Owner')->weight(FontWeight::Bold),
                    TextEntry::make('due_date')->weight(FontWeight::Bold)->label('Batas Waktu'),
                    TextEntry::make('attachments')->label('attach')->columnSpan(6) ->placeholder('none') ->listWithLineBreaks() ->bulleted() ->formatStateUsing(function ($state) { return sprintf('<span style="--c-50:var(--primary-50);--c-400:var(--primary-400);--c-600:var(--primary-600);"  class="text-xs rounded-md mx-1 font-medium px-2 min-w-[theme(spacing.6)] py-1  bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30"> <a href="%s"  target="_blank">%s</a></span>', '/storage/'.$state, basename($state)); }) ->html(), 
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
            'view' => Pages\ViewProject::route('/{record}'),
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
