<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Models\Contact;

/**
 * ContactData is a data transfer object (DTO) representing the contact information.
 *
 * This class encapsulates the mobile number and country code of a contact,
 * providing a structured way to transfer contact data between application layers.
 */
class ContactData extends Data
{
    /**
     * @param string $mobile The contact's mobile phone number.
     * @param string $country The country code associated with the contact's mobile number.
     */
    public function __construct(
        public string $mobile,
        public string $country
    ) {}

    /**
     * Creates a ContactData instance from a Contact model.
     *
     * @param Contact $contact The contact model instance.
     * @return ContactData A populated data object with the contact's mobile and country information.
     */
    public static function fromModel(Contact $contact): ContactData
    {
        return new self(
            mobile: $contact->mobile,
            country: $contact->country
        );
    }
}
