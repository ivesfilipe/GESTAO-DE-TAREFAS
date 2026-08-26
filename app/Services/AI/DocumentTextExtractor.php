<?php

namespace App\Services\AI;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Smalot\PdfParser\Parser;

class DocumentTextExtractor
{
    /**
     * Extrai texto de um arquivo suportado.
     *
     * Formatos suportados: txt, md, pdf, docx.
     */
    public function extractFromPath(string $path): string
    {
        if (! Storage::exists($path)) {
            throw new RuntimeException("Arquivo não encontrado: {$path}");
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'txt', 'md' => $this->extractText($path),
            'pdf' => $this->extractPdf($path),
            'docx' => $this->extractDocx($path),
            default => throw new RuntimeException("Extensão não suportada: {$extension}"),
        };
    }

    public function extractFromUpload(UploadedFile $file): string
    {
        $path = $file->store('temp/extract');

        try {
            return $this->extractFromPath($path);
        } finally {
            Storage::delete($path);
        }
    }

    private function extractText(string $path): string
    {
        $content = Storage::get($path);

        return $this->clean((string) $content);
    }

    private function extractPdf(string $path): string
    {
        if (! class_exists(Parser::class)) {
            throw new RuntimeException('Extrator de PDF não disponível. Instale smalot/pdfparser.');
        }

        $parser = new Parser;
        $pdf = $parser->parseFile(Storage::path($path));

        return $this->clean($pdf->getText());
    }

    private function extractDocx(string $path): string
    {
        $fullPath = Storage::path($path);
        $zip = new \ZipArchive;

        if ($zip->open($fullPath) !== true) {
            throw new RuntimeException('Não foi possível abrir o documento DOCX.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            throw new RuntimeException('Conteúdo do DOCX não encontrado.');
        }

        $text = strip_tags(str_replace(['<w:p>', '<w:br/>', '<w:t/>'], ["\n\n", "\n", ' '], $xml));

        return $this->clean($text);
    }

    private function clean(string $text): string
    {
        $text = preg_replace('/\r\n?/', "\n", $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text) ?? $text;

        return trim($text);
    }
}
