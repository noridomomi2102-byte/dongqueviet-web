<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvidenceReportResource\Pages;
use App\Models\EvidenceReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EvidenceReportResource extends Resource
{
    protected static ?string $model = EvidenceReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationLabel = 'Báo cáo từ người dân';

    protected static ?string $modelLabel = 'báo cáo';

    protected static ?string $pluralModelLabel = 'Báo cáo';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('reporter_name')->label('Họ tên')->disabled(),
                Forms\Components\TextInput::make('reporter_email')->label('Email')->disabled(),
                Forms\Components\TextInput::make('reporter_phone')->label('SĐT')->disabled(),
                Forms\Components\TextInput::make('category')->label('Loại phản ánh')->disabled(),
                Forms\Components\TextInput::make('source_url')->label('Link nguồn')->disabled()->columnSpanFull(),
                Forms\Components\Textarea::make('description')->label('Nội dung')->disabled()->rows(5)->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái xử lý')
                    ->options([
                        'pending' => 'Chờ xử lý',
                        'processing' => 'Đang xử lý',
                        'resolved' => 'Đã xử lý',
                        'rejected' => 'Từ chối',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('admin_note')->label('Ghi chú nội bộ')->rows(3)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reporter_name')->label('Người gửi')->searchable(),
                Tables\Columns\TextColumn::make('category')->label('Loại')->limit(24)->toggleable(),
                Tables\Columns\TextColumn::make('source_url')->label('Link')->limit(40)->url(fn ($r) => $r->source_url, true),
                Tables\Columns\TextColumn::make('description')->label('Mô tả')->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn (string $s) => match ($s) {
                        'pending' => 'Chờ xử lý',
                        'processing' => 'Đang xử lý',
                        'resolved' => 'Đã xử lý',
                        'rejected' => 'Từ chối',
                        default => $s,
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày gửi')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Trạng thái')->options([
                    'pending' => 'Chờ xử lý',
                    'processing' => 'Đang xử lý',
                    'resolved' => 'Đã xử lý',
                    'rejected' => 'Từ chối',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvidenceReports::route('/'),
            'edit' => Pages\EditEvidenceReport::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
