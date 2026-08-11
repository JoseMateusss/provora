<?php

namespace Tests\Unit;

use App\Exceptions\DocumentExtractionFailedException;
use App\Services\Documents\PdfExtractorService;
use Mockery;
use PHPUnit\Framework\TestCase;
use Smalot\PdfParser\Document as ParsedDocument;
use Smalot\PdfParser\Parser;

class PdfExtractorServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_sanitizes_control_characters_from_text(): void
    {
        $service = new PdfExtractorService();

        $dirtyText = "Olá\x00, mundo!\x07 \nLinha 2\x1F.";
        $cleanText = $service->sanitizeText($dirtyText);

        $this->assertEquals("Olá, mundo! \nLinha 2.", $cleanText);
    }

    public function test_throws_exception_if_file_does_not_exist(): void
    {
        $service = new PdfExtractorService();

        $this->expectException(DocumentExtractionFailedException::class);
        $this->expectExceptionMessage('Arquivo PDF não foi encontrado');

        $service->extract('/caminho/invalido/arquivo.pdf');
    }

    public function test_extracts_and_sanitizes_text_successfully(): void
    {
        $mockParser = Mockery::mock(Parser::class);
        $mockDocument = Mockery::mock(ParsedDocument::class);

        $mockParser->shouldReceive('parseFile')
            ->once()
            ->with('/tmp/sample.pdf')
            ->andReturn($mockDocument);

        $mockDocument->shouldReceive('getText')
            ->once()
            ->andReturn("Conteúdo do PDF\x00 de teste.");

        $service = new PdfExtractorService($mockParser);

        // Criar arquivo temporário para passar na checagem de file_exists
        $tempPath = tempnam(sys_get_temp_dir(), 'pdf_test');
        rename($tempPath, '/tmp/sample.pdf');

        try {
            $extracted = $service->extract('/tmp/sample.pdf');
            $this->assertEquals('Conteúdo do PDF de teste.', $extracted);
        } finally {
            if (file_exists('/tmp/sample.pdf')) {
                unlink('/tmp/sample.pdf');
            }
        }
    }
}
