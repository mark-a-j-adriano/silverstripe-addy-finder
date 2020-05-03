# AddressFinder for SilverStripe


## Requirements
 * SilverStripe 4.0 or higher

## Overview

This module provides a custom `AddressFinderField` which implements the
javascript AddressFinder widget ([1](https://www.addy.co.nz/address-postcode-finder-integrations))

To get started, sign up for an account at
[https://www.addy.co.nz/pricing](https://www.addy.co.nz/pricing) and set your
AddressFinder key values via the Config system

*mysite/_config/addressfinder.yml*
```
mark-a-j-adriano\SilverStripe\AddressFinderField:
  key: 123
```

Then add an instance of `AddressFinderField` to your form fields

```
use mark-a-j-adriano\SilverStripe\AddressFinderField;

$fields->push(new AddressFinderField('Address'));
```

This will provide your form with a single text box that provides an autocomplete
dropdown as well as a toggle for the user to enter a manual address in the event
the API is unaccessible.

The form field provides the saveInto logic for automatically saving into a
DataObject model if defined. The fields that the module will save too (if in the
database) are

* Address *single line representation, should be the name of your field*
* Address1
* Suburb
* Postcode
* City
* District
* Region
* FullAddress
* Longitude
* Latitude
* Meshblock

An example model which will capture all the information from AddressFinder is
outlined below:

```php
<?php

use SilverStripe\ORM\DataObject;

class AddressObject extends DataObject
{
	private static $db = [
		'Address'       =>  'Text',
		'Address1'      =>  'Varchar(200)',
		'Suburb'        =>  'Varchar(200)',
		'Postcode'      =>  'Varchar(200)',
		'City'          =>  'Varchar(200)',
		'District'      =>  'Varchar(200)',
		'Region'        =>  'Varchar(200)',
		'FullAddress'   =>  'Varchar(200)',
		'Longitude'     =>  'Varchar(200)',
		'Latitude'      =>  'Varchar(200)',
		'Meshblock'     =>  'Varchar(200)'
	];
}
```

