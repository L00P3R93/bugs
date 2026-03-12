<?php

namespace App\Faker\Providers;

use Faker\Provider\Base;

class KenyaProvider extends Base
{
    protected static $counties = [
        'Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Kiambu', 'Machakos', 'Murang\'a', 'Nyeri',
        'Uasin Gishu', 'Kakamega', 'Kajiado', 'Meru', 'Embu', 'Kericho', 'Bungoma', 'Homabay',
        'Siaya', 'Migori', 'Busia', 'Kisii', 'Nyamira', 'Kirinyaga', 'Tharaka Nithi', 'Laikipia',
        'Isiolo', 'Marsabit', 'Garissa', 'Wajir', 'Mandera', 'Turkana', 'West Pokot', 'Samburu',
        'Baringo', 'Elgeyo Marakwet', 'Nandi', 'Trans Nzoia', 'Vihiga', 'Taita Taveta', 'Kwale',
        'Kilifi', 'Lamu', 'Tana River',
    ];

    protected static $nairobiEstates = [
        'Westlands', 'Kileleshwa', 'Karen', 'Rongai', 'Donholm', 'Embakasi',
        'South B', 'South C', 'Langata', 'Kibera', 'Parklands', 'Buruburu',
        'Eastleigh', 'Kasarani', 'Roysambu', 'Kawangware', 'Dagoretti',
    ];

    protected static $countryPhonePrefixes = ['254', '0'];

    protected static $safaricomPrefixes = ['70', '71', '72', '74', '79', '10', '11', '12'];

    protected static $airtelPrefixes = ['73', '75', '78', '73', '75', '78'];

    protected static $telkomPrefixes = ['77', '76'];

    public function county()
    {
        return static::randomElement(static::$counties);
    }

    public function nairobiEstate()
    {
        return static::randomElement(static::$nairobiEstates);
    }

    public function safaricomPhone(): string
    {
        return static::randomElement(static::$countryPhonePrefixes).static::randomElement(static::$safaricomPrefixes).$this->generator->numberBetween(1000000, 9999999);
    }

    public function airtelPhone(): string
    {
        return static::randomElement(static::$countryPhonePrefixes).static::randomElement(static::$airtelPrefixes).$this->generator->numberBetween(1000000, 9999999);
    }

    public function telkomPhone(): string
    {
        return static::randomElement(static::$countryPhonePrefixes).static::randomElement(static::$telkomPrefixes).$this->generator->numberBetween(1000000, 9999999);
    }

    public function kenyanPhone(): string
    {
        return static::randomElement([static::safaricomPhone(), static::airtelPhone(), static::telkomPhone()]);
    }

    public function mpesaCode(): string
    {
        return strtoupper($this->generator->lexify('??????')).$this->generator->numerify('###');
    }
}
