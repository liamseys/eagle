<?php

use App\Actions\Forms\GenerateFormFields;
use App\Ai\Agents\FormFieldGenerator;

it('normalizes generated fields into form field attributes', function () {
    FormFieldGenerator::fake([[
        'fields' => [
            [
                'type' => 'text',
                'label' => 'First name',
                'description' => '',
                'is_required' => true,
                'options' => [],
            ],
            [
                'type' => 'select',
                'label' => 'Country',
                'description' => 'Where your company is based.',
                'is_required' => false,
                'options' => ['Belgium', 'The Netherlands'],
            ],
        ],
    ]]);

    $fields = app(GenerateFormFields::class)->handle('A customer onboarding form');

    expect($fields)->toHaveCount(2)
        ->and($fields[0])->toBe([
            'type' => 'text',
            'label' => 'First name',
            'description' => null,
            'is_required' => true,
            'is_visible' => true,
            'options' => null,
        ])
        ->and($fields[1]['options'])->toBe([
            'belgium' => 'Belgium',
            'the_netherlands' => 'The Netherlands',
        ])
        ->and($fields[1]['description'])->toBe('Where your company is based.');

    FormFieldGenerator::assertPrompted('A customer onboarding form');
});

it('discards generated fields with an unknown type or a blank label', function () {
    FormFieldGenerator::fake([[
        'fields' => [
            ['type' => 'signature', 'label' => 'Signature', 'description' => '', 'is_required' => false, 'options' => []],
            ['type' => 'text', 'label' => '   ', 'description' => '', 'is_required' => false, 'options' => []],
            ['type' => 'email', 'label' => 'Email', 'description' => '', 'is_required' => true, 'options' => []],
        ],
    ]]);

    $fields = app(GenerateFormFields::class)->handle('A form');

    expect($fields)->toHaveCount(1)
        ->and($fields[0]['type'])->toBe('email');
});

it('falls back to a label-based option for choice fields generated without options', function () {
    FormFieldGenerator::fake([[
        'fields' => [
            [
                'type' => 'checkbox',
                'label' => 'I accept the privacy policy',
                'description' => '',
                'is_required' => true,
                'options' => [],
            ],
        ],
    ]]);

    $fields = app(GenerateFormFields::class)->handle('A form with a privacy policy checkbox');

    expect($fields[0]['options'])->toBe([
        'i_accept_the_privacy_policy' => 'I accept the privacy policy',
    ]);
});

it('returns no fields when the response contains none', function () {
    FormFieldGenerator::fake([['fields' => []]]);

    expect(app(GenerateFormFields::class)->handle('Gibberish'))->toBe([]);
});
