<?php

namespace CS\Directory\Models;

use SilverStripe\Forms\TextField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Control\Email\Email;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Security\Security;
use SilverStripe\i18n\i18n;

class CMDirectoryBasicEntry extends CMDirectoryEntry
{

    private static $table_name = 'CMDirectoryBasicEntry';
    
    private static $db = array(
        'Phone' => 'Varchar(30)',
        'Email' => 'Varchar(100)',
        'Website' => 'Varchar',

        // Physical address
        'AddressLine1' => 'Varchar',
        'AddressLine2' => 'Varchar',
        'Suburb' => 'Varchar',
        'City' => 'Varchar',
        'State' => 'Varchar',
        'Country' => 'Varchar(2)',

        // Mailing address
        'MailingAddressLine1' => 'Varchar',
        'MailingAddressLine2' => 'Varchar',
        'MailingSuburb' => 'Varchar',
        'MailingCity' => 'Varchar',
        'MailingState' => 'Varchar',
        'MailingCountry' => 'Varchar(2)',
        'MailingPostCode' => 'Varchar(16)',

    );

    /**
     * @config
     * @var array
     */
    private static $disabled_fields = array();

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        /*
         * Main fields
         */

        $fields->addFieldsToTab('Root.Main', array(
            // Basic details
            TextField::create('Name', _t(CMDirectoryBasicEntry::class . '.Name', 'Name')),
            NumericField::create('Phone', _t(CMDirectoryBasicEntry::class . '.Phone', 'Phone'), '', '', ''),
            EmailField::create(Email::class, _t('CMDirectoryBasicEntry.Email', Email::class)),
            TextField::create('Website', _t(CMDirectoryBasicEntry::class . '.Website', 'Website')),

            // Physical Address
            TextField::create('AddressLine1', _t(CMDirectoryBasicEntry::class . '.AddressLine1', 'Address line 1')),
            TextField::create('AddressLine2', _t(CMDirectoryBasicEntry::class . '.AddressLine2', 'Address line 2')),
            TextField::create('Suburb', _t(CMDirectoryBasicEntry::class . '.Suburb', 'Suburb')),
            TextField::create('City', _t(CMDirectoryBasicEntry::class . '.City', 'City')),
            TextField::create('State', _t(CMDirectoryBasicEntry::class . '.State', 'State')),
            DropdownField::create('Country', _t(CMDirectoryBasicEntry::class . '.Country', 'Country')),

            // Mailing adddress
            TextField::create('MailingAddressLine1', _t(CMDirectoryBasicEntry::class . '.MailingAddressLine1', 'Address line 1')),
            TextField::create('MailingAddressLine2', _t(CMDirectoryBasicEntry::class . '.MailingAddressLine2', 'Address line 2')),
            TextField::create('MailingSuburb', _t(CMDirectoryBasicEntry::class . '.MailingSuburb', 'Suburb')),
            TextField::create('MailingCity', _t(CMDirectoryBasicEntry::class . '.MailingCity', 'City')),
            TextField::create('MailingState', _t(CMDirectoryBasicEntry::class . '.MailingState', 'State')),
            DropdownField::create('MailingCountry', _t(CMDirectoryBasicEntry::class . '.MailingCountry', 'Country')),
            TextField::create('MailingPostCode', _t(CMDirectoryBasicEntry::class . '.MailingPostCode', 'PostCode')),

        ));
        // Heading fields
        $fields->addFieldToTab('Root.Main', HeaderField::create('BasicHeading', _t(CMDirectoryBasicEntry::class . '.BasicHeading', 'Basic details')), 'Name');
        $fields->addFieldToTab('Root.Main', HeaderField::create('AddressHeading', _t(CMDirectoryBasicEntry::class . '.AddressHeading', 'Physical address')), 'AddressLine1');
        $fields->addFieldToTab('Root.Main', HeaderField::create('MailingAddressHeading', _t(CMDirectoryBasicEntry::class . '.MailingAddressHeading', 'Mailing address')), 'MailingAddressLine1');

        // Remove fields disabled in config
        $this->removeDisabledFields($fields);

        return $fields;
    }

    /*
    public function Link() {
    return $this->Directory()->Link('browse/'.$this->ID);
    }
     *
     */

    public function WebsiteLink()
    {
        $url = $this->Website;
        if (strpos($url, 'http') === false) {
            $url = 'http://' . $url;
        }
        return $url;
    }

    protected function locale()
    {
        if (($member = Security::getCurrentUser()) && $member->Locale) {
            return $member->Locale;
        }

        return i18n::get_locale();
    }
}
