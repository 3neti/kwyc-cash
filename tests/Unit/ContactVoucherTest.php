<?php

use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Models\{Contact, User};

uses(RefreshDatabase::class, WithFaker::class);

it('allows a voucher to attach contact as an entity', function () {
    $user = User::factory()->create();
    $voucher = Vouchers::withOwner($user)->create();
//    $contact = Contact::factory()->create();
    Contact::create(['mobile' => '09171234567']);
    $contact = Contact::firstOrCreate(['mobile' => '09171234567']);
    $contact = Contact::firstOrCreate(['mobile' => '09171234567']);
    $entities = compact('contact');
    if ($voucher instanceof Voucher) {
        $voucher->addEntities(...$entities);
    }
    $contact2 = $voucher->getEntities(Contact::class)->first();
    expect($contact2->is($contact))->toBeTrue();
});
