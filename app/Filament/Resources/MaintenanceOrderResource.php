<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceOrderResource\Pages;
use App\Filament\Resources\MaintenanceOrderResource\RelationManagers;
use App\Models\MaintenanceOrder;
use App\Enums\UserRole;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Enums\MaintenanceOrderPriority;
use App\Enums\MaintenanceOrderStatus;
use Filament\Tables\Columns\BadgeColumn;

class MaintenanceOrderResource extends Resource
{
    protected static ?string $model = MaintenanceOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->disabled(auth()->user()->role === UserRole::Technician),
                Select::make('asset_id')
                    ->relationship('asset', 'name')
                    ->searchable()
                    ->required()
                    ->disabled(auth()->user()->role === UserRole::Technician),
                Select::make('priority')
                    ->options(MaintenanceOrderPriority::options())
                    ->required()
                    ->disabled(auth()->user()->role === UserRole::Technician),
                Select::make('assigned_technician_id')
                    ->label('Technician')
                    ->relationship('assignedTechnician', 'name')
                    ->searchable()
                    ->required()
                    ->disabled(auth()->user()->role === UserRole::Technician),
                Textarea::make('rejection_reason')      
                    ->required(fn ($get) => $get('status') === MaintenanceOrderStatus::Rejected->value)
                    ->visible(fn ($get) => $get('status') === MaintenanceOrderStatus::Rejected->value)
                    ->disabled(auth()->user()->role === UserRole::Technician),
                    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('asset.name'),
                BadgeColumn::make('priority')
                    ->getStateUsing(fn ($record) => $record->priority?->value)
                    ->formatStateUsing(fn ($state) => MaintenanceOrderPriority::from($state)->label())
                    ->color(fn ($state) => MaintenanceOrderPriority::from($state)->color()),
                TextColumn::make('assignedTechnician.name'),
                BadgeColumn::make('status')
                    ->getStateUsing(fn ($record) => $record->status?->value)
                    ->formatStateUsing(fn ($state) => MaintenanceOrderStatus::from($state)->label())
                    ->color(fn ($state) => MaintenanceOrderStatus::from($state)->color()),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(MaintenanceOrderStatus::options())
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListMaintenanceOrders::route('/'),
            'create' => Pages\CreateMaintenanceOrder::route('/create'),
            'edit' => Pages\EditMaintenanceOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->role === UserRole::Technician) {
            $query->where('assigned_technician_id', auth()->id());
            $query->orderByRaw("CASE priority
                                    WHEN 'high' THEN 1
                                    WHEN 'medium' THEN 2
                                    WHEN 'low' THEN 3
                                    ELSE 4 -- This handles any other priority values
                                END");
        }

        return $query;
    }
}
