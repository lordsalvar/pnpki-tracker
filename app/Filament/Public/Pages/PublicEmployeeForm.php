<?php

namespace App\Filament\Public\Pages;

use App\Enums\Gender;
use App\Models\Address;
use App\Models\Attachment;
use App\Models\EmployeeForm;
use App\Models\FormSubmission;
use App\Services\AttachmentPathService;
use App\Services\AttachmentRuleService;
use App\Services\PsgcService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PublicEmployeeForm extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected string $view = 'filament.public.pages.public-employee-form';

    public ?EmployeeForm $formModel = null;

    public ?array $employeeData = [];

    public bool $submitted = false;

    public ?string $captchaToken = null;

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
        $this->formModel = EmployeeForm::where('public_id', $publicId)->firstOrFail();
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
                            ->rule($this->noEmojiRule())
                            ->maxLength(255),

                        TextInput::make('lastname')
                            ->label('Last Name')
                            ->required()
                            ->rule($this->noEmojiRule())
                            ->maxLength(255),

                        TextInput::make('middlename')
                            ->label('Middle Name')
                            ->required()
                            ->rule($this->noEmojiRule())
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
                            ->rule($this->noEmojiRule())
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
                            ->rule($this->noEmojiRule())
                            ->maxLength(255),

                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->tel()
                            ->required()
                            ->rule($this->noEmojiRule())
                            ->maxLength(20),
                    ]),

                Section::make('Address')
                    ->columns(2)
                    ->schema([
                        TextInput::make('house_no')
                            ->label('House No.')
                            ->required()
                            ->rule($this->noEmojiRule())
                            ->maxLength(255),

                        TextInput::make('street')
                            ->label('Street')
                            ->required()
                            ->rule($this->noEmojiRule())
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
                            ->rule($this->noEmojiRule())
                            ->maxLength(255),

                        Select::make('gender')
                            ->label('Gender')
                            ->options(Gender::class)
                            ->required(),

                        TextInput::make('tin_number')
                            ->label('TIN Number')
                            ->required()
                            ->rule($this->noEmojiRule())
                            ->maxLength(20),
                    ]),

                Section::make('Document Attachments')
                    ->columns(2)
                    ->schema([
                        Select::make('id_combo')
                            ->label('Select ID Combination')
                            ->options([
                                'national_id' => 'PNPKI form & National ID',
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
                            ->deletable(false)
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
                            ->deletable(false)
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
                            ->deletable(false)
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
                            ->deletable(false)
                            ->previewable()
                            ->uploadingMessage('Uploading Passport...')
                            ->required()
                            ->columnSpan(1)
                            ->visible(fn (Get $get) => in_array($get('id_combo'), ['passport_umid', 'passport_valid_ids'])),

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
                            ->deletable(false)
                            ->previewable()
                            ->uploadingMessage('Uploading UMID...')
                            ->required()
                            ->columnSpan(1)
                            ->visible(fn (Get $get) => in_array($get('id_combo'), ['birth_cert_umid', 'passport_umid'])),

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
                            ->deletable(false)
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
                            ->deletable(false)
                            ->previewable()
                            ->uploadingMessage('Uploading Valid ID #2...')
                            ->required()
                            ->columnSpan(1)
                            ->visible(fn (Get $get) => in_array($get('id_combo'), ['birth_cert_valid_ids', 'passport_valid_ids'])),

                    ]),
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
        $data = $this->normalizeUploadedAttachmentPaths($data);

        $rep = $this->formModel->user;

        DB::transaction(function () use ($data, $rep) {
            $address = Address::create([
                'house_no' => $data['house_no'],
                'street' => $data['street'],
                'barangay' => $data['barangay'],
                'municipality' => $data['municipality'],
                'province' => $data['province'],
                'zip_code' => $data['zip_code'],
            ]);

            $formSubmission = FormSubmission::create([
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
                'form_id' => $this->formModel->id,
                'status' => 'pending',
            ]);

            $this->saveAttachments($formSubmission, $data);
        });

        $this->submitted = true;
        $this->form->fill();
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

    private function saveAttachments(FormSubmission $formSubmission, array $data): void
    {
        $ruleService = $this->ruleService();
        $fileKeys = $ruleService->activeFieldsForCombo($data['id_combo'] ?? null);

        foreach ($ruleService->allFields() as $field) {
            if (! in_array($field, $fileKeys, true)) {
                continue;
            }

            $type = $ruleService->fileTypeForField($field);

            if ($type === null) {
                continue;
            }

            if (! empty($data[$field])) {
                $path = is_array($data[$field]) ? array_values($data[$field])[0] : $data[$field];

                Attachment::create([
                    'form_submission_id' => $formSubmission->id,
                    'file_type' => $type,
                    'file_name' => basename($path),
                    'file_path' => $path,
                ]);
            }
        }
    }

    private function normalizeUploadedAttachmentPaths(array $data): array
    {
        $ruleService = $this->ruleService();
        $pathService = $this->pathService();
        $activeFields = $ruleService->activeFieldsForCombo($data['id_combo'] ?? null);

        if ($activeFields === []) {
            return $data;
        }

        $disk = Storage::disk('local');
        $targetDirectory = $this->attachmentDirectoryFor($data);
        $lastnameToken = $pathService->lastnameToken((string) ($data['lastname'] ?? 'Employee'), 'employee');

        foreach ($activeFields as $field) {
            $sourcePath = $pathService->normalizeFilePath($data[$field] ?? null);

            if (! is_string($sourcePath) || $sourcePath === '') {
                continue;
            }

            $fileType = $ruleService->fileTypeForField($field) ?? 'FILE';
            $targetPath = $pathService->canonicalPath($sourcePath, $targetDirectory, $lastnameToken, $fileType);

            if ($sourcePath !== $targetPath && $disk->exists($sourcePath)) {
                $disk->makeDirectory($targetDirectory);

                if ($disk->exists($targetPath)) {
                    $disk->delete($targetPath);
                }

                $disk->move($sourcePath, $targetPath);
            }

            $data[$field] = is_array($data[$field]) ? [$targetPath] : $targetPath;
        }

        return $data;
    }

    private function attachmentDirectoryFor(array $data): string
    {
        $office = $this->formModel?->user?->office;
        $officeFolder = $office
            ? str($office->acronym ?? $office->name)->slug()
            : 'unknown-office';

        return $this->pathService()->employeeDirectory(
            $officeFolder,
            (string) ($data['firstname'] ?? 'Unknown'),
            (string) ($data['lastname'] ?? 'Employee')
        );
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

    private function ruleService(): AttachmentRuleService
    {
        return app(AttachmentRuleService::class);
    }

    private function pathService(): AttachmentPathService
    {
        return app(AttachmentPathService::class);
    }

    private function noEmojiRule(): string
    {
        return 'not_regex:/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{200D}\x{FE0F}]/u';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
