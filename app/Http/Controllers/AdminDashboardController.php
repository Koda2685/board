<?php

namespace App\Http\Controllers;

use App\Models\GovernanceDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        $hasStoredFile = function (GovernanceDocument $document): bool {
            return filled($document->file_path)
                && Storage::disk('private')->exists($document->file_path);
        };

        $normalizeGroupKey = function (GovernanceDocument $document): string {
            $reference = trim((string) $document->reference_no);

            if ($reference === '') {
                return (string) $document->id;
            }

            $normalized = preg_replace('/[-_\s]+(RES|RESOLUTION|MIN|MINUTES|SUP|SUPPORT|COMMUNICATION|DOC|DOCUMENT)$/i', '', $reference);

            return $normalized !== null && $normalized !== '' ? $normalized : $reference;
        };

        $resolutionGroupKey = function (GovernanceDocument $document): string {
            return (string) ($document->related_resolution_id ?? $document->id);
        };

        $minuteGroupKey = function (GovernanceDocument $document): string {
            return (string) ($document->related_minute_id ?? $document->id);
        };

        $consolidatedTopicKey = function (GovernanceDocument $document): string {
            $reference = trim((string) ($document->reference_no ?? ''));

            if ($reference !== '') {
                $referenceKey = preg_replace('#^(?:POL|REG|MIN|RES|COM|SPR|BRD)[\s/-]+#i', '', $reference);
                $referenceKey = preg_replace('#[\s/-]+(?:RES|RESOLUTION|MIN|MINUTES|COM|COMMUNICATION|SUP|SUPPORT|DOC|DOCUMENT|POL|REG|SPR|BRD|BOARD_REVIEWED|SPECIAL_RESOLUTION|SPECIALRESOLUTION)$#i', '', $referenceKey ?? '');

                if (is_string($referenceKey) && trim($referenceKey) !== '') {
                    return 'reference:' . trim($referenceKey);
                }
            }

            $issuedAt = $document->issued_at ? $document->issued_at->format('Y-m-d') : null;

            if ($issuedAt && $reference !== '') {
                $suffix = preg_replace('#^.*(?:POL|REG|MIN|RES|COM|SPR|BRD)[\s/-]+#i', '', $reference);
                $suffix = preg_replace('#[\s/-]+.*$#', '', (string) $suffix);

                if (trim((string) $suffix) !== '') {
                    return 'date-reference:' . $issuedAt . ':' . trim((string) $suffix);
                }
            }

            $resolutionId = $document->related_resolution_id ?? ($document->document_type === 'resolution' ? $document->id : null);
            $minuteId = $document->related_minute_id ?? ($document->document_type === 'minutes' ? $document->id : null);

            if ($document->document_type === 'resolution' && $reference === '' && $minuteId !== null) {
                return 'resolution-no-ref:' . $minuteId . ':' . $document->id;
            }

            if ($document->document_type === 'resolution' && $resolutionId !== null) {
                return 'resolution:' . $resolutionId;
            }

            if ($document->document_type === 'minutes' && $minuteId !== null) {
                return 'minute:' . $minuteId;
            }

            if ($resolutionId !== null) {
                return 'resolution:' . $resolutionId;
            }

            if ($minuteId !== null) {
                return 'minute:' . $minuteId;
            }

            $title = trim((string) ($document->title ?? ''));

            if ($title !== '') {
                return 'title:' . preg_replace('/\s+/', ' ', strtolower($title));
            }

            return 'document:' . (string) $document->id;
        };

        $consolidatedResolutionRegister = GovernanceDocument::query()
            ->whereIn('document_type', ['resolution', 'minutes', 'communication', 'board_reviewed'])
            ->orderBy('effective_at')
            ->orderBy('updated_at')
            ->get()
            ->groupBy($consolidatedTopicKey)
            ->map(function ($documents) {
                $resolution = $documents->firstWhere('document_type', 'resolution');

                if (! $resolution && $documents->first()?->related_resolution_id) {
                    $resolution = GovernanceDocument::query()->whereKey($documents->first()->related_resolution_id)->first();
                }

                $minute = $documents->firstWhere('document_type', 'minutes');

                if (! $minute && $documents->first()?->related_minute_id) {
                    $minute = GovernanceDocument::query()->whereKey($documents->first()->related_minute_id)->first();
                }

                if (! $resolution && $minute && $minute->related_resolution_id) {
                    $resolution = GovernanceDocument::query()->whereKey($minute->related_resolution_id)->first();
                }

                if (! $minute && $resolution && $resolution->related_minute_id) {
                    $minute = GovernanceDocument::query()->whereKey($resolution->related_minute_id)->first();
                }

                $supportingDocuments = $documents
                    ->filter(fn (GovernanceDocument $document) => in_array($document->document_type, ['communication', 'board_reviewed'], true))
                    ->values();

                return [
                    'title' => $resolution?->title ?? $documents->first()->title,
                    'reference_no' => $resolution?->reference_no ?? $minute?->reference_no ?? $documents->first()->reference_no,
                    'issued_at' => $resolution?->issued_at ? $resolution->issued_at->format('d M Y') : ($minute?->issued_at ? $minute->issued_at->format('d M Y') : '-'),
                    'resolution' => $resolution?->document_type === 'resolution' ? $resolution : null,
                    'minute' => $minute?->document_type === 'minutes' ? $minute : null,
                    'supporting_documents' => $supportingDocuments,
                    'status' => $resolution?->status ?? $minute?->status ?? $documents->first()->status ?? 'active',
                ];
            })
            ->values();

        $resolutionRegister = GovernanceDocument::query()
            ->whereIn('document_type', ['resolution', 'communication'])
            ->orderBy('effective_at')
            ->orderBy('updated_at')
            ->get()
            ->groupBy($resolutionGroupKey)
            ->map(function ($documents) {
                $resolution = $documents->firstWhere('document_type', 'resolution');

                $supportingDocuments = $documents
                    ->filter(fn (GovernanceDocument $document) => $document->document_type === 'communication')
                    ->values();

                return [
                    'title' => $resolution?->title ?? $documents->first()->title,
                    'reference_no' => $resolution?->reference_no ?? $documents->first()->reference_no,
                    'resolution' => $resolution,
                    'supporting_documents' => $supportingDocuments,
                    'status' => $resolution?->status ?? $documents->first()->status ?? 'active',
                ];
            })
            ->values();

        $minutesRegister = GovernanceDocument::query()
            ->whereIn('document_type', ['minutes', 'communication'])
            ->orderBy('effective_at')
            ->orderBy('updated_at')
            ->get()
            ->groupBy($minuteGroupKey)
            ->map(function ($documents) {
                $minutes = $documents->firstWhere('document_type', 'minutes');

                $supportingDocuments = $documents
                    ->filter(fn (GovernanceDocument $document) => $document->document_type === 'communication')
                    ->values();

                return [
                    'title' => $minutes?->title ?? $documents->first()->title,
                    'reference_no' => $minutes?->reference_no ?? $documents->first()->reference_no,
                    'minutes' => $minutes,
                    'supporting_documents' => $supportingDocuments,
                    'status' => $minutes?->status ?? $documents->first()->status ?? 'active',
                ];
            })
            ->values();

        $policiesRegister = GovernanceDocument::query()
            ->whereIn('document_type', ['policy'])
            ->orderBy('effective_at')
            ->orderBy('updated_at')
            ->get()
            ->groupBy($normalizeGroupKey)
            ->map(function ($documents) {
                $policy = $documents->first();

                return [
                    'title' => $policy?->title ?? $documents->first()->title,
                    'reference_no' => $policy?->reference_no ?? $documents->first()->reference_no,
                    'document' => $policy,
                    'status' => $policy?->status ?? $documents->first()->status ?? 'active',
                ];
            })
            ->values();

        $regulationsRegister = GovernanceDocument::query()
            ->whereIn('document_type', ['regulation'])
            ->orderBy('effective_at')
            ->orderBy('updated_at')
            ->get()
            ->groupBy($normalizeGroupKey)
            ->map(function ($documents) {
                $regulation = $documents->first();

                return [
                    'title' => $regulation?->title ?? $documents->first()->title,
                    'reference_no' => $regulation?->reference_no ?? $documents->first()->reference_no,
                    'document' => $regulation,
                    'status' => $regulation?->status ?? $documents->first()->status ?? 'active',
                ];
            })
            ->values();

        $correspondenceRegister = GovernanceDocument::query()
            ->whereIn('document_type', ['communication', 'correspondence'])
            ->orderBy('effective_at')
            ->orderBy('updated_at')
            ->get()
            ->groupBy($normalizeGroupKey)
            ->map(function ($documents) {
                $correspondence = $documents->first();

                return [
                    'title' => $correspondence?->title ?? $documents->first()->title,
                    'reference_no' => $correspondence?->reference_no ?? $documents->first()->reference_no,
                    'document' => $correspondence,
                    'status' => $correspondence?->status ?? $documents->first()->status ?? 'active',
                ];
            })
            ->values();

        $hrAgreementsRegister = GovernanceDocument::query()
            ->whereIn('document_type', ['hr_agreement'])
            ->orderBy('effective_at')
            ->orderBy('updated_at')
            ->get()
            ->groupBy($normalizeGroupKey)
            ->map(function ($documents) {
                $agreement = $documents->first();

                return [
                    'title' => $agreement?->title ?? $documents->first()->title,
                    'reference_no' => $agreement?->reference_no ?? $documents->first()->reference_no,
                    'document' => $agreement,
                    'status' => $agreement?->status ?? $documents->first()->status ?? 'active',
                ];
            })
            ->values();

        $externalAgreementsRegister = GovernanceDocument::query()
            ->whereIn('document_type', ['external_agreement'])
            ->orderBy('effective_at')
            ->orderBy('updated_at')
            ->get()
            ->groupBy($normalizeGroupKey)
            ->map(function ($documents) {
                $agreement = $documents->first();

                return [
                    'title' => $agreement?->title ?? $documents->first()->title,
                    'reference_no' => $agreement?->reference_no ?? $documents->first()->reference_no,
                    'document' => $agreement,
                    'status' => $agreement?->status ?? $documents->first()->status ?? 'active',
                ];
            })
            ->values();

        $commercialContractsRegister = GovernanceDocument::query()
            ->whereIn('document_type', ['commercial_contract'])
            ->orderBy('effective_at')
            ->orderBy('updated_at')
            ->get()
            ->groupBy($normalizeGroupKey)
            ->map(function ($documents) {
                $contract = $documents->first();

                return [
                    'title' => $contract?->title ?? $documents->first()->title,
                    'reference_no' => $contract?->reference_no ?? $documents->first()->reference_no,
                    'document' => $contract,
                    'status' => $contract?->status ?? $documents->first()->status ?? 'active',
                ];
            })
            ->values();

        return view('admin.dashboard', [
            'consolidatedResolutionRegister' => $consolidatedResolutionRegister,
            'resolutionRegister' => $resolutionRegister,
            'minutesRegister' => $minutesRegister,
            'policiesRegister' => $policiesRegister,
            'regulationsRegister' => $regulationsRegister,
            'correspondenceRegister' => $correspondenceRegister,
            'hrAgreementsRegister' => $hrAgreementsRegister,
            'externalAgreementsRegister' => $externalAgreementsRegister,
            'commercialContractsRegister' => $commercialContractsRegister,
        ]);
    }
}
