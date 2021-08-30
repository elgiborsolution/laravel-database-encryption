<?php

namespace ESolution\DBEncryption\Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EncryptedTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     */
    public function it_test_if_encryption_decoding_is_working()
    {
        $name = 'Jhon';
        $email = 'foo@bar.com';

        $user = $this->createUser($name, $email);

        $this->assertEquals($user->email, $email);
        $this->assertEquals($user->name, $name);
    }

    /**
     * @test
     */
    public function it_test_if_encryption_encoding_is_working()
    {
        $name = 'Jhon';
        $email = 'foo@bar.com';
        $user = $this->createUser($name, $email);

        $userRaw = DB::table('test_users')->select('*')->first();

        $this->assertEquals($userRaw->email, $user->encryptAttribute($email));
        $this->assertEquals($userRaw->name, $user->encryptAttribute($name));
    }


    /**
     * @test
     */
    public function it_test_that_encrypt_model_commands_encrypt_existing_records()
    {
        TestUser::$enableEncryption = false;

        $user = $this->createUser();

        $this->artisan('encryptable:encryptModel', ['model' => TestUser::class]);
        $raw = DB::table('test_users')->select('*')->first();

        $this->assertEquals($raw->email, $user->encryptAttribute($user->email));
        $this->assertEquals($raw->name, $user->encryptAttribute($user->name));

        TestUser::$enableEncryption = true;
    }


    /**
     * @test
     */
    public function it_test_that_where_in_query_builder_is_working()
    {
        $email = 'example@email.com';
        $this->createUser('Jhon Doe', $email);

        $user = TestUser::whereEncrypted('email', '=', $email)->first();

        $this->assertNotNull($user);
    }

    /**
     * @test
     */
    public function it_assert_that_where_does_not_retrieve_a_user_with_incorrect_email()
    {
        $this->createUser();

        $user = TestUser::whereEncrypted('email', '=', 'non_existing@email.com')->first();

        $this->assertNull($user);
    }


    /**
     * @test
     */
    public function it_test_that_validation_rule_exists_when_record_exists_is_working()
    {
        $email = 'example@email.com';

        $this->createUser('Jhon Doe', $email);

        $validator = validator(compact('email'), ['email' => 'exists_encrypted:test_users,email']);

        $this->assertFalse($validator->fails());
    }

    /**
     * @test
     */
    public function it_test_that_validation_rule_exists_when_record_does_not_exists_is_working()
    {
        $this->createUser();

        $validator = validator(
            ['email' => 'non_existing@email.com'],
            ['email' => 'exists_encrypted:test_users,email']
        );

        $this->assertTrue($validator->fails());
    }


    /**
     * @test
     */
    public function it_test_that_validation_rule_unique_when_record_exists_is_working()
    {
        $email = 'example@email.com';

        $this->createUser('Jhon Doe', $email);

        $validator = validator(compact('email'), ['email' => 'unique_encrypted:test_users,email']);

        $this->assertTrue($validator->fails());
    }

    /**
     * @test
     */
    public function it_test_that_validation_rule_unique_when_record_does_not_exists_is_working()
    {
        $this->createUser();

        $validator = validator(
            ['email' => 'non_existing@email.com'],
            ['email' => 'unique_encrypted:test_users,email']
        );

        $this->assertFalse($validator->fails());
    }

    /**
     * @test
     */
    public function it_tests_that_empty_values_are_encrypted()
    {
        $user = $this->createUser(null, 'example@email.com');
        $raw = DB::table('test_users')->select('*')->first();

        $this->assertNotEmpty($raw->name);
        $this->assertEmpty($user->name);
    }


    /**
     * @test
     */
    public function it_test_that_decrypt_command_is_working()
    {
        TestUser::$enableEncryption = false;

        $user = $this->createUser();

        $this->artisan('encryptable:encryptModel', ['model' => TestUser::class]);
        $this->artisan('encryptable:decryptModel', ['model' => TestUser::class]);
        $raw = DB::table('test_users')->select('*')->first();


        $this->assertEquals($user->email, $raw->email);
        $this->assertEquals($user->name, $raw->name);

        TestUser::$enableEncryption = true;
    }

    /**
     * @test
     */
    public function it_test_that_where_query_is_working_with_non_lowercase_values()
    {
        $this->createUser();
        $this->assertNotNull(TestUser::whereEncrypted('email', '=', 'JhOn@DoE.cOm')->first());
    }

    /**
     * @test
     */
    public function it_test_that_whereencrypted_can_handle_single_quote()
    {
        $email = "JhOn@DoE.cOm'";
        $name = "Single's";
        $this->createUser($name, $email);
        $query = TestUser::whereEncrypted('email', $email)->orWhereEncrypted('name', $name)->first();

        $this->assertNotNull($query);
    }
}
