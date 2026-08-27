<?php

namespace App\Services\AI;

use App\Models\CompanyDocument;
use App\Models\CompanyKnowledgeChunk;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class CompanyKnowledgeService
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function __construct(
        private ?DocumentTextExtractor $extractor = null,
        private ?ImageUnderstandingService $images = null,
    ) {
        $this->extractor ??= new DocumentTextExtractor;
        $this->images ??= new ImageUnderstandingService;
    }

    public function storeDocument(User $uploader, UploadedFile $file, ?string $customName = null): CompanyDocument
    {
        $name = $customName ?? $file->getClientOriginalName();
        $path = $file->store("company-documents/{$uploader->id}");
        $extension = strtolower($file->getClientOriginalExtension());

        $document = CompanyDocument::create([
            'uploaded_by' => $uploader->id,
            'name' => $name,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extracted_text' => null,
            'processing_status' => 'processando',
            'metadata' => ['original_name' => $file->getClientOriginalName()],
        ]);

        try {
            if (in_array($extension, self::IMAGE_EXTENSIONS, true)) {
                $result = $this->images->understand($path);
                $text = $result['text'];
                $status = $result['status'];
            } else {
                $text = $this->extractor->extractFromPath($path);
                $status = $text !== '' ? 'pronto' : 'needs_ocr';
            }

            $document->update([
                'extracted_text' => $text !== '' ? $text : null,
                'processing_status' => $status,
                'processing_error' => $status === 'pronto' ? null : 'Não foi possível extrair texto do arquivo.',
            ]);

            if ($text !== '') {
                $this->chunkAndStore($document, $text);
            }
        } catch (\Throwable $e) {
            $document->update([
                'processing_status' => 'erro',
                'processing_error' => $e->getMessage(),
            ]);
            report($e);
        }

        return $document->fresh();
    }

    /**
     * @return Collection<int, CompanyKnowledgeChunk>
     */
    public function retrieve(string $query, ?int $limit = null): Collection
    {
        $limit ??= config('ai.knowledge.max_chunks_per_query', 5);
        $terms = $this->extractTerms($query);

        if ($terms === []) {
            return CompanyKnowledgeChunk::query()->limit($limit)->get();
        }

        $queryBuilder = CompanyKnowledgeChunk::query();

        foreach ($terms as $term) {
            $queryBuilder->where('content', 'like', "%{$term}%");
        }

        $results = $queryBuilder->orderBy('order')->limit($limit)->get();

        if ($results->isEmpty()) {
            $results = CompanyKnowledgeChunk::query()
                ->where(function ($q) use ($terms) {
                    foreach ($terms as $term) {
                        $q->orWhere('content', 'like', "%{$term}%");
                    }
                })
                ->orderBy('order')
                ->limit($limit)
                ->get();
        }

        return $results;
    }

    /**
     * @param  list<int>  $documentIds
     * @return Collection<int, CompanyKnowledgeChunk>
     */
    public function retrieveByDocuments(array $documentIds, ?int $limit = null): Collection
    {
        $limit ??= config('ai.knowledge.max_chunks_per_query', 5);

        if ($documentIds === []) {
            return collect();
        }

        return CompanyKnowledgeChunk::query()
            ->whereIn('document_id', $documentIds)
            ->orderBy('order')
            ->limit($limit)
            ->get();
    }

    public function deleteDocument(CompanyDocument $document): void
    {
        $document->chunks()->delete();

        if ($document->path && Storage::exists($document->path)) {
            Storage::delete($document->path);
        }

        $document->delete();
    }

    private function chunkAndStore(CompanyDocument $document, string $text): void
    {
        $chunks = $this->chunk($text);
        $order = 0;

        foreach ($chunks as $chunk) {
            CompanyKnowledgeChunk::create([
                'document_id' => $document->id,
                'content' => $chunk,
                'order' => $order++,
            ]);
        }
    }

    /**
     * @return list<string>
     */
    private function chunk(string $text): array
    {
        $maxSize = config('ai.knowledge.chunk_size', 800);
        $overlap = config('ai.knowledge.chunk_overlap', 80);

        $paragraphs = array_filter(array_map('trim', explode("\n\n", $text)));
        $chunks = [];
        $current = '';

        foreach ($paragraphs as $paragraph) {
            if (mb_strlen($current) + mb_strlen($paragraph) > $maxSize && $current !== '') {
                $chunks[] = $current;
                $current = mb_substr($current, -$overlap);
            }

            $current .= ($current === '' ? '' : "\n\n").$paragraph;
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        if ($chunks === []) {
            $chunks[] = $text;
        }

        return $chunks;
    }

    /**
     * @return list<string>
     */
    private function extractTerms(string $query): array
    {
        $query = mb_strtolower($query);
        $query = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $query) ?? $query;
        $terms = array_filter(explode(' ', $query));

        return array_values(array_filter($terms, fn ($term) => mb_strlen($term) >= 3));
    }
}
