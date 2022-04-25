<?php

namespace CS\Directory\Models;

use Zend_Locale;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TimeField;
use SilverStripe\Forms\FieldGroup;

class CMDirectoryBusinessEntry extends CMDirectoryBasicEntry
{

    private static $table_name = 'CMDirectoryBusinessEntry';

    private static $db = array(
        // Open days / hours
        'Sunday' => 'Boolean',
        'Sunday_From' => 'Time',
        'Sunday_To' => 'Time',

        'Monday' => 'Boolean',
        'Monday_From' => 'Time',
        'Monday_To' => 'Time',

        'Tuesday' => 'Boolean',
        'Tuesday_From' => 'Time',
        'Tuesday_To' => 'Time',

        'Wednesday' => 'Boolean',
        'Wednesday_From' => 'Time',
        'Wednesday_To' => 'Time',

        'Thursday' => 'Boolean',
        'Thursday_From' => 'Time',
        'Thursday_To' => 'Time',

        'Friday' => 'Boolean',
        'Friday_From' => 'Time',
        'Friday_To' => 'Time',

        'Saturday' => 'Boolean',
        'Saturday_From' => 'Time',
        'Saturday_To' => 'Time',
    );

    protected $translatedDays;

    protected function getTranslatedDays()
    {
        // Check instance cache
        if ($this->translatedDays) {
            return $this->translatedDays;
        }
        $zendLocale = new Zend_Locale;
        $list = $zendLocale->getTranslationList("Days", $this->locale());
        $this->translatedDays = $list['format']['wide'];
        return $this->translatedDays;
    }

    /**
     *
     * @param type $name
     * @return type
     */
    public function TranslatedDay($name)
    {
        $translated = $this->getTranslatedDays();
        return (!empty($translated[$name]) ? $translated[$name] : $name);
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeFieldsFromTab('Root.Main', array_keys($this->stat('db')));

        // Get day fields with translations
        $translatedDays = $this->getTranslatedDays();

        $dayFields = array(
            HeaderField::create('OpeningHours', _t(CMDirectoryBusinessEntry::class . '.BusinessHours', 'Opening hours')),
            FieldGroup::create(
                array(
                    CheckboxField::create('Sunday', $this->TranslatedDay('sun')),
                    TimeField::create('Sunday_From', _t(CMDirectoryBusinessEntry::class . '.OpenFrom', 'From')),
                    TimeField::create('Sunday_To', _t(CMDirectoryBusinessEntry::class . '.OpenTo', 'To')),
                )
            ),
            FieldGroup::create(
                array(
                    CheckboxField::create('Monday', $this->TranslatedDay('mon')),
                    TimeField::create('Monday_From', _t(CMDirectoryBusinessEntry::class . '.OpenFrom', 'From')),
                    TimeField::create('Monday_To', _t(CMDirectoryBusinessEntry::class . '.OpenTo', 'To')),
                )
            ),
            FieldGroup::create(
                array(
                    CheckboxField::create('Tuesday', $this->TranslatedDay('tue')),
                    TimeField::create('Tuesday_From', _t(CMDirectoryBusinessEntry::class . '.OpenFrom', 'From')),
                    TimeField::create('Tuesday_To', _t(CMDirectoryBusinessEntry::class . '.OpenTo', 'To')),
                )
            ),
            FieldGroup::create(
                array(
                    CheckboxField::create('Wednesday', $this->TranslatedDay('wed')),
                    TimeField::create('Wednesday_From', _t(CMDirectoryBusinessEntry::class . '.OpenFrom', 'From')),
                    TimeField::create('Wednesday_To', _t(CMDirectoryBusinessEntry::class . '.OpenTo', 'To')),
                )
            ),

            FieldGroup::create(
                array(
                    CheckboxField::create('Thursday', $this->TranslatedDay('thu')),
                    TimeField::create('Thursday_From', _t(CMDirectoryBusinessEntry::class . '.OpenFrom', 'From')),
                    TimeField::create('Thursday_To', _t(CMDirectoryBusinessEntry::class . '.OpenTo', 'To')),
                )
            ),
            FieldGroup::create(
                array(
                    CheckboxField::create('Friday', $this->TranslatedDay('fri')),
                    TimeField::create('Friday_From', _t(CMDirectoryBusinessEntry::class . '.OpenFrom', 'From')),
                    TimeField::create('Friday_To', _t(CMDirectoryBusinessEntry::class . '.OpenTo', 'To')),
                )
            ),
            FieldGroup::create(
                array(
                    CheckboxField::create('Saturday', $this->TranslatedDay('sat')),
                    TimeField::create('Saturday_From', _t(CMDirectoryBusinessEntry::class . '.OpenFrom', 'From')),
                    TimeField::create('Saturday_To', _t(CMDirectoryBusinessEntry::class . '.OpenTo', 'To')),
                )
            ),
        );

        $fields->addFieldsToTab('Root.' . _t(CMDirectoryBusinessEntry::class . '.OpeningHours', 'Opening Hours'), $dayFields);

        // Remove fields disabled in config
        $this->removeDisabledFields($fields);

        return $fields;
    }
}
