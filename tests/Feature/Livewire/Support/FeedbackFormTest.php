<?php

use App\Livewire\Support\FeedbackForm;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use App\Notifications\SupportTicketReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('stores support ticket for guest submission', function (): void {
    Livewire::test(FeedbackForm::class)
        ->set('formData.name', 'Guest Tester')
        ->set('formData.email', 'guest@example.test')
        ->set('formData.category', 'bug')
        ->set('formData.message', 'La page se fige quand je valide mon log.')
        ->call('submit')
        ->assertSet('submitted', true);

    $ticket = SupportTicket::where('email', 'guest@example.test')->first();

    expect($ticket)->not->toBeNull()
        ->and($ticket->category)->toBe('bug')
        ->and($ticket->user_id)->toBeNull();
});

it('prefills authenticated user data and links the ticket', function (): void {
    $user = User::factory()->create([
        'name' => 'Maker One',
        'email' => 'maker@example.test',
    ]);

    $this->actingAs($user);

    Livewire::test(FeedbackForm::class)
        ->assertSet('formData.name', $user->name)
        ->assertSet('formData.email', $user->email)
        ->set('formData.category', 'idea')
        ->set('formData.message', 'Ajouter un export hebdo en PDF.')
        ->call('submit')
        ->assertSet('submitted', true);

    $ticket = SupportTicket::where('user_id', $user->id)->first();

    expect($ticket)->not->toBeNull()
        ->and($ticket->name)->toBe($user->name)
        ->and($ticket->email)->toBe($user->email)
        ->and($ticket->category)->toBe('idea');
});

it('notifies the support team when a ticket is created', function (): void {
    Notification::fake();
    Http::fake();

    config()->set('support.team_recipients', ['team@example.test']);
    config()->set('support.team_telegram_chat_ids', ['123456789']);

    Livewire::test(FeedbackForm::class)
        ->set('formData.name', 'Team Ping')
        ->set('formData.email', 'ping@example.test')
        ->set('formData.category', 'question')
        ->set('formData.message', 'Test notification flow.')
        ->call('submit');

    Notification::assertSentOnDemand(SupportTicketReceived::class, function (SupportTicketReceived $notification, array $channels, $notifiable): bool {
        return in_array('mail', $channels, true)
            && ($notifiable->routes['mail'] ?? null) === 'team@example.test';
    });

    Notification::assertSentOnDemand(SupportTicketReceived::class, function (SupportTicketReceived $notification, array $channels, $notifiable): bool {
        return in_array(TelegramChannel::class, $channels, true)
            && ($notifiable->routes['telegram'] ?? null) === '123456789';
    });
});
