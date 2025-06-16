<?php

use App\Http\Requests\Admin\User\UpdateUserActiveStatusRequest;

it('has required validation rules', function () {
    $request = new UpdateUserActiveStatusRequest;
    $rules = $request->rules();

    expect($rules)->toHaveKey('is_active')
        ->and($rules['is_active'])->toBe(['required', 'boolean']);
});

it('passes validation with valid boolean values', function ($value) {
    $validator = validator(['is_active' => $value], (new UpdateUserActiveStatusRequest)->rules());
    expect($validator->passes())->toBeTrue();
})->with([
    'true boolean' => true,
    'false boolean' => false,
    'integer 1' => 1,
    'integer 0' => 0,
    'string "1"' => '1',
    'string "0"' => '0',
]);

it('fails validation with invalid values', function ($value) {
    $validator = validator(['is_active' => $value], (new UpdateUserActiveStatusRequest)->rules());
    expect($validator->passes())->toBeFalse();
})->with([
    'string' => 'not-a-boolean',
    'null' => null,
    'integer 2' => 2,
]);

it('fails validation when required field is missing', function () {
    $validator = validator([], (new UpdateUserActiveStatusRequest)->rules());
    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->has('is_active'))->toBeTrue();
});

it('fails validation with empty array', function () {
    $validator = validator(['is_active' => []], (new UpdateUserActiveStatusRequest)->rules());
    expect($validator->passes())->toBeFalse();
});

it('has custom validation messages', function () {
    $request = new UpdateUserActiveStatusRequest;
    $messages = $request->messages();

    expect($messages)->toHaveKey('is_active.required')
        ->and($messages)->toHaveKey('is_active.boolean')
        ->and($messages['is_active.required'])->toBe(__('tle-validation.is_active.required'))
        ->and($messages['is_active.boolean'])->toBe(__('tle-validation.is_active.boolean'));
});

it('has exactly the expected validation messages', function () {
    $request = new UpdateUserActiveStatusRequest;
    $messages = $request->messages();

    expect($messages)->toBe([
        'is_active.required' => __('tle-validation.is_active.required'),
        'is_active.boolean' => __('tle-validation.is_active.boolean'),
    ]);
});

it('uses custom message for required validation', function () {
    $request = new UpdateUserActiveStatusRequest;
    $validator = validator([], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->get('is_active')[0])->toBe(__('tle-validation.is_active.required'));
});

it('uses custom message for boolean validation', function () {
    $request = new UpdateUserActiveStatusRequest;
    $validator = validator(['is_active' => 'not-a-boolean'], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->get('is_active')[0])->toBe(__('tle-validation.is_active.boolean'));
});
