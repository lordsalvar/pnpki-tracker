<?php

use App\DataTransferObjects\PnpkiExtractedData;
use App\Services\PnpkiFormExtractionService;

it('maps extracted PNPKI data to public form state', function () {
    $data = new PnpkiExtractedData(
        firstname: 'Wendel Ray',
        middlename: 'Te',
        lastname: 'Ramos',
        suffix: 'N/A',
        sex: 'male',
        birthDate: '1992-08-18',
        tinNumber: '723920009',
        organizationalUnit: 'Office of the Provincial Cooperatives Development Officer',
        houseNo: '1667',
        street: 'Lopez Jaena Extension',
        email: 'wendelrayramos@gmail.com',
        phoneNumber: '09310559237',
        zipCode: '8002',
    );

    $state = app(PnpkiFormExtractionService::class)->toFormState($data, 'PGDS — Provincial Government');

    expect($state['firstname'])->toBe('Wendel Ray')
        ->and($state['lastname'])->toBe('Ramos')
        ->and($state['middlename'])->toBe('Te')
        ->and($state['suffix'])->toBe('N/A')
        ->and($state['sex'])->toBe('male')
        ->and($state['birth_date'])->toBe('1992-08-18')
        ->and($state['tin_number'])->toBe('723920009')
        ->and($state['organization'])->toBe('PGDS — Provincial Government')
        ->and($state['phone_number'])->toBe('09310559237')
        ->and($state['email'])->toBe('wendelrayramos@gmail.com')
        ->and($state['zip_code'])->toBe('8002');
});
