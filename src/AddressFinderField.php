<?php

namespace AddyAddressFinder\SilverStripe;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\HiddenField;
use SilverStripe\View\Requirements;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\Forms\ToggleCompositeField;

/**
 * A wrapper for the AddressFinder API.
 *
 * Standard layout is a simple text field with the required javascript events,
 * but as per best practices (http://addressfinder.co.nz/docs/best_practices)
 * this FormField also provides fallback fields in case the user cannot find
 * their address
 */

class AddressFinderField extends TextField
{
    /**
     * @config
     */
    private static $api_key = false;

    /**
     * @config
     */
    private static $include_address_finder_js = true;

    /**
     * @var FieldList
     */
    protected $manualFields;

    /**
     * @var TextField
     */
    protected $addressField;

    /**
     * @var HiddenField
     */
    protected $manualToggle;

    /**
     * @var boolean
     */
    protected $showManualFields = true;

    protected $showLatLngManual = false;

    protected $requireLatLngManual = false;

    /**
     * @param string $name
     * @param string $title
     * @param mixed $value
     */
    public function __construct($name, $title = null, $value = null)
    {
        $this->addressField = TextField::create("{$name}[Address]", $title);
        $this->manualToggle = HiddenField::create("{$name}[ManualAddress]");

        $this->manualFields = FieldList::create([
            FieldGroup::create([
                TextField::create("{$name}[Address1]", "Address1"),
                TextField::create("{$name}[Suburb]", "Suburb"),
                $postcode = NumericField::create("{$name}[Postcode]", "Postcode"),
                TextField::create("{$name}[City]", "City"),
                TextField::create("{$name}[District]", "District"),
                TextField::create("{$name}[Region]", "Region")

            ]),
            ToggleCompositeField::create('Other', 'Other Address Details', [
                TextField::create("{$name}[FullAddress]", "FullAddress"),
                TextField::create("{$name}[Longitude]", "Longitude"),
                TextField::create("{$name}[Latitude]", "Latitude"),
                HiddenField::create("{$name}[Meshblock]", "Meshblock"),
            ]),
        ]);

        $postcode->setHTML5(true);

        $this->setFieldHolderTemplate('Includes/AddressFinderField_holder');

        parent::__construct($name, $title, $value);
    }

    /**
     * @param bool $bool
     *
     * @return $this
     */
    public function setShowLatLngManual($bool)
    {
        $this->showLatLngManual = $bool;

        return $this;
    }

    /**
     * @param bool $bool
     *
     * @return $this
     */
    public function setRequireLatLngManual($bool)
    {
        $this->requireLatLngManual = $bool;
        $this->showLatLngManual = true;

        return $this;
    }


    /**
     * @param bool $bool
     *
     * @return $this
     */
    public function setReadonly($bool)
    {
        parent::setReadonly($bool);

        foreach ($this->manualFields as $field) {
            $field->setReadonly($bool);
        }

        return $this;
    }

