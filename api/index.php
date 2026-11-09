<?php

declare(strict_types=1);

/**
 * Vercel Serverless Function entry point for CodeIgniter 4
 */

// Inform CodeIgniter it is running in serverless mode
putenv('VERCEL=1');

// Normalize server paths for CodeIgniter URI detection
if (!isset($_SERVER['SCRIPT_NAME']) || $_SERVER['SCRIPT_NAME'] === '') {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
}

// Forward to public front controller
require __DIR__ . '/../public/index.php';

