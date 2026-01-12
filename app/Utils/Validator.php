<?php
namespace App\Utils;

/**
 * Input validation utility
 */
class Validator {
    /**
     * Validate required fields
     */
    public static function required($data, $fields) {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $errors[$field] = "Field '{$field}' is required";
            }
        }
        return $errors;
    }
    
    /**
     * Validate email
     */
    public static function email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate integer
     */
    public static function integer($value, $min = null, $max = null) {
        if (!is_numeric($value)) {
            return false;
        }
        $int = (int)$value;
        if ($min !== null && $int < $min) {
            return false;
        }
        if ($max !== null && $int > $max) {
            return false;
        }
        return true;
    }
    
    /**
     * Validate GPS coordinates
     */
    public static function gpsLatitude($lat) {
        if (empty($lat)) {
            return true; // Optional
        }
        return is_numeric($lat) && $lat >= -90 && $lat <= 90;
    }
    
    public static function gpsLongitude($lng) {
        if (empty($lng)) {
            return true; // Optional
        }
        return is_numeric($lng) && $lng >= -180 && $lng <= 180;
    }
    
    /**
     * Sanitize string
     */
    public static function sanitizeString($string) {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate date time
     */
    public static function dateTime($dateTime) {
        $d = \DateTime::createFromFormat('Y-m-d H:i:s', $dateTime);
        return $d && $d->format('Y-m-d H:i:s') === $dateTime;
    }
}
