<?php

namespace App\Services;

use App\Models\WebmasterContact;
use App\Models\WebmasterContactComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WebmasterContactService
{
    public const CATEGORY_ISSUE = 'issue';

    public const CATEGORY_ENHANCEMENT = 'enhancement';

    public const SOURCE_PUBLIC_WEBSITE = 'public_website';

    public const SOURCE_MEMBER_PORTAL = 'member_portal';

    /**
     * @param  array<int, UploadedFile>  $screenshotFiles
     */
    public function createSubmission(array $attributes, array $screenshotFiles = []): WebmasterContact
    {
        $screenshotPaths = $this->storeScreenshots($screenshotFiles);
        $category = (string) ($attributes['category'] ?? self::CATEGORY_ISSUE);
        $urgent = $category === self::CATEGORY_ENHANCEMENT
            ? false
            : (bool) ($attributes['urgent'] ?? false);

        $contact = WebmasterContact::query()->create([
            'name' => (string) $attributes['name'],
            'email' => (string) $attributes['email'],
            'subject' => (string) $attributes['subject'],
            'message' => (string) $attributes['message'],
            'urgent' => $urgent,
            'screenshots' => $screenshotPaths,
            'facility_id' => $attributes['facility_id'] ?? null,
            'category' => $category,
            'source' => (string) ($attributes['source'] ?? self::SOURCE_PUBLIC_WEBSITE),
            'user_id' => $attributes['user_id'] ?? null,
        ]);

        $this->notifyWebmaster($contact);

        return $contact;
    }

    public function updateMemberSubmission(WebmasterContact $contact, array $attributes, array $screenshotFiles = []): WebmasterContact
    {
        $category = (string) ($attributes['category'] ?? $contact->category);
        $urgent = $category === self::CATEGORY_ENHANCEMENT
            ? false
            : (bool) ($attributes['urgent'] ?? false);

        $existingScreenshots = is_array($contact->screenshots) ? $contact->screenshots : [];
        $newScreenshots = $this->storeScreenshots($screenshotFiles);
        $screenshots = array_slice(array_merge($existingScreenshots, $newScreenshots), 0, 5);

        $contact->fill([
            'name' => (string) $attributes['name'],
            'email' => (string) $attributes['email'],
            'subject' => (string) $attributes['subject'],
            'message' => (string) $attributes['message'],
            'urgent' => $urgent,
            'facility_id' => $attributes['facility_id'] ?? $contact->facility_id,
            'category' => $category,
        ]);
        $contact->screenshots = $screenshots;
        $contact->save();
        $contact->markUnreadForAdmin();

        return $contact->fresh();
    }

    public function addComment(
        WebmasterContact $contact,
        string $body,
        User $user,
        string $authorType = WebmasterContactComment::AUTHOR_MEMBER
    ): WebmasterContactComment {
        $comment = $contact->comments()->create([
            'user_id' => $user->id,
            'author_type' => $authorType,
            'author_name' => $user->name,
            'body' => trim($body),
        ]);

        $contact->markUnreadForAdmin();

        return $comment;
    }

    public function notifyWebmasterOfActivity(WebmasterContact $contact, string $actionLabel): void
    {
        $webmasterEmail = config('mail.webmaster_address', env('WEBMASTER_EMAIL', 'webmaster@example.com'));

        if (! $webmasterEmail) {
            return;
        }

        try {
            Mail::raw(
                "Feedback submission #{$contact->id} was updated.\n"
                . "Action: {$actionLabel}\n"
                . "Subject: {$contact->subject}\n"
                . "Status: {$contact->status}\n"
                . "View in admin: " . route('admin.webmaster.contacts.show', $contact),
                function ($message) use ($webmasterEmail, $contact, $actionLabel) {
                    $message->to($webmasterEmail)
                        ->subject('[Portal Feedback] ' . $actionLabel . ' — ' . $contact->subject);
                }
            );
        } catch (\Throwable $e) {
            Log::error('Webmaster activity notification failed: ' . $e->getMessage());
        }
    }

    public function commentValidationRules(): array
    {
        return [
            'body' => 'required|string|max:2000',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function storeScreenshots(array $screenshotFiles): array
    {
        $paths = [];

        foreach ($screenshotFiles as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $paths[] = $file->store('webmaster_screenshots', 'public');
            }
        }

        return $paths;
    }

    public function notifyWebmaster(WebmasterContact $contact): void
    {
        $webmasterEmail = config('mail.webmaster_address', env('WEBMASTER_EMAIL', 'webmaster@example.com'));

        if (! $webmasterEmail) {
            return;
        }

        try {
            $categoryLabel = $contact->categoryLabel();
            $sourceLabel = $contact->sourceLabel();
            $facilityName = $contact->facility?->name ?? 'Not specified';

            $body = "Portal / Website Feedback\n"
                . "Category: {$categoryLabel}\n"
                . "Source: {$sourceLabel}\n"
                . "Facility: {$facilityName}\n"
                . "Name: {$contact->name}\n"
                . "Email: {$contact->email}\n"
                . "Subject: {$contact->subject}\n"
                . "Urgent: " . ($contact->urgent ? 'Yes' : 'No') . "\n"
                . "Message:\n{$contact->message}\n";

            if (! empty($contact->screenshots)) {
                $body .= "\nScreenshots attached in admin panel.";
            }

            Mail::raw($body, function ($message) use ($webmasterEmail, $contact, $categoryLabel) {
                $message->to($webmasterEmail)
                    ->subject('[Portal Feedback] ' . $categoryLabel . ' — ' . $contact->subject);
            });
        } catch (\Throwable $e) {
            Log::error('Webmaster contact notification failed: ' . $e->getMessage());
        }
    }

    /**
     * @return array<string, string>
     */
    public function categoryOptions(): array
    {
        return [
            self::CATEGORY_ISSUE => 'Issue or error',
            self::CATEGORY_ENHANCEMENT => 'Wish list / enhancement',
        ];
    }

    public function validationRules(bool $requireFacilityId = false): array
    {
        return [
            'category' => 'nullable|string|in:' . self::CATEGORY_ISSUE . ',' . self::CATEGORY_ENHANCEMENT,
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:2000',
            'urgent' => 'nullable|boolean',
            'facility_id' => ($requireFacilityId ? 'required' : 'nullable') . '|integer|exists:facilities,id',
            'screenshots' => 'nullable|array|max:5',
            'screenshots.*' => 'image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ];
    }

    /**
     * @return array<int, UploadedFile>
     */
    public function screenshotFilesFromRequest(Request $request): array
    {
        if (! $request->hasFile('screenshots')) {
            return [];
        }

        return array_values(array_filter($request->file('screenshots') ?? []));
    }
}
