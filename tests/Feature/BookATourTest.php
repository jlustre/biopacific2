<?php

namespace Tests\Feature;

use App\Mail\BookATourMail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class BookATourTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure the facilities table is not cleared
        $this->artisan('migrate');
    }

    public function test_it_sends_book_a_tour_email()
    {
        Mail::fake();

        $formData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
            'preferred_date' => '2025-10-25',
            'message' => 'Looking forward to the tour.',
        ];

        $this->postJson(route('book-a-tour.store'), $formData)
             ->assertStatus(200)
             ->assertJson(['message' => 'Your request has been sent successfully.']);

        Mail::assertSent(BookATourMail::class, function ($mail) use ($formData) {
            return $mail->data['name'] === $formData['name'] &&
                   $mail->data['email'] === $formData['email'] &&
                   $mail->data['preferred_date'] === $formData['preferred_date'];
        });
    }
}