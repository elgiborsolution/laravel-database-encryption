<?php

namespace ESolution\DBEncryption\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

use ESolution\DBEncryption\Tests\TestCase;

class DecryptModelTest extends TestCase
{
    /** @test */
    function command_decrypt_model() {
        Artisan::call('encryptable:decryptModel');
    }
}