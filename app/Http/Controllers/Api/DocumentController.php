<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use App\Models\ErpDocument;
use App\Models\User;
use App\Services\DocumentSigningService;
use App\Services\QrCodeService;
use App\Services\SecurityAuditService;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentSigningService $signing,
        private readonly SecurityAuditService $audit,
        private readonly TenantContext $tenant,
        private readonly QrCodeService $qrCode,
    ) {}

    public function index(Request $request)
    {
        $viewer = $request->user();
        $documents = ErpDocument::query()
            ->with([
                'owner:id,name,username,role,job_title',
                'creator:id,name,username',
                'signatures.signer:id,name,username,job_title,role',
                'certificateTemplate:id,name,file_hash',
                'supervisor:id,name,username,employee_code,job_title,role',
            ])
            ->when(
                ! $viewer->isCEO() && ! $viewer->isHRD(),
                function (Builder $query) use ($viewer): void {
                    if ($viewer->isManager()) {
                        $teamIds = User::query()
                            ->where('is_active', true)
                            ->when($this->tenant->id(), fn (Builder $users) => $users->where('company_id', $this->tenant->id()))
                            ->where(fn (Builder $users) => $users
                                ->whereKey($viewer->id)
                                ->orWhere('parent', $viewer->username))
                            ->pluck('id');
                        $query->whereIn('owner_user_id', $teamIds);
                    } else {
                        $query->where('owner_user_id', $viewer->id);
                    }
                },
            )
            ->latest('id')
            ->get()
            ->map(fn (ErpDocument $document): array => $this->formatDocument($document, $viewer));

        $canIssue = $viewer->isCEO() || $viewer->isHRD();

        return response()->json([
            'can_issue' => $canIssue,
            'can_manage_templates' => $canIssue,
            'signature_profile_configured' => (bool) $viewer->signature_image_path,
            'people' => $canIssue
                ? User::query()->where('is_active', true)->when($this->tenant->id(), fn (Builder $users) => $users->where('company_id', $this->tenant->id()))->orderBy('name')->get([
                    'id', 'name', 'username', 'employee_code', 'role', 'job_title',
                ])
                : [],
            'signers' => $canIssue
                ? User::query()
                    ->where('is_active', true)
                    ->when($this->tenant->id(), fn (Builder $users) => $users->where('company_id', $this->tenant->id()))
                    ->where(function (Builder $query): void {
                        $query->where('role', 'ceo')
                            ->orWhere('role', 'like', 'mgr_%')
                            ->orWhereIn('role', ['mgr_hrd', 'staff_hrd']);
                    })
                    ->orderBy('name')
                    ->get(['id', 'name', 'username', 'employee_code', 'role', 'job_title'])
                : [],
            'templates' => $canIssue
                ? CertificateTemplate::query()
                    ->latest('id')
                    ->get(['id', 'name', 'file_hash', 'is_active', 'created_at'])
                : [],
            'documents' => $documents,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function storeInternshipCertificate(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor->isCEO() || $actor->isHRD(), 403);

        $validated = $request->validate([
            'owner_user_id' => ['required', 'integer', 'exists:users,id'],
            'certificate_number' => ['nullable', 'string', 'max:100', Rule::unique('erp_documents', 'document_number')->where('company_id', $this->tenant->id())],
            'program_name' => ['required', 'string', 'max:180'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'issued_at' => ['required', 'date'],
            'performance_label' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:3000'],
            'supervisor_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'certificate_template_id' => ['nullable', 'integer', 'exists:certificate_templates,id'],
        ]);

        $owner = User::query()->whereKey($validated['owner_user_id'])->where('is_active', true)->when($this->tenant->id(), fn (Builder $users) => $users->where('company_id', $this->tenant->id()))->firstOrFail();
        $number = ($validated['certificate_number'] ?? null) ?: $this->nextCertificateNumber();
        $defaultSupervisorId = User::query()
            ->where('role', 'ceo')
            ->where('is_active', true)
            ->value('id')
            ?? $actor->id;
        $supervisor = User::query()
            ->whereKey($validated['supervisor_user_id'] ?? $defaultSupervisorId)
            ->where('is_active', true)
            ->firstOrFail();
        $template = CertificateTemplate::query()
            ->whereKey($validated['certificate_template_id'] ?? null)
            ->where('is_active', true)
            ->first()
            ?? CertificateTemplate::query()->where('is_active', true)->latest('id')->first();

        $document = ErpDocument::query()->create([
            'document_type' => 'internship_certificate',
            'document_number' => $number,
            'title' => 'Sertifikat Magang - '.$owner->name,
            'owner_user_id' => $owner->id,
            'created_by_id' => $actor->id,
            'certificate_template_id' => $template?->id,
            'supervisor_user_id' => $supervisor->id,
            'status' => 'draft',
            'issued_at' => $validated['issued_at'],
            'verification_token' => Str::random(64),
            'content' => [
                'recipient_name' => $owner->name,
                'recipient_role' => $owner->job_title,
                'program_name' => $validated['program_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'performance_label' => $validated['performance_label'] ?? 'Baik',
                'description' => $validated['description']
                    ?? 'Telah menyelesaikan program magang di Suba Arch dengan dedikasi dan tanggung jawab yang baik.',
                'template_hash' => $template?->file_hash,
                'supervisor_employee_code' => $supervisor->employee_code,
            ],
        ]);

        $this->audit->record(
            'document.internship_certificate_created',
            actor: $actor,
            request: $request,
            metadata: ['number' => $number, 'owner' => $owner->username],
            subjectType: ErpDocument::class,
            subjectId: $document->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'Draft sertifikat magang berhasil dibuat dan siap ditandatangani.',
            'document' => $this->formatDocument(
                $document->fresh([
                    'owner',
                    'creator',
                    'supervisor',
                    'certificateTemplate',
                    'signatures.signer',
                ]),
                $actor,
            ),
        ], 201);
    }

    public function sign(Request $request, ErpDocument $document)
    {
        $actor = $request->user();
        $isAssignedSupervisor = (int) $document->supervisor_user_id === (int) $actor->id;
        $isLegacyIssuer = ! $document->supervisor_user_id && ($actor->isCEO() || $actor->isHRD());
        abort_unless($isAssignedSupervisor || $isLegacyIssuer, 403, 'Hanya pembimbing yang ditunjuk yang dapat menandatangani sertifikat ini.');
        abort_if($document->revoked_at, 422, 'Dokumen yang telah dicabut tidak dapat ditandatangani.');
        abort_unless($document->status === 'draft', 422, 'Hanya dokumen berstatus draft yang dapat ditandatangani.');

        $signed = $this->signing->sign($document, $actor);
        $this->audit->record(
            'document.signed',
            actor: $actor,
            request: $request,
            metadata: ['number' => $signed->document_number, 'signature_level' => 'internal'],
            subjectType: ErpDocument::class,
            subjectId: $signed->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil ditandatangani secara internal dan dapat diverifikasi.',
            'document' => $this->formatDocument($signed, $actor),
        ]);
    }

    public function storeTemplate(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor->isCEO() || $actor->isHRD(), 403);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'background' => ['required', 'file', 'mimes:png,jpg,jpeg,webp', 'max:8192'],
        ]);

        $file = $request->file('background');
        $path = $file->store('certificate-templates', 'local');
        $hash = hash('sha256', Storage::disk('local')->get($path));

        $template = DB::transaction(function () use ($actor, $validated, $file, $path, $hash): CertificateTemplate {
            CertificateTemplate::query()->update(['is_active' => false]);

            return CertificateTemplate::query()->create([
                'name' => $validated['name'],
                'background_path' => $path,
                'background_mime' => $file->getMimeType() ?: 'image/png',
                'file_hash' => $hash,
                'is_active' => true,
                'created_by_id' => $actor->id,
            ]);
        });

        $this->audit->record(
            'certificate.template_uploaded',
            actor: $actor,
            request: $request,
            metadata: ['template_id' => $template->id, 'name' => $template->name, 'hash' => $hash],
            subjectType: CertificateTemplate::class,
            subjectId: $template->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'Template gratis berhasil diaktifkan untuk sertifikat berikutnya.',
            'template' => $template,
        ], 201);
    }

    public function storeSignatureProfile(Request $request)
    {
        $actor = $request->user();
        $validated = $request->validate([
            'signature' => ['required', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'consent' => ['accepted'],
        ]);

        $oldPath = $actor->signature_image_path;
        $path = $request->file('signature')->store(
            'signature-profiles/'.$actor->id,
            'local',
        );
        $actor->forceFill([
            'signature_image_path' => $path,
            'signature_consented_at' => now(),
        ])->save();
        if ($oldPath && $oldPath !== $path) {
            Storage::disk('local')->delete($oldPath);
        }

        $this->audit->record(
            'signature.profile_updated',
            actor: $actor,
            request: $request,
            metadata: ['consented_at' => $actor->signature_consented_at?->toIso8601String()],
            subjectType: User::class,
            subjectId: $actor->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'Tanda tangan tersimpan terenkripsi di area privat dan siap digunakan.',
            'configured' => true,
        ]);
    }

    public function revoke(Request $request, ErpDocument $document)
    {
        $actor = $request->user();
        abort_unless($actor->isCEO() || $actor->isHRD(), 403);
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $document->forceFill([
            'status' => 'revoked',
            'revoked_at' => now(),
            'revocation_reason' => $validated['reason'],
        ])->save();

        $this->audit->record(
            'document.revoked',
            actor: $actor,
            request: $request,
            metadata: ['number' => $document->document_number, 'reason' => $validated['reason']],
            subjectType: ErpDocument::class,
            subjectId: $document->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'Dokumen telah dicabut. Halaman verifikasi publik langsung menampilkan status tidak berlaku.',
            'document' => $this->formatDocument(
                $document->fresh([
                    'owner',
                    'creator',
                    'supervisor',
                    'certificateTemplate',
                    'signatures.signer',
                ]),
                $actor,
            ),
        ]);
    }

    public function certificate(string $token)
    {
        $document = $this->findCertificate($token);

        return view('certificates.internship', [
            'document' => $document,
            'isValid' => $this->signing->verifyIntegrity($document) && ! $document->revoked_at,
            'qrDataUri' => $this->qrCode->dataUri(
                route('certificates.verify', ['token' => $document->verification_token]),
            ),
            'backgroundUrl' => $document->certificateTemplate
                ? route('certificates.background', ['token' => $document->verification_token])
                : null,
            'signatureUrl' => $document->signatures->first()?->image_path
                ? route('certificates.signature', ['token' => $document->verification_token])
                : null,
        ]);
    }

    public function verify(string $token)
    {
        $document = $this->findCertificate($token);

        return view('certificates.verify', [
            'document' => $document,
            'isValid' => $this->signing->verifyIntegrity($document) && ! $document->revoked_at,
        ]);
    }

    public function certificateBackground(string $token)
    {
        $document = $this->findCertificate($token);
        $template = $document->certificateTemplate;
        abort_unless(
            $template && Storage::disk('local')->exists($template->background_path),
            404,
        );

        return Storage::disk('local')->response(
            $template->background_path,
            null,
            [
                'Content-Type' => $template->background_mime,
                'Cache-Control' => 'public, max-age=86400',
            ],
        );
    }

    public function certificateSignature(string $token)
    {
        $document = $this->findCertificate($token);
        $signature = $document->signatures->first();
        abort_unless(
            $signature?->image_path
                && Storage::disk('local')->exists($signature->image_path),
            404,
        );

        return Storage::disk('local')->response(
            $signature->image_path,
            null,
            ['Cache-Control' => 'public, max-age=86400'],
        );
    }

    private function findCertificate(string $token): ErpDocument
    {
        return ErpDocument::query()
            ->with([
                'owner',
                'creator',
                'supervisor',
                'certificateTemplate',
                'signatures.signer',
            ])
            ->where('document_type', 'internship_certificate')
            ->where('verification_token', $token)
            ->firstOrFail();
    }

    private function formatDocument(ErpDocument $document, ?User $viewer = null): array
    {
        return [
            ...$document->toArray(),
            'certificate_url' => route('certificates.show', ['token' => $document->verification_token]),
            'verification_url' => route('certificates.verify', ['token' => $document->verification_token]),
            'integrity_valid' => $this->signing->verifyIntegrity($document),
            'document_hash_short' => $document->document_hash ? substr($document->document_hash, 0, 16) : null,
            'can_sign' => $viewer
                && $document->status === 'draft'
                && (
                    (int) $document->supervisor_user_id === (int) $viewer->id
                    || (! $document->supervisor_user_id && ($viewer->isCEO() || $viewer->isHRD()))
                ),
            'visual_signature' => (bool) $document->signatures->first()?->image_path,
        ];
    }

    private function nextCertificateNumber(): string
    {
        $prefix = 'SUBA/INT/'.now()->format('Y/m');
        $sequence = ErpDocument::query()
            ->where('document_type', 'internship_certificate')
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        do {
            $number = $prefix.'/'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (ErpDocument::query()->where('document_number', $number)->exists());

        return $number;
    }
}
