<?php

namespace CS\Directory\Models;

use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FieldGroup;

class CMDirectoryPersonEntry extends CMDirectoryBasicEntry
{

    private static $table_name = 'CMDirectoryPersonEntry';

    private static $db = array(
        'MiddleName' => 'Varchar',
        'LastName' => 'Varchar',
    );

    public function getName()
    {
        $name = $this->getField('Name') . ' ' . $this->MiddleName . ' ' . $this->LastName;
        return str_replace('  ', ' ', $name);
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeFieldsFromTab('Root.Main', array_keys($this->config->get('db')));

        $nameFields = array(
            TextField::create('Name', _t(CMDirectoryPersonEntry::class . '.Name', 'First name')),
            TextField::create('MiddleName', _t(CMDirectoryPersonEntry::class . '.MiddleName', 'Middle name or initial')),
            TextField::create('LastName', _t(CMDirectoryPersonEntry::class . '.LastName', 'Last name')),
        );
        $nameGroup = FieldGroup::create(
            _t(CMDirectoryPersonEntry::class . '.NameGroup', 'Name'),
            $nameFields
        );
        $fields->replaceField('Name', $nameGroup);

        // Remove fields disabled in config
        $this->removeDisabledFields($fields);

        return $fields;
    }
}
