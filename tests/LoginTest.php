<?php
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    private $plainPassword = "Secret123";
    private $hashedPassword;

    protected function setUp(): void
    {
        $this->hashedPassword = password_hash($this->plainPassword, PASSWORD_DEFAULT);
    }

    /** @test */
    public function correct_password_should_verify()
    {
        $this->assertTrue(
            password_verify($this->plainPassword, $this->hashedPassword)
        );
    }

    /** @test */
    public function wrong_password_should_fail()
    {
        $this->assertFalse(
            password_verify("WrongPassword", $this->hashedPassword)
        );
    }

    /** @test */
    public function role_should_redirect_correctly()
    {
        $role = "Admin";

        switch ($role) {
            case "Admin":
                $redirect = "admin/admin_dashboard.php";
                break;
            case "Customer":
                $redirect = "customer/customer_dashboard.php";
                break;
            case "Rider":
                $redirect = "rider/rider_dashboard.php";
                break;
            default:
                $redirect = "index.html";
        }

        $this->assertEquals(
            "admin/admin_dashboard.php",
            $redirect
        );
    }
}
