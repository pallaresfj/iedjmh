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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Throwable;

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
                TextColumn::make('attachments')
                    ->label('Adjuntos')
                    ->state(fn (PqrsMessage $record): string => static::attachmentsCountLabel($record))
                    ->badge(),
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
                        Placeholder::make('attachments')
                            ->label('Adjuntos')
                            ->content(fn (PqrsMessage $record): HtmlString|string => static::attachmentsHtml($record)),
                    ]),
            ])
            ->emptyStateHeading('Sin respuestas institucionales registradas.');
    }

    private static function attachmentsCountLabel(PqrsMessage $record): string
    {
        $count = collect($record->attachments ?? [])
            ->filter(fn (mixed $attachment): bool => is_array($attachment) && filled($attachment['path'] ?? null))
            ->count();

        if ($count === 0) {
            return '0';
        }

        return $count === 1 ? '1 archivo' : "{$count} archivos";
    }

    private static function attachmentsHtml(PqrsMessage $record): HtmlString|string
    {
        $items = collect($record->attachments ?? [])
            ->filter(fn (mixed $attachment): bool => is_array($attachment) && filled($attachment['path'] ?? null))
            ->map(function (array $attachment, int $index): string {
                $disk = (string) ($attachment['disk'] ?? 'local');
                $path = (string) ($attachment['path'] ?? '');
                $name = (string) ($attachment['name'] ?? "Adjunto {$index}");
                $url = null;

                try {
                    if (Storage::disk($disk)->exists($path)) {
                        $url = Storage::disk($disk)->temporaryUrl($path, now()->addMinutes(30));
                    }
                } catch (Throwable) {
                    $url = null;
                }

                if (filled($url)) {
                    return '<li><a href="'.e($url).'" target="_blank" rel="noopener noreferrer" class="text-primary-600 underline">'.e($name).'</a></li>';
                }

                return '<li>'.e($name).'</li>';
            })
            ->implode('');

        if ($items === '') {
            return 'Sin adjuntos';
        }

        return new HtmlString('<ul class="list-disc space-y-1 pl-4">'.$items.'</ul>');
    }
}
