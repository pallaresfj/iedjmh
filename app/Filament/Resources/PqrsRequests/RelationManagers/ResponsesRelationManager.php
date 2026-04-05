<?php

namespace App\Filament\Resources\PqrsRequests\RelationManagers;

use App\Filament\Resources\PqrsRequests\Pages\ViewPqrsRequest;
use App\Models\PqrsMessage;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ResponsesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Respuestas';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if ($pageClass !== ViewPqrsRequest::class) {
            return false;
        }

        return parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with('user')
                ->whereNotNull('user_id')
                ->where('is_internal', false)
                ->orderByRaw('COALESCE(responded_at, created_at) desc')
                ->orderBy('id', 'desc'))
            ->columns([
                TextColumn::make('responded_at')
                    ->label('Fecha')
                    ->state(fn (PqrsMessage $record): ?\DateTimeInterface => $record->responded_at ?? $record->created_at)
                    ->dateTime('d/m/Y H:i')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderByRaw("COALESCE(responded_at, created_at) {$direction}")
                        ->orderBy('id', $direction)),
                TextColumn::make('subject')
                    ->label('Asunto')
                    ->placeholder('Sin asunto')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('author_name')
                    ->label('Autor')
                    ->state(fn (PqrsMessage $record): string => (string) ($record->author_name ?: $record->user?->name ?: 'Institucion'))
                    ->searchable(),
                TextColumn::make('message')
                    ->label('Extracto')
                    ->state(fn (PqrsMessage $record): string => Str::limit(Str::squish(strip_tags((string) $record->message)), 90))
                    ->wrap(),
            ])
            ->recordActions([
                Action::make('view_response')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->slideOver()
                    ->modalHeading(fn (PqrsMessage $record): string => filled($record->subject) ? $record->subject : 'Respuesta institucional')
                    ->modalSubmitAction(false)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('responded_at')
                                    ->label('Fecha')
                                    ->content(fn (PqrsMessage $record): string => ($record->responded_at ?? $record->created_at)?->format('Y-m-d H:i') ?? 'Sin fecha'),
                                Placeholder::make('author')
                                    ->label('Autor')
                                    ->content(fn (PqrsMessage $record): string => (string) ($record->author_name ?: $record->user?->name ?: 'Institucion')),
                            ]),
                        Placeholder::make('subject')
                            ->label('Asunto')
                            ->content(fn (PqrsMessage $record): string => (string) ($record->subject ?: 'Sin asunto')),
                        Placeholder::make('message')
                            ->label('Mensaje')
                            ->content(fn (PqrsMessage $record): HtmlString => new HtmlString((string) $record->message)),
                    ]),
            ])
            ->emptyStateHeading('Sin respuestas institucionales registradas.');
    }
}
