<?php

namespace ESolution\DBEncryption\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

use ESolution\DBEncryption\Tests\TestCase;

class EncryptModelTest extends TestCase
{
    /** @test */
    function command_encrypt_model() {
        Artisan::call('encryptable:encryptModel');
    }
}