<?php

namespace App\Services;

use App\Models\PortalHelpRequest;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PortalHelpRequestService
{
    /**
     * @param  array<int, UploadedFile>  $attachmentFiles
     */
    public function createRequest(array $attributes, array $attachmentFiles = []): PortalHelpRequest
    {
        $type = (string) ($attributes['type'] ?? PortalHelpRequest::TYPE_SUPPORT);
        $priority = $type === PortalHelpRequest::TYPE_HR
            ? 'normal'
            : (string) ($attributes['priority'] ?? 'normal');

        $request = PortalHelpRequest::query()->create([
            'user_id' => $attributes['user_id'],
            'facility_id' => $attributes['facility_id'] ?? null,
            'type' => $type,
            'category' => (string) $attributes['category'],
            'priority' => $priority,
            'name' => (string) $attributes['name'],
            'email' => (string) $attributes['email'],
            'phone' => $attributes['phone'] ?? null,
            'employee_num' => $attributes['employee_num'] ?? null,
            'subject' => (string) $attributes['subject'],
            'message' => (string) $attributes['message'],
            'preferred_contact' => (string) ($attributes['preferred_contact'] ?? 'email'),
            'best_time_to_reach' => $attributes['best_time_to_reach'] ?? null,
            'steps_to_reproduce' => $attributes['steps_to_reproduce'] ?? null,
            'attachments' => $this->storeAttachments($attachmentFiles),
            'no_phi_confirmed' => (bool) ($attributes['no_phi_confirmed'] ?? false),
        ]);

        $this->notifyTeam($request);

        return $request;
    }

    /**
     * @return array<int, string>
     */
    public function storeAttachments(array $files): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $paths[] = $file->store('portal_help_attachments', 'public');
            }
        }

        return array_slice($paths, 0, 5);
    }

    public function notifyTeam(PortalHelpRequest $request): void
    {
        $recipient = $request->isHrInquiry()
            ? config('portal-help.hr_notification_email')
            : config('portal-help.support_notification_email');

        if (! $recipient) {
            return;
        }

        try {
            $body = $request->typeLabel() . " — " . $request->referenceCode() . "\n"
                . "Category: {$request->categoryLabel()}\n"
                . "Priority: {$request->priority}\n"
                . "Facility: " . ($request->facility?->name ?? 'Not specified') . "\n"
                . "From: {$request->name} ({$request->email})\n"
                . "Phone: " . ($request->phone ?: '—') . "\n"
                . "Employee #: " . ($request->employee_num ?: '—') . "\n"
                . "Preferred contact: {$request->preferred_contact}\n"
                . "Best time: " . ($request->best_time_to_reach ?: '—') . "\n"
                . "Subject: {$request->subject}\n\n"
                . "Message:\n{$request->message}\n";

            if ($request->steps_to_reproduce) {
                $body .= "\nSteps to reproduce:\n{$request->steps_to_reproduce}\n";
            }

            if (! empty($request->attachments)) {
                $body .= "\nAttachments saved in admin panel.";
            }

            Mail::raw($body, function ($message) use ($recipient, $request) {
                $message->to($recipient)
                    ->subject('[' . $request->typeLabel() . '] ' . $request->subject);
            });
        } catch (\Throwable $e) {
            Log::error('Portal help request notification failed: ' . $e->getMessage());
        }
    }

    public function hrValidationRules(bool $requireFacilityId = false): array
    {
        $categories = implode(',', array_keys(config('portal-help.hr_categories', [])));
        $preferred = implode(',', array_keys(config('portal-help.preferred_contact_options', [])));
        $bestTime = implode(',', array_keys(config('portal-help.best_time_options', [])));

        return [
            'category' => 'required|string|in:' . $categories,
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:32',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:3000',
            'preferred_contact' => 'required|string|in:' . $preferred,
            'best_time_to_reach' => 'nullable|string|in:' . $bestTime,
            'no_phi_confirmed' => 'accepted',
            'facility_id' => ($requireFacilityId ? 'required' : 'nullable') . '|integer|exists:facilities,id',
        ];
    }

    public function supportValidationRules(bool $requireFacilityId = false): array
    {
        $categories = implode(',', array_keys(config('portal-help.support_categories', [])));
        $preferred = implode(',', array_keys(config('portal-help.preferred_contact_options', [])));
        $bestTime = implode(',', array_keys(config('portal-help.best_time_options', [])));

        return [
            'category' => 'required|string|in:' . $categories,
            'priority' => 'required|string|in:normal,urgent',
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:32',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:3000',
            'steps_to_reproduce' => 'nullable|string|max:2000',
            'preferred_contact' => 'required|string|in:' . $preferred,
            'best_time_to_reach' => 'nullable|string|in:' . $bestTime,
            'no_phi_confirmed' => 'accepted',
            'facility_id' => ($requireFacilityId ? 'required' : 'nullable') . '|integer|exists:facilities,id',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf|max:5120',
        ];
    }

    /**
     * @return array<int, UploadedFile>
     */
    public function attachmentFilesFromRequest(Request $request): array
    {
        if (! $request->hasFile('attachments')) {
            return [];
        }

        return array_values(array_filter($request->file('attachments') ?? []));
    }
}
