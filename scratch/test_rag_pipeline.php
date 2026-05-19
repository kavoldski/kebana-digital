<?php
/**
 * Test RAG pipeline search, adjacent chunk expansion, scoring, and LLM synthesis.
 */
require_once __DIR__ . '/../bootstrap.php';

use App\Services\RAGService;

echo "--- TESTING RAG SYSTEM IMPROVEMENTS ---\n\n";

function runTestQuery($query) {
    echo "==================================================\n";
    echo "QUERY: \"{$query}\"\n";
    echo "==================================================\n";
    
    // 1. Test first-stage search (semantic + lexical boost + deduplication)
    $startSearch = microtime(true);
    $chunks = RAGService::search($query, 3);
    $searchTime = round((microtime(true) - $startSearch) * 1000, 2);
    
    echo "Retrieved Chunks Count: " . count($chunks) . " (Time: {$searchTime}ms)\n\n";
    
    if (empty($chunks)) {
        echo "NO MATCHES FOUND (Similarity below threshold 0.50)\n\n";
    } else {
        foreach ($chunks as $idx => $chunk) {
            $score = round($chunk['score'], 4);
            $docName = $chunk['doc_name'];
            $file = $chunk['file_path'];
            $index = $chunk['chunk_index'];
            
            echo "Match #" . ($idx + 1) . ":\n";
            echo "  Document: {$docName}\n";
            echo "  File Path: {$file}\n";
            echo "  Chunk Index: {$index}\n";
            echo "  Final Match Score: {$score}\n";
            echo "  Text Preview: " . substr(str_replace("\n", " ", $chunk['chunk_text']), 0, 150) . "...\n\n";
        }
    }
    
    // 2. Test full RAG synthesis ask pipeline
    echo "Running LLM ask synthesis...\n";
    $startAsk = microtime(true);
    $res = RAGService::ask($query);
    $askTime = round((microtime(true) - $startAsk) * 1000, 2);
    
    echo "Synthesis Finished (Time: {$askTime}ms)\n";
    echo "ANSWER:\n";
    echo "--------------------------------------------------\n";
    echo $res['answer'] . "\n";
    echo "--------------------------------------------------\n\n";
}

// Test 1: Specific query matching content in the document (Malay)
runTestQuery("Siapakah pembina atau pembangun Digital Management System untuk Persatuan Kenyah Badeng Sarawak?");

// Test 2: Specific query matching content in the document (English)
runTestQuery("What is the bachelor degree of the author Cedric Hilary Samah?");

// Test 3: Unrelated query that should return no matches or fall back to "tidak dijumpai"
runTestQuery("Berapakah jumlah perbelanjaan untuk resit pembeliaan barangan runcit?");
