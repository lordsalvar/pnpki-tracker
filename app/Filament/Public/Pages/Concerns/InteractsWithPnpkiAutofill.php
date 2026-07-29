<?php

namespace App\Filament\Public\Pages\Concerns;

use App\Services\PnpkiFormExtractionService;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

trait InteractsWithPnpkiAutofill
{
    /**
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    private function documentsStepSchema(): array
    {
        return [
            Section::make('Data privacy (consent / agreement)')
                ->description('Acknowledge this notice before uploading your documents.')
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

            Section::make('ID combination & uploads')
                ->description('Select your ID option, then upload your filled PNPKI form and supporting IDs.')
                ->columns(2)
                ->disabled(fn (Get $get) => ! (bool) $get('data_privacy_consent'))
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
                            'birth_cert_valid_ids' => 'PNPKI form, Birth Cert & 2 Valid IDs',
                            'passport_valid_ids' => 'PNPKI form, Passport & 2 valid IDs',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (?string $state): void {
                            if (blank($state)) {
                                return;
                            }
                            Notification::make()
                                ->title('Include both sides if applicable')
                                ->body('If your ID has a back side, please upload both the front and back for a complete submission.')
                                ->info()
                                ->persistent()
                                ->send();
                        })
                        ->columnSpan(2),

                    FileUpload::make('upload_pnpki')
                        ->label('PNPKI Form')
                        ->helperText('PDF only · Max 5 MB · We read this file to pre-fill the next steps')
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
                        ->visible(fn (Get $get) => filled($get('id_combo')))
                        ->live()
                        ->afterStateUpdated(fn (mixed $state) => $this->autofillFromPnpkiPdf($state)),

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
                        ->visible(fn (Get $get) => $get('id_combo') === 'birth_cert_valid_ids'),

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
                        ->visible(fn (Get $get) => in_array($get('id_combo'), ['passport_only', 'passport_valid_ids'])),

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
                        ->visible(fn (Get $get) => $get('id_combo') === 'umid_only'),

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
                ]),
        ];
    }

    public function autofillFromPnpkiPdf(mixed $state): void
    {
        if (blank($state)) {
            $this->pnpkiAutofilled = false;

            return;
        }

        $pdfPath = $this->resolveUploadedPdfPath($state);

        if ($pdfPath === null) {
            return;
        }

        try {
            $extracted = app(PnpkiFormExtractionService::class)->extract($pdfPath);
            $current = $this->employeeData ?? [];
            $mapped = app(PnpkiFormExtractionService::class)->toFormState(
                $extracted,
                $current['organization'] ?? null,
            );

            $this->employeeData = array_merge($current, $mapped);
            $this->resetValidation();
            $this->pnpkiAutofilled = true;
            $this->dispatch('$refresh');

            $filled = $extracted->filledFieldNames();
            $missing = $this->missingAutofillFields($mapped);

            Notification::make()
                ->title('PNPKI form read successfully')
                ->body(
                    count($filled) > 0
                        ? 'Pre-filled '.count($filled).' field(s) from your PDF. Please review the next steps'
                        .(count($missing) > 0 ? ' and complete: '.implode(', ', $missing).'.' : '.')
                        : 'Please review and complete the remaining fields on the next steps.'
                )
                ->success()
                ->send();
        } catch (ValidationException) {
            $this->pnpkiAutofilled = false;
        } catch (\Throwable $exception) {
            $this->pnpkiAutofilled = false;

            Notification::make()
                ->title('Could not read PNPKI form automatically')
                ->body($exception->getMessage())
                ->warning()
                ->send();
        }
    }

    /**
     * @param  array<string, mixed>  $mapped
     * @return list<string>
     */
    private function missingAutofillFields(array $mapped): array
    {
        $labels = [
            'civil_status' => 'civil status',
            'birth_place_country' => 'country of birth',
            'birth_place_province' => 'province of birth',
            'barangay' => 'barangay (if address was not matched)',
        ];

        $missing = [];

        $state = $this->employeeData ?? [];

        foreach ($labels as $field => $label) {
            if (! array_key_exists($field, $mapped) || blank($mapped[$field])) {
                if (blank($state[$field] ?? null)) {
                    $missing[] = $label;
                }
            }
        }

        return $missing;
    }

    private function resolveUploadedPdfPath(mixed $state): ?string
    {
        if (blank($state)) {
            return null;
        }

        if (is_array($state)) {
            foreach ($state as $item) {
                $path = $this->resolveUploadedPdfPath($item);
                if ($path !== null) {
                    return $path;
                }
            }

            return null;
        }

        // Livewire TemporaryUploadedFile object — getRealPath() uses the configured temp disk.
        if (is_object($state)) {
            foreach (['getRealPath', 'getPathname'] as $method) {
                if (method_exists($state, $method)) {
                    $path = $state->{$method}();
                    if (is_string($path) && is_readable($path)) {
                        return $path;
                    }
                }
            }

            return null;
        }

        if (! is_string($state)) {
            return null;
        }

        // Serialized livewire-file: reference — strip prefix and resolve via temp disk.
        if (str_starts_with($state, 'livewire-file:')) {
            $filename = substr($state, strlen('livewire-file:'));

            return $this->resolveTempFilePath($filename);
        }

        // Absolute path already on disk.
        if (is_readable($state)) {
            return $state;
        }

        // Path relative to the configured Livewire temp disk (public by default).
        $livewireDisk = config('livewire.temporary_file_upload.disk') ?: config('filesystems.default');
        if (Storage::disk($livewireDisk)->exists('livewire-tmp/'.$state)) {
            $path = Storage::disk($livewireDisk)->path('livewire-tmp/'.$state);

            return is_readable($path) ? $path : null;
        }

        // Path relative to the local disk (permanent attachments/).
        if (Storage::disk('local')->exists($state)) {
            $path = Storage::disk('local')->path($state);

            return is_readable($path) ? $path : null;
        }

        return $this->resolveTempFilePath($state);
    }

    private function resolveTempFilePath(string $filename): ?string
    {
        $livewireDisk = config('livewire.temporary_file_upload.disk') ?: config('filesystems.default');
        $path = Storage::disk($livewireDisk)->path('livewire-tmp/'.$filename);
        if (is_readable($path)) {
            return $path;
        }

        // Fallback: app/public/livewire-tmp and app/livewire-tmp
        foreach (['app/public/livewire-tmp/', 'app/livewire-tmp/'] as $segment) {
            $fallback = storage_path($segment.$filename);
            if (is_readable($fallback)) {
                return $fallback;
            }
        }

        return null;
    }

    private function pnpkiAutofillBannerHtml(): HtmlString
    {
        return new HtmlString(
            '<div class="rounded-lg border border-primary-200 bg-primary-50 px-4 py-3 text-sm text-primary-900 dark:border-primary-800 dark:bg-primary-950 dark:text-primary-100">'
            .'<p class="font-medium">Details imported from your PNPKI form</p>'
            .'<p class="mt-1 text-primary-800 dark:text-primary-200">Review each field below and correct anything that does not match your form.</p>'
            .'</div>'
        );
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
            .'<p class="mt-2">By proceeding, you also consent to this portal reading your uploaded PNPKI PDF locally '
            .'to pre-fill the registration form. Uploaded files are stored securely and used only for your application.</p>'
            .'</div>'
        );
    }
}
