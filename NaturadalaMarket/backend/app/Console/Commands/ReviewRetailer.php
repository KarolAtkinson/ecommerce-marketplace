<?php

namespace App\Console\Commands;

use App\Models\RetailerProfile;
use App\Models\Role;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReviewRetailer extends Command
{
    protected $signature = 'marketplace:review-retailer {email : Retailer applicant email} {decision : approve or reject}';

    protected $description = 'Approve or reject a retailer application from a trusted local CLI session';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $decision = (string) $this->argument('decision');

        if (! in_array($decision, ['approve', 'reject'], true)) {
            $this->components->error('Decision must be approve or reject.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();
        $profile = $user?->retailerProfile;

        if (! $user || ! $profile) {
            $this->components->error('No retailer application was found for that email address.');

            return self::FAILURE;
        }

        if ($user->status !== 'active' || ! $user->hasVerifiedEmail()) {
            $this->components->error('The account must be active and email-verified before retailer review.');

            return self::FAILURE;
        }

        if ($profile->verification_status !== 'pending') {
            $this->components->error("This application is already {$profile->verification_status}.");

            return self::FAILURE;
        }

        $reviewNotes = trim((string) ($this->ask('Internal review note (optional)', '') ?? ''));

        if ($decision === 'reject' && $reviewNotes === '') {
            $this->components->error('A reason is required when declining an application.');

            return self::FAILURE;
        }

        if (! $this->confirm(ucfirst($decision)." retailer application for {$profile->legal_business_name}?")) {
            $this->components->warn('Retailer review cancelled.');

            return self::SUCCESS;
        }

        $eventType = $decision === 'approve'
            ? 'retailer_application_approved'
            : 'retailer_application_rejected';

        DB::transaction(function () use ($user, $profile, $decision, $reviewNotes, $eventType): void {
            RetailerProfile::query()
                ->whereKey($profile->id)
                ->where('verification_status', 'pending')
                ->lockForUpdate()
                ->firstOrFail()
                ->update([
                    'verification_status' => $decision === 'approve' ? 'approved' : 'rejected',
                    'reviewed_at' => now(),
                    'reviewed_by' => null,
                    'review_notes' => $reviewNotes === '' ? null : $reviewNotes,
                ]);

            if ($decision === 'approve') {
                $role = Role::query()->where('slug', 'retailer')->firstOrFail();
                $user->roles()->syncWithoutDetaching([$role->id => ['assigned_by' => null]]);
            } else {
                $retailerRole = Role::query()->where('slug', 'retailer')->firstOrFail();
                $user->roles()->detach($retailerRole);
            }

            SecurityEvent::query()->create([
                'user_id' => $user->id,
                'event_type' => $eventType,
                'metadata' => ['reviewed_by' => 'cli'],
            ]);
        });

        $this->components->info("Retailer application {$decision}d.");

        return self::SUCCESS;
    }
}
