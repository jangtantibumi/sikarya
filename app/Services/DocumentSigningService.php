<?php

namespace App\Services;

use App\Models\DocumentSignature;
use App\Models\ErpDocument;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentSigningService
{
    public function sign(ErpDocument $document, User $signer): ErpDocument
    {
        return DB::transaction(function () use ($document, $signer): ErpDocument {
            $signedAt = now();
            $signatureImagePath = null;
            $signatureImageHash = null;
            if ($signer->signature_image_path && Storage::disk('local')->exists($signer->signature_image_path)) {
                $extension = pathinfo($signer->signature_image_path, PATHINFO_EXTENSION) ?: 'png';
                $signatureImagePath = 'document-signatures/'
                    .$document->id.'-'.Str::uuid().'.'.$extension;
                Storage::disk('local')->copy($signer->signature_image_path, $signatureImagePath);
                $signatureImageHash = hash(
                    'sha256',
                    Storage::disk('local')->get($signatureImagePath),
                );
            }
            $documentHash = hash('sha256', json_encode([
                'number' => $document->document_number,
                'title' => $document->title,
                'issued_at' => $document->issued_at?->format('Y-m-d'),
                'content' => $document->content,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            $signatureHash = hash_hmac(
                'sha256',
                implode('|', [
                    $documentHash,
                    $signer->id,
                    $signer->job_title ?: $signer->role,
                    $signedAt->toIso8601String(),
                    $signatureImageHash ?? '',
                ]),
                (string) config('app.key'),
            );

            DocumentSignature::query()->updateOrCreate(
                ['document_id' => $document->id, 'signer_id' => $signer->id],
                [
                    'signer_role' => $signer->job_title ?: $signer->role,
                    'signature_method' => 'internal_authenticated_approval',
                    'signature_hash' => $signatureHash,
                    'image_path' => $signatureImagePath,
                    'metadata' => [
                        'authentication' => 'active_erp_session',
                        'signature_level' => 'internal',
                        'hash_version' => 2,
                        'signature_image_hash' => $signatureImageHash,
                        'signature_consented_at' => $signer->signature_consented_at?->toIso8601String(),
                    ],
                    'signed_at' => $signedAt,
                ],
            );

            $document->forceFill([
                'status' => 'signed',
                'document_hash' => $documentHash,
                'signed_at' => $signedAt,
                'issued_at' => $document->issued_at ?: $signedAt->toDateString(),
            ])->save();

            return $document->fresh([
                'owner',
                'creator',
                'supervisor',
                'certificateTemplate',
                'signatures.signer',
            ]);
        });
    }

    public function verifyIntegrity(ErpDocument $document): bool
    {
        if (! $document->document_hash || $document->status !== 'signed') {
            return false;
        }

        $currentHash = hash('sha256', json_encode([
            'number' => $document->document_number,
            'title' => $document->title,
            'issued_at' => $document->issued_at?->format('Y-m-d'),
            'content' => $document->content,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        if (! hash_equals($document->document_hash, $currentHash)) {
            return false;
        }

        $signatures = $document->relationLoaded('signatures')
            ? $document->signatures
            : $document->signatures()->get();

        return $signatures->contains(function (DocumentSignature $signature) use ($document): bool {
            if (! $signature->signed_at) {
                return false;
            }

            $parts = [
                $document->document_hash,
                $signature->signer_id,
                $signature->signer_role,
                $signature->signed_at->toIso8601String(),
            ];
            if ((int) data_get($signature->metadata, 'hash_version', 1) >= 2) {
                $storedImageHash = data_get($signature->metadata, 'signature_image_hash');
                if ($storedImageHash) {
                    if (! $signature->image_path || ! Storage::disk('local')->exists($signature->image_path)) {
                        return false;
                    }
                    $currentImageHash = hash(
                        'sha256',
                        Storage::disk('local')->get($signature->image_path),
                    );
                    if (! hash_equals((string) $storedImageHash, $currentImageHash)) {
                        return false;
                    }
                }
                $parts[] = $storedImageHash ?? '';
            }

            $expected = hash_hmac(
                'sha256',
                implode('|', $parts),
                (string) config('app.key'),
            );

            return hash_equals($expected, $signature->signature_hash);
        });
    }
}
