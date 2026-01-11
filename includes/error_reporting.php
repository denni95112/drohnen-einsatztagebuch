<?php
/**
 * Error reporting configuration
 */
// Set error reporting based on environment
// In production, you might want to disable error display
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
