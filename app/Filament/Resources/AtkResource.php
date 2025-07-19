<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AtkResource\Pages;
use App\Models\Atk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AtkResource extends Resource
{
    protected static ?string $model = Atk::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?string $modelLabel = 'ATK';
    protected static ?string $pluralModelLabel = 'Daftar ATK';
    protected static ?string $navigationGroup = 'Manajemen Produk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Kolom kategori_atk_id: diizinkan nullable di form
                Forms\Components\Select::make('kategori_atk_id')
                    ->relationship('kategoriAtk', 'nama_kategori')
                    ->nullable()
                    ->searchable()
                    ->preload()
                    ->label('Kategori ATK'),

                // Kolom nama_atk: wajib diisi
                Forms\Components\TextInput::make('nama_atk')
                    ->required()
                    ->maxLength(150)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state)))
                    ->label('Nama ATK'),

                // Kolom slug: wajib diisi dan unik
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(Atk::class, 'slug', ignoreRecord: true)
                    ->maxLength(170)
                    ->label('Slug'),

                // Kolom deskripsi: opsional (nullable)
                Forms\Components\RichEditor::make('deskripsi')
                    ->nullable()
                    ->columnSpanFull()
                    ->label('Deskripsi'),

                // Kolom harga: wajib diisi, numerik, dengan prefix Rp
                Forms\Components\TextInput::make('harga')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->label('Harga'),

                // Kolom stok: wajib diisi, numerik, min 0, default 0
                Forms\Components\TextInput::make('stok')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->label('Stok'),

                // Kolom status_ketersediaan: wajib diisi dengan pilihan tertentu
                Forms\Components\Select::make('status_ketersediaan')
                    ->options([
                        'Tersedia' => 'Tersedia',
                        'Habis' => 'Habis',
                    ])
                    ->required()
                    ->default('Tersedia')
                    ->label('Status Ketersediaan'),

                // Kolom gambar_utama: opsional, gambar, diupload ke Cloudinary
                Forms\Components\FileUpload::make('gambar_utama')
                    ->label('Gambar Utama')
                    ->image()
                    ->disk('cloudinary')
                    ->directory('atk-images')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                Tables\Columns\ImageColumn::make('gambar_utama')
                    ->label('Gambar')
                    ->disk('cloudinary')
                    ->width(80)->height(60)
                    ->defaultImageUrl(url('/images/placeholder.png')),

                Tables\Columns\TextColumn::make('nama_atk')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('kategoriAtk.nama_kategori')
                    ->label('Kategori')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('harga')
                    ->money('IDR')->sortable(),

                Tables\Columns\TextColumn::make('stok')
                    ->numeric()->sortable(),

                Tables\Columns\TextColumn::make('status_ketersediaan')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Tersedia' => 'success',
                        'Habis' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori_atk_id')
                    ->relationship('kategoriAtk', 'nama_kategori')
                    ->label('Filter Kategori'),

                Tables\Filters\SelectFilter::make('status_ketersediaan')
                    ->options([
                        'Tersedia' => 'Tersedia',
                        'Habis' => 'Habis',
                    ])
                    ->label('Filter Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('deleteSelected')
                        ->label('Delete Selected (Hard)')
                        ->action(fn(\Illuminate\Support\Collection $records) => $records->each->delete())
                        ->requiresConfirmation()
                        ->color('danger')
                        ->icon('heroicon-o-trash'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['kategoriAtk']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAtk::route('/'),
            'create' => Pages\CreateAtk::route('/create'),
            'edit' => Pages\EditAtk::route('/{record}/edit'),
        ];
    }
}