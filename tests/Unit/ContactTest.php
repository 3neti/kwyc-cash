<?php

use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use App\Models\Contact;

uses(RefreshDatabase::class, WithFaker::class);

test('contact has attributes', function () {
    $contact = Contact::factory()->create();
    expect($contact->mobile)->toBeString();
    expect($contact->country)->toBeString();
});

test('contact has default properties', function () {
    $contact = Contact::create(['mobile' => '09171234567']);
    expect($contact->country)->toBe(Contact::DEFAULT_COUNTRY);
});
