<?php

namespace App\Filament\Public\Pages;

use App\Enums\Gender;
use App\Models\Address;
use App\Models\Employee;
use App\Models\Form;
use App\Services\PsgcService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class PublicEmployeeForm extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected string $view = 'filament.public.pages.public-employee-form';

    public ?Form $formModel = null;

    public ?array $employeeData = [];

    public bool $submitted = false;

    public static function getRoutePath(Panel $panel): string
    {
        return '/forms/{publicId}';
    }

    public function getTitle(): string|Htmlable
    {
        return $this->formModel?->name ?? 'Employee Registration';
    }

    public function mount(string $publicId): void
    {
        $this->formModel = Form::where('public_id', $publicId)->firstOrFail();
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Personal Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('firstname')
                            ->label('First Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('lastname')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('middlename')
                            ->label('Middle Name')
                            ->required()
                            ->maxLength(255)
                            ->suffixAction(
                                Action::make('set_na_mid')
                                    ->label('N/A')
                                    ->link()
                                    ->color('gray')
                                    ->action(fn (Set $set) => $set('middlename', 'N/A'))
                            ),

                        TextInput::make('suffix')
                            ->label('Suffix')
                            ->placeholder('Jr., Sr., III')
                            ->required()
                            ->maxLength(20)
                            ->suffixAction(
                                Action::make('set_na_suf')
                                    ->label('N/A')
                                    ->link()
                                    ->color('gray')
                                    ->action(fn (Set $set) => $set('suffix', 'N/A'))
                            ),
                    ]),

                Section::make('Contact Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                    ]),

                Section::make('Address')
                    ->columns(2)
                    ->schema([
                        TextInput::make('house_no')
                            ->label('House No.')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('street')
                            ->label('Street')
                            ->required()
                            ->maxLength(255),

                        Select::make('province')
                            ->label('Province')
                            ->options(fn () => app(PsgcService::class)->provinces())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('municipality', null);
                                $set('barangay', null);
                            })
                            ->required(),

                        Select::make('municipality')
                            ->label('City / Municipality')
                            ->options(function (Get $get) {
                                $province = $get('province');
                                if (! $province) {
                                    return [];
                                }

                                return app(PsgcService::class)->municipalities($province);
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('barangay', null))
                            ->disabled(fn (Get $get) => ! $get('province'))
                            ->required(),

                        Select::make('barangay')
                            ->label('Barangay')
                            ->options(function (Get $get) {
                                $municipality = $get('municipality');
                                if (! $municipality) {
                                    return [];
                                }

                                return app(PsgcService::class)->barangays($municipality);
                            })
                            ->searchable()
                            ->live()
                            ->disabled(fn (Get $get) => ! $get('municipality'))
                            ->required(),

                        TextInput::make('zip_code')
                            ->label('ZIP Code')
                            ->numeric()
                            ->minLength(4)
                            ->maxLength(4)
                            ->required(),
                    ]),

                Section::make('Employment Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('organizational_unit')
                            ->label('Organizational Unit')
                            ->required()
                            ->maxLength(255),

                        Select::make('gender')
                            ->label('Gender')
                            ->options(Gender::class)
                            ->required(),

                        TextInput::make('tin_number')
                            ->label('TIN Number')
                            ->required()
                            ->maxLength(20),
                    ]),
            ])
            ->statePath('employeeData');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $rep = $this->formModel->user;

        $address = Address::create([
            'house_no' => $data['house_no'],
            'street' => $data['street'],
            'barangay' => $data['barangay'],
            'municipality' => $data['municipality'],
            'province' => $data['province'],
            'zip_code' => $data['zip_code'],
        ]);

        Employee::create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'middlename' => $data['middlename'],
            'suffix' => $data['suffix'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'organizational_unit' => $data['organizational_unit'],
            'gender' => $data['gender'],
            'tin_number' => $data['tin_number'],
            'address_id' => $address->id,
            'office_id' => $rep->office_id,
        ]);

        $this->submitted = true;
        $this->form->fill();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
