<?php

use App\Services\PnpkiFormExtractionService;

it('extracts DICT-PNPKI-FO-001 fillable PDF acroform fields', function () {
    $pdfPath = base_path('RAMOS_PNPKI.pdf');

    if (! is_readable($pdfPath)) {
        test()->markTestSkipped('RAMOS_PNPKI.pdf sample not available.');
    }

    $extracted = app(PnpkiFormExtractionService::class)->extract($pdfPath);
    $state = app(PnpkiFormExtractionService::class)->toFormState($extracted);

    expect($extracted->lastname)->toBe('Ramos')
        ->and($extracted->firstname)->toBe('Wendel Ray')
        ->and($extracted->middlename)->toBe('Te')
        ->and($extracted->sex)->toBe('male')
        ->and($extracted->birthDate)->toBe('1992-08-18')
        ->and($extracted->tinNumber)->toBe('723920009')
        ->and($extracted->email)->toBe('wendelrayramos@gmail.com')
        ->and($extracted->phoneNumber)->toBe('09310559237')
        ->and($state['province'])->not->toBeNull();
});
