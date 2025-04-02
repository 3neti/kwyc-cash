<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use App\Rules\DoesNotMatchAppDomain;

describe('DoesNotMatchAppDomain rule', function () {

    beforeEach(function () {
        Config::set('app.url', 'https://kwyc-cash.test');
    });

    it('passes when domain is different', function () {
        $rule = new DoesNotMatchAppDomain();

        $validator = Validator::make([
            'email' => 'someone@example.com'
        ], [
            'email' => [$rule]
        ]);

        expect($validator->passes())->toBeTrue();
    });

    it('fails when domain matches app domain', function () {
        $rule = new DoesNotMatchAppDomain();

        $validator = Validator::make([
            'email' => '09171234567@kwyc-cash.test'
        ], [
            'email' => [$rule]
        ]);

        expect($validator->fails())->toBeTrue();
    });

    it('ignores non-email strings (let other rules handle)', function () {
        $rule = new DoesNotMatchAppDomain();

        $validator = Validator::make([
            'email' => 'not-an-email'
        ], [
            'email' => ['email', $rule]
        ]);

        expect($validator->fails())->toBeTrue(); // because 'email' rule fails first
    });
});
