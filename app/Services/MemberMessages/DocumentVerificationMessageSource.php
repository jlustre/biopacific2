<?php

namespace App\Services\MemberMessages;

use App\Contracts\MemberMessageSource;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Support\Collection;

class DocumentVerificationMessageSource implements MemberMessageSource
{
    public function key(): string
    {
        return 'documents';
    }

    public function label(): string
    {
        return 'Documents';
    }

    public function messagesFor(User $user): Collection
    {
        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee()
            : null;

        if (! $employee) {
            return collect();
        }

        return Upload::query()
            ->where('employee_num', $employee->employee_num)
            ->current()
            ->whereIn('verification_status', [
                Upload::VERIFICATION_APPROVED,
                Upload::VERIFICATION_REJECTED,
            ])
            ->whereNotNull('verified_at')
            ->where('verified_at', '>=', now()->subDays(90))
            ->with(['uploadType:id,name,is_license_or_certification', 'checklistItem:id,name', 'verifiedBy:id,name'])
            ->latest('verified_at')
            ->limit(25)
            ->get()
            ->map(function (Upload $upload) {
                $documentName = $upload->checklistItem?->name
                    ?? $upload->uploadType?->name
                    ?? $upload->original_filename
                    ?? 'Document';
                $reviewer = $upload->verifiedBy?->name;
                $isLicense = (bool) ($upload->uploadType?->is_license_or_certification ?? false);
                $isRejected = $upload->verification_status === Upload::VERIFICATION_REJECTED;
                $kind = $isLicense ? 'credential' : 'document';
                $route = $isLicense
                    ? route('member.certifications', array_filter(['upload_type_id' => $upload->upload_type_id]))
                    : route('member.documents', array_filter(['upload_type_id' => $upload->upload_type_id]));

                if ($isRejected) {
                    $title = ($isLicense ? 'Credential' : 'Document').' returned for correction: '.$documentName;
                    $body = ($reviewer ? "Returned by {$reviewer}. " : '')
                        .($upload->verification_notes
                            ? 'Notes: '.$upload->verification_notes
                            : 'Please upload a corrected file and resubmit for approval.');
                    $tone = 'rose';
                    $action = 'Upload correction';
                } else {
                    $title = ($isLicense ? 'Credential' : 'Document').' approved: '.$documentName;
                    $body = $reviewer
                        ? "Approved by {$reviewer}. Your {$kind} is now verified."
                        : "Your {$kind} was approved and verified.";
                    $tone = 'brand';
                    $action = $isLicense ? 'View credentials' : 'View documents';
                }

                $isRecent = $upload->verified_at
                    && $upload->verified_at->greaterThanOrEqualTo(now()->subDays(14));

                return [
                    'id' => ($isRejected ? 'document-rejected:' : 'document-approved:').$upload->id,
                    'source' => $this->key(),
                    'category' => $isLicense ? 'Credentials' : 'Documents',
                    'title' => $title,
                    'body' => $body,
                    'tone' => $tone,
                    'occurred_at' => $upload->verified_at ?? $upload->updated_at,
                    'route' => $route,
                    'action_label' => $action,
                    'attention' => (bool) $isRecent || $isRejected,
                    'meta' => [
                        'upload_id' => $upload->id,
                        'verification_status' => $upload->verification_status,
                    ],
                ];
            });
    }
}
