<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $file,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
        $this->assertNotNull($user->avatar_url);
    }

    public function test_avatar_must_be_valid_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $invalidFile,
            ]);

        $response->assertSessionHasErrors('avatar');
    }

    public function test_user_can_remove_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');
        $path = $file->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        Storage::disk('public')->assertExists($path);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'remove_avatar' => true,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertNull($user->avatar);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_user_can_upload_cropped_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        // A minimal valid 1x1 png base64
        $fakeBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'avatar_cropped_data' => $fakeBase64,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
        $this->assertNotNull($user->avatar_url);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_user_can_change_email_with_correct_password(): void
    {
        $user = User::factory()->create([
            'email' => 'original@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile/email', [
                'email' => 'new-email@example.com',
                'password' => 'correct-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('new-email@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_user_cannot_change_email_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'email' => 'original@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile/email', [
                'email' => 'new-email@example.com',
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('changeEmail', 'password');

        $user->refresh();

        $this->assertSame('original@example.com', $user->email);
    }

    public function test_user_cannot_change_email_to_already_used_email(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'taken@example.com',
        ]);

        $user = User::factory()->create([
            'email' => 'original@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile/email', [
                'email' => 'taken@example.com',
                'password' => 'correct-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('changeEmail', [
                'email' => 'Email đã được sử dụng',
            ]);

        $user->refresh();

        $this->assertSame('original@example.com', $user->email);
    }

    public function test_user_can_request_password_reset_link_to_original_email_from_profile(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'original@example.com',
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson('/profile/forgot-password');

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        Notification::assertSentTo($user, ResetPassword::class);
    }
}
