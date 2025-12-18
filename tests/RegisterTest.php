<?php
use PHPUnit\Framework\TestCase;

class RegisterTest extends TestCase
{
    private $password = "Test@123";
    private $confirmPassword = "Test@123";

    /** @test */
    public function password_and_confirm_password_should_match()
    {
        $this->assertEquals(
            $this->password,
            $this->confirmPassword,
            "Passwords do not match"
        );
    }

    /** @test */
    public function password_should_be_hashed()
    {
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);

        $this->assertTrue(
            password_verify($this->password, $hashedPassword)
        );
    }

    /** @test */
    public function email_should_be_valid()
    {
        $email = "user@example.com";

        $this->assertTrue(
            filter_var($email, FILTER_VALIDATE_EMAIL) !== false
        );
    }
}
