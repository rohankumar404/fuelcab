<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\VendorResource\Pages;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Enums\DocumentStatus;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Companies & Vendors';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'brand_name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([

                // ── Left: Profile ─────────────────────────────────────────
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Business Identity')->schema([
                        Forms\Components\TextInput::make('brand_name')
                            ->label('Business Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('legal_name')
                            ->label('Legal Name')
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('vendor_code')
                            ->label('Vendor Code')
                            ->nullable()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('company_type')
                            ->label('Company Type')
                            ->options([
                                'private_limited' => 'Private Limited',
                                'public_limited'  => 'Public Limited',
                                'llp'             => 'LLP',
                                'partnership'     => 'Partnership',
                                'proprietorship'  => 'Proprietorship',
                                'other'           => 'Other',
                            ])
                            ->nullable(),
                        Forms\Components\Select::make('company_id')
                            ->label('Company')
                            ->options(Company::pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                    ])->columns(2),

                    Forms\Components\Section::make('Compliance')->schema([
                        Forms\Components\TextInput::make('gst_number')
                            ->label('GST Number')
                            ->nullable()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('pan')
                            ->label('PAN')
                            ->nullable()
                            ->maxLength(50),
                    ])->columns(2),

                    Forms\Components\Section::make('Contact Details')->schema([
                        Forms\Components\TextInput::make('contact_person')->nullable()->maxLength(255),
                        Forms\Components\TextInput::make('mobile')->tel()->nullable()->maxLength(50),
                        Forms\Components\TextInput::make('email')->email()->nullable()->maxLength(150),
                    ])->columns(3),

                    Forms\Components\Section::make('Address')->schema([
                        Forms\Components\Textarea::make('registered_address')
                            ->label('Registered Address')
                            ->rows(2)
                            ->nullable()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('operational_address')
                            ->label('Operational Address')
                            ->rows(2)
                            ->nullable()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('city')->nullable()->maxLength(100),
                        Forms\Components\TextInput::make('state')->nullable()->maxLength(100),
                        Forms\Components\TextInput::make('pincode')->nullable()->maxLength(20),
                        Forms\Components\TextInput::make('latitude')->numeric()->nullable(),
                        Forms\Components\TextInput::make('longitude')->numeric()->nullable(),
                    ])->columns(3),
                ])->columnSpan(2),

                // ── Right: Status & Settings ──────────────────────────────
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Vendor Status')->schema([
                        Forms\Components\Select::make('status')
                            ->options(collect(VendorStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                            ->default('pending')
                            ->required(),
                        Forms\Components\Select::make('verification_status')
                            ->options(collect(DocumentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                            ->default('pending')
                            ->required(),
                    ]),

                    Forms\Components\Section::make('Platform Settings')->schema([
                        Forms\Components\Toggle::make('is_first_party')
                            ->label('First-Party (FuelCab Direct)')
                            ->default(false),
                        Forms\Components\TextInput::make('commission_rate')
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('service_radius_meters')
                            ->label('Service Radius (meters)')
                            ->numeric()
                            ->default(5000)
                            ->required(),
                    ]),

                    Forms\Components\Section::make('Internal Notes')->schema([
                        Forms\Components\Textarea::make('internal_notes')
                            ->label('Admin Notes (internal only)')
                            ->rows(4)
                            ->nullable()
                            ->columnSpanFull(),
                    ])->collapsed(),
                ])->columnSpan(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('brand_name')
                    ->label('Business Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor_code')
                    ->label('Vendor Code')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('contact_person')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('mobile')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('city')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('state')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => VendorStatus::Pending->value,
                        'info'    => VendorStatus::UnderReview->value,
                        'success' => VendorStatus::Approved->value,
                        'danger'  => VendorStatus::Rejected->value,
                        'gray'    => VendorStatus::Suspended->value,
                    ]),
                Tables\Columns\BadgeColumn::make('verification_status')
                    ->label('Verification')
                    ->colors([
                        'warning' => DocumentStatus::Pending->value,
                        'success' => DocumentStatus::Verified->value,
                        'danger'  => DocumentStatus::Rejected->value,
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(VendorStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
                Tables\Filters\SelectFilter::make('verification_status')
                    ->options(collect(DocumentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Vendor $record) {
                        $record->update([
                            'status'              => VendorStatus::Approved,
                            'verification_status' => DocumentStatus::Verified,
                        ]);
                        Notification::make()->title('Vendor approved.')->success()->send();
                    })
                    ->visible(fn (Vendor $record) => $record->status !== VendorStatus::Approved),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Vendor $record, array $data) {
                        $record->update([
                            'status'         => VendorStatus::Rejected,
                            'internal_notes' => $data['reason'],
                        ]);
                        Notification::make()->title('Vendor rejected.')->danger()->send();
                    })
                    ->visible(fn (Vendor $record) => $record->status !== VendorStatus::Rejected),

                Tables\Actions\Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Suspension Reason')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (Vendor $record, array $data) {
                        $record->update([
                            'status'         => VendorStatus::Suspended,
                            'internal_notes' => $data['reason'],
                        ]);
                        Notification::make()->title('Vendor suspended.')->warning()->send();
                    })
                    ->visible(fn (Vendor $record) => $record->status === VendorStatus::Approved),

                Tables\Actions\Action::make('reactivate')
                    ->label('Reactivate')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (Vendor $record) {
                        $record->update(['status' => VendorStatus::Approved]);
                        Notification::make()->title('Vendor reactivated.')->success()->send();
                    })
                    ->visible(fn (Vendor $record) => $record->status === VendorStatus::Suspended),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit'   => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}
