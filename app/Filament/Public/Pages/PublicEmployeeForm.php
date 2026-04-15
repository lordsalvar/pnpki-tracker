<?php

namespace App\Filament\Public\Pages;

use App\Actions\FormSubmission\StorePublicFormSubmissionAction;
use App\Enums\CivilStatus;
use App\Enums\Sex;
use App\Models\EmployeeForm;
use App\Models\FormSubmission;
use App\Services\PsgcService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;

class PublicEmployeeForm extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected string $view = 'filament.public.pages.public-employee-form';

    public ?EmployeeForm $formModel = null;

    public ?array $employeeData = [];

    public bool $submitted = false;

    public ?string $receiptPdfUrl = null;

    public ?string $captchaToken = null;

    public static function getRoutePath(Panel $panel): string
    {
        return '/forms/{publicId}';
    }

    public function getTitle(): string|Htmlable
    {
        return $this->formModel?->name ?? 'Employee Registration';
    }

    /**
     * Omit the Filament page header &lt;h1 class="fi-header-heading"&gt;; title still
     * feeds the document &lt;title&gt; via {@see getTitle()}.
     */
    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public function mount(string $publicId): void
    {
        $this->formModel = EmployeeForm::query()
            ->with('office')
            ->where('public_id', $publicId)
            ->where('is_active', true)
            ->firstOrFail();

        $office = $this->formModel->office;
        $organization = match (true) {
            $office !== null && filled($office->acronym) => "{$office->acronym} — {$office->name}",
            $office !== null => $office->name,
            default => $this->formModel->name ?? 'Employee registration',
        };

        $this->form->fill([
            'organization' => $organization,
            'phone_number' => '09',
        ]);
    }

    public function form(Schema $form): Schema
    {
        $turnstileHtml = new HtmlString(
            '<div class="mt-8 rounded-xl border border-gray-200/80 bg-gray-50/80 p-4 dark:border-gray-700 dark:bg-gray-800/50">'
            .'<p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">Security check</p>'
            .'<div wire:ignore class="flex justify-center sm:justify-start">'
            .'<div class="cf-turnstile" '
            .'data-sitekey="'.e(config('services.turnstile.site_key')).'" '
            .'data-callback="onTurnstileSolved" '
            .'data-expired-callback="onTurnstileExpired" '
            .'data-error-callback="onTurnstileExpired" '
            .'data-theme="auto"></div>'
            .'</div></div>'
        );

        return $form
            ->components([
                Wizard::make([
                    Step::make('Personal')
                        ->description('Data privacy, then your legal name, sex, birth details, and civil status')
                        ->icon(Heroicon::OutlinedUser)
                        ->schema([
                            Section::make('Data privacy (consent / agreement)')
                                ->description('Acknowledge this notice before entering your information below.')
                                ->schema([
                                    Grid::make(12)
                                        ->schema([
                                            Checkbox::make('data_privacy_consent')
                                                ->hiddenLabel()
                                                ->inline(false)
                                                ->accepted()
                                                ->validationMessages([
                                                    'accepted' => "\u{200B}",
                                                ])
                                                ->live()
                                                ->dehydrated(false)
                                                ->columnSpan([
                                                    'default' => 12,
                                                    'sm' => 0.5,
                                                ])
                                                ->extraFieldWrapperAttributes([
                                                    'class' => '!max-w-none w-full sm:max-w-[3.5rem] [&_.fi-fo-field-wrp-error-list]:hidden [&_p.fi-fo-field-wrp-error-message]:hidden [&_div.fi-fo-field-wrp-error-message]:hidden',
                                                ])
                                                ->extraInputAttributes([
                                                    'class' => 'mt-1 size-5 shrink-0 cursor-pointer',
                                                ]),
                                            Html::make($this->dataPrivacyConsentHtml())
                                                ->columnSpan([
                                                    'default' => 12,
                                                    'sm' => 10,
                                                ]),
                                        ]),
                                ]),

                            Section::make('Personal information')
                                ->columns(2)
                                ->disabled(fn (Get $get) => ! (bool) $get('data_privacy_consent'))
                                ->schema([
                                    Select::make('sex')
                                        ->label('Sex')
                                        ->options(Sex::class)
                                        ->required()
                                        ->native(false)
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, Set $set): void {
                                            if (! $this->maidenNameIsApplicable($get)) {
                                                $set('maiden_name', null);
                                            }
                                        }),

                                    Select::make('civil_status')
                                        ->label('Civil Status')
                                        ->options(CivilStatus::class)
                                        ->required()
                                        ->native(false)
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, Set $set): void {
                                            if (! $this->maidenNameIsApplicable($get)) {
                                                $set('maiden_name', null);
                                            }
                                        }),

                                    TextInput::make('firstname')
                                        ->label('First Name')
                                        ->required()
                                        ->rule($this->noEmojiRule())
                                        ->rule($this->noSymbolRule())
                                        ->maxLength(255),

                                    TextInput::make('lastname')
                                        ->label('Last Name')
                                        ->required()
                                        ->rule($this->noEmojiRule())
                                        ->rule($this->noSymbolRule())
                                        ->maxLength(255),

                                    TextInput::make('middlename')
                                        ->label('Middle Name')
                                        ->required()
                                        ->rule($this->noEmojiRule())
                                        ->rule($this->noSymbolRule())
                                        ->maxLength(255)
                                        ->suffixAction(
                                            Action::make('set_na_mid')
                                                ->label('N/A')
                                                ->link()
                                                ->color('gray')
                                                ->action(fn (Set $set) => $set('middlename', 'N/A'))
                                        ),

                                    Select::make('suffix')
                                        ->label('Suffix')
                                        ->options([
                                            'N/A' => 'N/A',
                                            'Jr.' => 'Jr.',
                                            'Sr.' => 'Sr.',
                                            'I' => 'I',
                                            'II' => 'II',
                                            'III' => 'III',
                                            'IV' => 'IV',
                                            'V' => 'V',
                                        ])
                                        ->required()
                                        ->native(false),

                                    TextInput::make('maiden_name')
                                        ->label('Maiden Name')
                                        ->visible(fn (Get $get) => $this->maidenNameIsApplicable($get))
                                        ->required(fn (Get $get) => $this->maidenNameIsApplicable($get))
                                        ->dehydrated(fn (Get $get) => $this->maidenNameIsApplicable($get))
                                        ->default(null)
                                        ->rule($this->noEmojiRule())
                                        ->rule($this->noSymbolRule())
                                        ->maxLength(255),

                                ]),

                            Section::make('Birth information')
                                ->columns(2)
                                ->disabled(fn (Get $get) => ! (bool) $get('data_privacy_consent'))
                                ->schema([
                                    DatePicker::make('birth_date')
                                        ->label('Date of Birth')
                                        ->required()
                                        ->native(false)
                                        ->displayFormat('M d, Y')
                                        ->maxDate(now())
                                        ->minDate(now()->subYears(100))
                                        ->validationMessages([
                                            'max' => 'The date of birth cannot be in the future.',
                                        ]),

                                    TextInput::make('birth_place_country')
                                        ->label('Country of Birth')
                                        ->required()
                                        ->rule($this->noEmojiRule())
                                        ->rule($this->noSymbolRule())
                                        ->maxLength(255),

                                    TextInput::make('birth_place_province')
                                        ->label('Province / State of Birth')
                                        ->required()
                                        ->rule($this->noEmojiRule())
                                        ->rule($this->noSymbolRule())
                                        ->maxLength(255)
                                        ->columnSpanFull(),
                                ]),

                        ]),

                    Step::make('Address & contact')
                        ->description('Residential address and how to reach you')
                        ->icon(Heroicon::OutlinedMapPin)
                        ->schema([
                            Section::make('Residential address')
                                ->description('Current address in the Philippines')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('house_no')
                                        ->label('House No.')
                                        ->required()
                                        ->rule($this->noEmojiRule())
                                        ->rule($this->noSymbolRule())
                                        ->maxLength(255),

                                    TextInput::make('street')
                                        ->label('Street')
                                        ->required()
                                        ->rule($this->noEmojiRule())
                                        ->rule($this->noSymbolRule())
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
                                        ->required()
                                        ->validationMessages([
                                            'regex' => 'numbers should only contain 4 digits',
                                        ])
                                        ->extraInputAttributes([
                                            'inputmode' => 'numeric',
                                            'oninput' => "this.value = this.value.replace(/\\D/g, '').slice(0, 4)",
                                        ]),
                                ]),

                            Section::make('Contact information')
                                ->description('For updates about this registration')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('email')
                                        ->label('Email Address')
                                        ->helperText('Use your active personal email address.')
                                        ->email()
                                        ->required()
                                        ->rule($this->noEmojiRule())
                                        ->maxLength(255),

                                    TextInput::make('phone_number')
                                        ->label('Phone Number')
                                        ->helperText('Use your active personal phone number.')
                                        ->tel()
                                        ->placeholder('e.g. 09171234567')
                                        ->required()
                                        ->rule($this->noEmojiRule())
                                        ->default('09')
                                        ->minLength(11)
                                        ->maxLength(11)
                                        ->rule('regex:/^09\d{9}$/')
                                        ->validationMessages([
                                            'regex' => 'The phone number must start with 09 and be followed by 9 digits (total 11 digits).',
                                        ])
                                        ->extraInputAttributes([
                                            'inputmode' => 'numeric',
                                            'oninput' => "let value = this.value.replace(/\\D/g, ''); if (value.startsWith('09')) { value = '09' + value.slice(2); } else if (value.startsWith('9')) { value = '09' + value.slice(1).replace(/^0+/, ''); } else { value = '09' + value.replace(/^0+/, ''); } this.value = value.slice(0, 11);",
                                        ]),

                                ]),
                        ]),

                    Step::make('Employment')
                        ->description('Organization, your unit, and TIN')
                        ->icon(Heroicon::OutlinedBriefcase)
                        ->columns(2)
                        ->schema([
                            TextInput::make('organization')
                                ->label('Organization')
                                ->disabled()
                                ->dehydrated()
                                ->maxLength(255)
                                ->columnSpanFull(),

                            TextInput::make('organizational_unit')
                                ->label('Organizational Unit')
                                ->required()
                                ->rule($this->noEmojiRule())
                                ->rule($this->noSymbolRule())
                                ->maxLength(255)
                                ->helperText('Your department, division, or station within this organization.')
                                ->columnSpanFull(),

                            TextInput::make('tin_number')
                                ->label('TIN Number')
                                ->required()
                                ->rule($this->noSymbolRule())
                                ->rule($this->noEmojiRule())
                                ->length(9)
                                ->mask('999999999')
                                ->unique(ignoreRecord: true)
                                ->rule(self::noEmojiRule())
                                ->rule(self::noSymbolRule())
                                ->validationMessages([
                                    'regex' => 'The TIN number must be 9 digits (total 9 digits).',
                                ]),
                        ]),

                    Step::make('Documents')
                        ->description('ID option and PDF uploads')
                        ->icon(Heroicon::OutlinedDocumentArrowUp)
                        ->columns(2)
                        ->schema([
                            Select::make('id_combo')
                                ->label('Select ID Combination')
                                ->options([
                                    'national_id' => 'PNPKI form, Philippine National ID (PhilID)',
                                    'passport_only' => 'PNPKI form, Philippine Passport',
                                    'umid_only' => 'PNPKI form, SSS Unified Multi-Purpose ID (UMID)',
                                    'drivers_license_only' => "PNPKI form, LTO Driver's License",
                                    'prc_only' => 'PNPKI form, Professional Regulation Commission (PRC)',
                                    'postal_id_only' => 'PNPKI form, ID Postal Identity Card',
                                    'birth_cert_umid' => 'PNPKI form, Birth Cert & UMID',
                                    'passport_umid' => 'PNPKI form, Passport & UMID',
                                    'birth_cert_valid_ids' => 'PNPKI form, Birth Cert & 2 Valid IDs',
                                    'passport_valid_ids' => 'PNPKI form, Passport & 2 valid IDs',
                                ])
                                ->required()
                                ->live()
                                ->columnSpan(2),

                            FileUpload::make('upload_pnpki')
                                ->label('PNPKI Form')
                                ->helperText('PDF only · Max 5 MB')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->disk('local')
                                ->directory('attachments')
                                ->visibility('private')
                                ->getUploadedFileNameForStorageUsing($this->fileNameForStorage('PNPKI'))
                                ->openable()
                                ->downloadable()
                                ->deletable(fn () => ! $this->submitted)
                                ->previewable()
                                ->uploadingMessage('Uploading PNPKI Form...')
                                ->required()
                                ->columnSpan(2)
                                ->visible(fn (Get $get) => filled($get('id_combo'))),

                            FileUpload::make('upload_national_id')
                                ->label('Philippine National ID')
                                ->helperText('PDF only · Max 5 MB')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->disk('local')
                                ->directory('attachments')
                                ->visibility('private')
                                ->getUploadedFileNameForStorageUsing($this->fileNameForStorage('NationalID'))
                                ->openable()
                                ->downloadable()
                                ->deletable(fn () => ! $this->submitted)
                                ->previewable()
                                ->uploadingMessage('Uploading National ID...')
                                ->required()
                                ->columnSpan(2)
                                ->visible(fn (Get $get) => $get('id_combo') === 'national_id'),

                            FileUpload::make('upload_birth_cert')
                                ->label('Birth Certificate')
                                ->helperText('PDF only · Max 5 MB')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->disk('local')
                                ->directory('attachments')
                                ->visibility('private')
                                ->getUploadedFileNameForStorageUsing($this->fileNameForStorage('BirthCert'))
                                ->openable()
                                ->downloadable()
                                ->deletable(fn () => ! $this->submitted)
                                ->previewable()
                                ->uploadingMessage('Uploading Birth Certificate...')
                                ->required()
                                ->columnSpan(1)
                                ->visible(fn (Get $get) => in_array($get('id_combo'), ['birth_cert_umid', 'birth_cert_valid_ids'])),

                            FileUpload::make('upload_passport')
                                ->label('Passport (Bio-data page)')
                                ->helperText('PDF only · Max 5 MB')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->disk('local')
                                ->directory('attachments')
                                ->visibility('private')
                                ->getUploadedFileNameForStorageUsing($this->fileNameForStorage('Passport'))
                                ->openable()
                                ->downloadable()
                                ->deletable(fn () => ! $this->submitted)
                                ->previewable()
                                ->uploadingMessage('Uploading Passport...')
                                ->required()
                                ->columnSpan(1)
                                ->visible(fn (Get $get) => in_array($get('id_combo'), ['passport_only', 'passport_umid', 'passport_valid_ids'])),

                            FileUpload::make('upload_umid')
                                ->label('UMID Card')
                                ->helperText('PDF only · Max 5 MB')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->disk('local')
                                ->directory('attachments')
                                ->visibility('private')
                                ->getUploadedFileNameForStorageUsing($this->fileNameForStorage('UMID'))
                                ->openable()
                                ->downloadable()
                                ->deletable(fn () => ! $this->submitted)
                                ->previewable()
                                ->uploadingMessage('Uploading UMID...')
                                ->required()
                                ->columnSpan(1)
                                ->visible(fn (Get $get) => in_array($get('id_combo'), ['umid_only', 'birth_cert_umid', 'passport_umid'])),

                            FileUpload::make('upload_drivers_license')
                                ->label("LTO Driver's License")
                                ->helperText('PDF only · Max 5 MB')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->disk('local')
                                ->directory('attachments')
                                ->visibility('private')
                                ->getUploadedFileNameForStorageUsing($this->fileNameForStorage('DriversLicense'))
                                ->openable()
                                ->downloadable()
                                ->deletable(fn () => ! $this->submitted)
                                ->previewable()
                                ->uploadingMessage("Uploading Driver's License...")
                                ->required()
                                ->columnSpan(2)
                                ->visible(fn (Get $get) => $get('id_combo') === 'drivers_license_only'),

                            FileUpload::make('upload_prc_id')
                                ->label('PRC ID')
                                ->helperText('PDF only · Max 5 MB')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->disk('local')
                                ->directory('attachments')
                                ->visibility('private')
                                ->getUploadedFileNameForStorageUsing($this->fileNameForStorage('PRCID'))
                                ->openable()
                                ->downloadable()
                                ->deletable(fn () => ! $this->submitted)
                                ->previewable()
                                ->uploadingMessage('Uploading PRC ID...')
                                ->required()
                                ->columnSpan(2)
                                ->visible(fn (Get $get) => $get('id_combo') === 'prc_only'),

                            FileUpload::make('upload_postal_id')
                                ->label('Postal ID')
                                ->helperText('PDF only · Max 5 MB')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->disk('local')
                                ->directory('attachments')
                                ->visibility('private')
                                ->getUploadedFileNameForStorageUsing($this->fileNameForStorage('PostalID'))
                                ->openable()
                                ->downloadable()
                                ->deletable(fn () => ! $this->submitted)
                                ->previewable()
                                ->uploadingMessage('Uploading Postal ID...')
                                ->required()
                                ->columnSpan(2)
                                ->visible(fn (Get $get) => $get('id_combo') === 'postal_id_only'),

                            FileUpload::make('upload_id1')
                                ->label('Valid ID #1')
                                ->helperText('PDF only · Max 5 MB')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->disk('local')
                                ->directory('attachments')
                                ->visibility('private')
                                ->getUploadedFileNameForStorageUsing($this->fileNameForStorage('ID1'))
                                ->openable()
                                ->downloadable()
                                ->deletable(fn () => ! $this->submitted)
                                ->previewable()
                                ->uploadingMessage('Uploading Valid ID #1...')
                                ->required()
                                ->columnSpan(1)
                                ->visible(fn (Get $get) => in_array($get('id_combo'), ['birth_cert_valid_ids', 'passport_valid_ids'])),

                            FileUpload::make('upload_id2')
                                ->label('Valid ID #2')
                                ->helperText('PDF only · Max 5 MB')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120)
                                ->disk('local')
                                ->directory('attachments')
                                ->visibility('private')
                                ->getUploadedFileNameForStorageUsing($this->fileNameForStorage('ID2'))
                                ->openable()
                                ->downloadable()
                                ->deletable(fn () => ! $this->submitted)
                                ->previewable()
                                ->uploadingMessage('Uploading Valid ID #2...')
                                ->required()
                                ->columnSpan(1)
                                ->visible(fn (Get $get) => in_array($get('id_combo'), ['birth_cert_valid_ids', 'passport_valid_ids'])),

                            Html::make($turnstileHtml)
                                ->columnSpanFull(),
                        ]),
                ])
                    ->label(__('Registration steps'))
                    ->contained(true)
                    ->alpineSubmitHandler('$wire.submit()')
                    ->nextAction(fn (Action $action) => $action
                        ->label(__('Next'))
                        ->icon(Heroicon::OutlinedArrowRight)
                        ->iconPosition(IconPosition::After))
                    ->previousAction(fn (Action $action) => $action
                        ->label(__('Back')))
                    ->submitAction(
                        Action::make('submit')
                            ->label(__('Submit registration'))
                            ->action('submit')
                            ->icon(Heroicon::OutlinedCheckBadge)
                            ->size(Size::Large)
                    ),
            ])
            ->statePath('employeeData');
    }

    public function submit(): void
    {
        if (! $this->verifyCaptcha()) {
            Notification::make()
                ->title('CAPTCHA verification failed. Please try again.')
                ->danger()
                ->send();

            $this->captchaToken = null;

            return;
        }

        $data = $this->form->getState();

        $rep = $this->formModel->user;

        // Duplicate check BEFORE transaction (prevents orphaned address rows)
        $alreadySubmitted = FormSubmission::where('office_id', $rep->office_id)
            ->where('firstname', $data['firstname'])
            ->where('birth_date', $data['birth_date'])
            ->exists();

        if ($alreadySubmitted) {
            Notification::make()
                ->title('Duplicated Submission Error')
                ->body('A submission with this name and birth date already exists for this office.')
                ->warning()
                ->send();

            return;
        }

        try {
            $formSubmission = app(StorePublicFormSubmissionAction::class)->execute(
                $this->formModel,
                $rep,
                $data
            );
        } catch (UniqueConstraintViolationException) {
            Notification::make()
                ->title('Duplicate Submission Error')
                ->body('A submission with this name and birth date already exists for this office.')
                ->warning()
                ->send();

            return;
        }

        if ($formSubmission === null) {
            Notification::make()
                ->title('Something went wrong. Please try again.')
                ->danger()
                ->send();

            return;
        }
        $this->formModel->user->notify(
            new \App\Notifications\NewFormSubmissionNotification($formSubmission)
        );

        // Generate a signed URL (expires in 5 minutes)
        $downloadUrl = URL::temporarySignedRoute(
            'submission.download-pdf',
            now()->addMinutes(5),
            ['submission_id' => $formSubmission->id]
        );

        $this->receiptPdfUrl = $downloadUrl;
        $this->submitted = true;
        $this->form->fill();
    }

    public function downloadReceiptCopy(): void
    {
        if (blank($this->receiptPdfUrl)) {
            return;
        }

        $this->js('window.open('.Js::from($this->receiptPdfUrl).', "_blank")');
    }

    private function verifyCaptcha(): bool
    {
        if (blank($this->captchaToken)) {
            return false;
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret'),
            'response' => $this->captchaToken,
            'remoteip' => request()->ip(),
        ]);

        return (bool) $response->json('success');
    }

    private function fileNameForStorage(string $type): \Closure
    {
        return function (Get $get, $file) use ($type) {
            $office = $this->formModel?->user?->office;
            $officeFolder = $office
                ? str($office->acronym ?? $office->name)->slug()
                : 'unknown-office';

            $firstname = str($get('firstname') ?? 'Unknown')->slug();
            $lastname = str($get('lastname') ?? 'Employee')->slug();
            $employeeFolder = "{$lastname}-{$firstname}";

            $extension = $file->getClientOriginalExtension();
            $filename = str($lastname)->upper()."_{$type}.{$extension}";

            return "{$officeFolder}/Employees/{$employeeFolder}/{$filename}";
        };
    }

    private function dataPrivacyConsentHtml(): HtmlString
    {
        return new HtmlString(
            '<div class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">'
            .'<p class="mb-2 font-semibold text-gray-900 dark:text-gray-100">Data Privacy (Consent/Agreement)</p>'
            .'<p>I hereby authorize the Department of Information and Communications Technology (DICT) and recognize '
            .'their responsibilities under the Republic Act No. 10173, also known as the Data Privacy Act of 2012, '
            .'with respect to the data they collect, record, organize, update, use, consolidate or destruct from '
            .'PNPKI applicants. The personal data obtained from this portal is entered and stored within the DICT '
            .'authorized information and communications system and will only be accessed by the PNPKI RA Officers. '
            .'The DICT have instituted appropriate organizational, technical and physical security measures to ensure '
            .'the protection of the PNPKI applicants personal data.</p>'
            .'</div>'
        );
    }

    private function maidenNameIsApplicable(Get $get): bool
    {
        $sex = $get('sex');
        $civilStatus = $get('civil_status');

        $isFemale = $sex instanceof Sex
            ? $sex === Sex::Female
            : $sex === Sex::Female->value;

        $isMarried = $civilStatus instanceof CivilStatus
            ? $civilStatus === CivilStatus::Married
            : $civilStatus === CivilStatus::Married->value;

        return $isFemale && $isMarried;
    }

    private function noEmojiRule(): string
    {
        return 'not_regex:/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{200D}\x{FE0F}]/u';
    }

    private function noSymbolRule(): string
    {
        return 'regex:/^[\pL\pN\s.,\/-]+$/u';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