    /**
     * @param string $message
     * @param string $messageType
     *
     * @return $this
     */
    public function setError($message, $messageType)
    {
        // show the manual fields
        $this->manualToggle->setValue('1');

        $name = $this->getName();
        $field = $this->manualFields->dataFieldByName("{$name}[{$messageType}]");

        if ($field) {
            $field->setError($message, 'validation');
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function performReadonlyTransformation()
    {
        parent::performReadonlyTransformation();

        $readonly = new FieldList();

        foreach ($this->manualFields as $field) {
            $readonly->push($field->performReadonlyTransformation());
        }

        $this->addressField = $this->addressField->performReadonlyTransformation();
        $this->manualFields = $readonly;

        return clone $this;
    }

    /**
     * @param bool $bool
     *
     * @return $this
     */
    public function setDisabled($bool)
    {
        parent::setDisabled($bool);

        foreach ($this->manualFields as $field) {
            $field->setDisabled($bool);
        }

        return $this;
    }

    /**
     * @param array $properties
     *
     * @return string
     */
    public function FieldHolder($properties = array())
    {
        Requirements::javascript('//cdn.addy.co.nz/addycomplete.min.js');
        Requirements::javascript('//code.jquery.com/jquery-1.11.1.min.js');
        Requirements::javascript('//code.jquery.com/ui/1.11.4/jquery-ui.min.js');

        if (Controller::curr()->hasMethod('ShowSwitchView')) {
            // leftandmain check. If admin then use entwine.
        } else {
            if (Config::inst()->get(AddressFinderField::class, 'include_address_finder_js')) {
                Requirements::javascript('mark-a-j-adriano/silverstripe-addy-finder:client/js/addressfinder.js');
            }
        }

        $fields = $this->getManualFields();

        if ($this->showLatLngManual) {
            $name = $this->getName();

            $longitude = $fields->dataFieldByName("{$name}[Longitude]")->Value();

            $latitude = $fields->dataFieldByName("{$name}[Latitude]")->Value();

            $fields->removeByName("{$name}[Latitude]");
            $fields->removeByName("{$name}[Longitude]");

            $replaceLong = new TextField("{$name}[Longitude]", 'Longitude', $longitude);

            $replaceLat = new TextField("{$name}[Latitude]", 'Latitude', $latitude);

            if ($this->isReadonly()) {
                $replaceLong->setReadonly(true);
                $replaceLat->setReadonly(true);
            }

            $fields->push($replaceLong);
            $fields->push($replaceLat);
        }

        $properties = array(
            'ManualAddressFields' => ($this->showManualFields) ? $fields : null,
            'AddressField' => $this->addressField->Field(),
            'ManualToggleField' => ($this->showManualFields) ? $this->manualToggle : null,
        );

        return parent::FieldHolder($properties);
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return Config::inst()->get(AddressFinderField::class, 'key');
    }

    /**
     * @return FieldList
     */
    public function getManualFields()
    {
        return $this->manualFields;
    }

    /**
     * @param boolean
     *
     * @return $this
     */
    public function setShowManualFields($manual = true)
    {
        $this->showManualFields = $manual;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getShowManualFields()
    {
        return $this->showManualFields;
    }

    /**
     * @return TextField
     */
    public function getAddressField()
    {
        return $this->addressField;
    }

    /**
     * @param array $value
     * @param DataObjectInterface $record
     */
    public function setValue($value, $record = null)
    {
        if ($record && is_object($record)) {
            $this->addressField->setValue($record->{$this->getName()});

            foreach ($this->getManualFields() as $field) {
                $name = $this->getNestedFieldName($field);

                $field->setValue($record->{$name});
            }
        } elseif (is_array($value)) {
            if (isset($value['Address'])) {
                $this->addressField->setValue($value['Address']);
            }

            foreach ($this->getManualFields() as $field) {
                $nested = $this->getNestedFieldName($field);

                if (isset($value[$nested])) {
                    $field->setValue($value[$nested]);
                }
            }
        } elseif (is_string($value)) {
            $this->addressField->setValue($value);
        }
    }

    /**
     * @param SilverStripe\ORM\DataObjectInterface
     */
    public function saveInto(DataObjectInterface $record)
    {
        $record->{$this->getName()} = $this->addressField->Value();

        foreach ($this->getManualFields() as $field) {
            $fieldName = $this->getNestedFieldName($field);

            $record->{$fieldName} = $field->Value();
        }
    }

    /**
     * Returns the actual name of a child field without the prefix of this
     * field.
     *
     * @param FormField $field
     *
     * @return string
     */
    protected function getNestedFieldName($field)
    {
        return substr($field->getName(), strlen($this->getName()) + 1, -1);
    }

    /**
     * @param string $name
     *
     * @return AddressFinderField
     */
    public function setName($name)
    {
        parent::setName($name);

        $this->addressField->setName("{$name}[Address]");

        foreach ($this->getManualFields() as $field) {
            $nested = $this->getNestedFieldName($field);

            $field->setName("{$name}[{$nested}]");
        }

        return $this;
    }

    /**
     * If this field is required then we require at least the first postal line
     * along with the town and postcode. Either this has been manually filled
     * in or, automatically filled in by
     *
     * @param Validator $validator
     *
     * @return bool
     */
    public function validate($validator)
    {
        $name = $this->getName();

        if ($validator->fieldIsRequired($name)) {
            // remove this as a required field as we're doing the checking here.
            $validator->removeRequiredField($name);

            $fields = $this->getManualFields();
            $postal = $fields->dataFieldByName("{$name}[FullAddress]");

            if (!$postal->Value()) {
                $validator->validationError(
                    $name,
                    _t("AddressFinderField.ENTERAVALIDADDRESS", "Please enter a valid address."),
                    'PostalLine1',
                    false
                );

                return false;
            }

            $city = $fields->dataFieldByName("{$name}[City]");
            if (!$city->Value()) {
                $validator->validationError(
                    $name,
                    _t("AddressFinderField.ENTERAVALIDCITY", "Please enter a valid city."),
                    "City",
                    false
                );

                return false;
            }

            $postcode = $fields->dataFieldByName("{$name}[Postcode]");
            if (!$postcode->Value()) {
                $validator->validationError(
                    $name,
                    _t("AddressFinderField.ENTERAVALIDPOSTCODE", "Please enter a valid postcode."),
                    "Postcode",
                    false
                );

                return false;
            }

            if ($this->requireLatLngManual) {
                $lat = $fields->dataFieldByName("{$name}[Latitude]");

                if (!$lat->Value()) {
                    $lat->validationError(
                        $name,
                        _t("AddressFinderField.LATITUDEMISSING", "Please enter a valid Latitude."),
                        "Latitude",
                        false
                    );

                    return false;
                }

                $lng = $fields->dataFieldByName("{$name}[Longitude]");
                if (!$lng->Value()) {
                    $lng->validationError(
                        $name,
                        _t("AddressFinderField.LONGTITUDEMISSING", "Please enter a valid Longitude."),
                        "Longitude",
                        false
                    );

                    return false;
                }
            }
        }

        return true;
    }
}
