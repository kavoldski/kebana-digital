<?php
/**
 * Test script for verifying the new recursive chunking algorithm on sample text.
 * File: scratch/test_chunking.php
 */
require_once __DIR__ . '/../bootstrap.php';

// Sample document text containing paragraphs, sentences, and a financial table.
$sampleText = <<<TEXT
LAPORAN PRESTASI KEWANGAN PERSATUAN KENYAH BADENG SARAWAK (KEBANA)
Bagi Tahun Berakhir 31 Disember 2025.

Laporan ini disediakan oleh Jawatankuasa Kewangan untuk membentangkan status kewangan terkini persatuan. Semua transaksi telah diaudit dan diselaraskan mengikut piawaian perakaunan yang diluluskan oleh persatuan.

1. PENGENALAN
Persatuan Kenyah Badeng Sarawak (KEBANA) telah menunjukkan prestasi yang stabil pada tahun 2025. Peningkatan dalam yuran keahlian dan sumbangan daripada pihak luar telah membantu membiayai pelbagai aktiviti kebajikan dan pembangunan komuniti yang telah dirancang. Pihak pentadbiran juga telah berjaya mengurangkan kos operasi sebanyak 15% melalui langkah-langkah penjimatan yang berkesan.

2. RINGKASAN KEWANGAN (JADUAL PRESTASI)
Berikut adalah ringkasan pendapatan dan perbelanjaan bagi tahun 2024 dan 2025:

Butiran Kewangan          | Tahun 2024 (RM) | Tahun 2025 (RM) | Perbezaan (%)
-------------------------|-----------------|-----------------|--------------
Yuran Keahlian           | 12,500.00       | 15,200.00       | +21.6%
Sumbangan & Dana         | 45,000.00       | 60,000.00       | +33.3%
Pendapatan Acara Khas    | 8,000.00        | 12,500.00       | +56.2%
Jumlah Pendapatan        | 65,500.00       | 87,700.00       | +33.9%
Perbelanjaan Aktiviti    | 35,000.00       | 42,000.00       | +20.0%
Perbelanjaan Pentadbiran  | 18,000.00       | 15,300.00       | -15.0%
Jumlah Perbelanjaan      | 53,000.00       | 57,300.00       | +8.1%
Lebihan / (Defisit)      | 12,500.00       | 30,400.00       | +143.2%

Nota Khas: Lebihan sebanyak RM 30,400.00 pada tahun 2025 akan dibawa ke tahun hadapan untuk digunakan bagi tujuan Tabung Pendidikan Anak-Anak Kenyah Badeng yang bakal dilancarkan pada suku pertama tahun 2026.

3. AKTIVITI-AKTIVITI UTAMA PADA TAHUN 2025
Pihak persatuan telah menjalankan tiga program utama sepanjang tahun ini. Pertama, Program Jelajah Kasih KEBANA di Baram yang bertujuan untuk mengedarkan bantuan makanan dan barangan asas kepada keluarga yang memerlukan. Kedua, Pesta Kebudayaan Kenyah Badeng yang diadakan di Miri dengan penyertaan lebih daripada 500 orang ahli. Ketiga, Seminar Keusahawanan Digital untuk belia-belia persatuan bagi meningkatkan kemahiran ekonomi digital mereka.

Sebarang pertanyaan mengenai laporan kewangan ini boleh dikemukakan terus kepada Bendahari Agung KEBANA melalui e-mel rasmi persatuan di bendahari@kebana.org.my.
TEXT;

// Original naive character splitter logic
function originalChunkText($text, $chunkSize = 900, $overlap = 200) {
    $chunks = [];
    $textLength = mb_strlen($text);
    if ($textLength > 1000000) {
        $text = mb_substr($text, 0, 1000000);
        $textLength = 1000000;
    }
    if ($textLength <= $chunkSize) {
        return [trim($text)];
    }
    $start = 0;
    $maxChunks = 2000;
    while ($start < $textLength && count($chunks) < $maxChunks) {
        $end = min($start + $chunkSize, $textLength);
        $chunk = mb_substr($text, $start, $end - $start);
        if ($end < $textLength) {
            $lastSpace = mb_strrpos($chunk, ' ');
            $lastNewline = mb_strrpos($chunk, "\n");
            $breakPoint = max($lastSpace, $lastNewline);
            if ($breakPoint !== false && $breakPoint > ($chunkSize * 0.8)) {
                $chunk = mb_substr($chunk, 0, $breakPoint);
                $end = $start + mb_strlen($chunk);
            }
        }
        $trimmedChunk = trim($chunk);
        if (!empty($trimmedChunk)) {
            $chunks[] = $trimmedChunk;
        }
        $nextStart = $end - $overlap;
        if ($nextStart <= $start) {
            $start = $end;
        } else {
            $start = $nextStart;
        }
        if ($start >= $textLength) break;
    }
    return $chunks;
}

// New Recursive Character Splitter logic
function newChunkText($text, $chunkSize = 1200, $overlap = 200) {
    $textLength = mb_strlen($text);
    if ($textLength > 1000000) {
        $text = mb_substr($text, 0, 1000000);
        $textLength = 1000000;
    }
    if ($textLength <= $chunkSize) {
        return [trim($text)];
    }

    $separators = ["\n\n", "\n", ". ", " ", ""];
    return recursiveSplit($text, $separators, $chunkSize, $overlap);
}

function recursiveSplit($text, $separators, $chunkSize, $overlap) {
    $finalChunks = [];
    $separator = "";
    $nextSeparators = [];

    // Find the first separator that actually exists in the text
    foreach ($separators as $i => $sep) {
        if ($sep === "") {
            $separator = $sep;
            $nextSeparators = array_slice($separators, $i + 1);
            break;
        }
        if (mb_strpos($text, $sep) !== false) {
            $separator = $sep;
            $nextSeparators = array_slice($separators, $i + 1);
            break;
        }
    }

    // Split text by the separator
    if ($separator === "") {
        // Safe character splitting
        $splits = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
    } else {
        $splits = explode($separator, $text);
    }

    $goodSplits = [];
    foreach ($splits as $split) {
        if ($split !== "") {
            $goodSplits[] = $split;
        }
    }

    $currentChunk = [];
    $currentLength = 0;

    foreach ($goodSplits as $split) {
        $splitLength = mb_strlen($split);

        // If a single split is larger than chunkSize, we must split it recursively
        if ($splitLength > $chunkSize) {
            // Save current group if any
            if (!empty($currentChunk)) {
                $finalChunks[] = implode($separator, $currentChunk);
                $currentChunk = [];
                $currentLength = 0;
            }

            if (!empty($nextSeparators)) {
                $recursiveChunks = recursiveSplit($split, $nextSeparators, $chunkSize, $overlap);
                $finalChunks = array_merge($finalChunks, $recursiveChunks);
            } else {
                // Hard character chunking as a final resort
                $start = 0;
                while ($start < $splitLength) {
                    $finalChunks[] = mb_substr($split, $start, $chunkSize);
                    $start += ($chunkSize - $overlap);
                }
            }
        } else {
            // Check if adding this split exceeds chunkSize
            $separatorLength = !empty($currentChunk) ? mb_strlen($separator) : 0;
            if ($currentLength + $separatorLength + $splitLength > $chunkSize) {
                // Save current group
                if (!empty($currentChunk)) {
                    $finalChunks[] = implode($separator, $currentChunk);
                }

                // Carry over overlap by accumulating previous splits from the tail of currentChunk
                $overlapChunk = [];
                $overlapLength = 0;
                for ($i = count($currentChunk) - 1; $i >= 0; $i--) {
                    $prevSplit = $currentChunk[$i];
                    $prevLength = mb_strlen($prevSplit);
                    $sepLength = !empty($overlapChunk) ? mb_strlen($separator) : 0;
                    if ($overlapLength + $sepLength + $prevLength <= $overlap) {
                        array_unshift($overlapChunk, $prevSplit);
                        $overlapLength += $sepLength + $prevLength;
                    } else {
                        break;
                    }
                }

                $currentChunk = $overlapChunk;
                $currentLength = $overlapLength;
            }

            // Add current split
            $currentChunk[] = $split;
            $currentLength += (!empty($currentChunk) && $currentLength > $splitLength ? mb_strlen($separator) : 0) + $splitLength;
        }
    }

    // Add remaining chunk
    if (!empty($currentChunk)) {
        $finalChunks[] = implode($separator, $currentChunk);
    }

    // Post-processing: clean up and trim chunks
    $trimmedChunks = [];
    foreach ($finalChunks as $c) {
        $trimmed = trim($c);
        if ($trimmed !== "") {
            $trimmedChunks[] = $trimmed;
        }
    }

    return $trimmedChunks;
}

echo "=== RUNNING CHUNKING COMPARISON ===\n\n";

echo "--- ORIGINAL CHUNKING (Size: 900, Overlap: 200) ---\n";
$origChunks = originalChunkText($sampleText, 900, 200);
echo "Total Chunks: " . count($origChunks) . "\n";
foreach ($origChunks as $idx => $c) {
    echo "\n[Chunk " . ($idx + 1) . " - Length: " . mb_strlen($c) . " chars]\n";
    echo "--------------------------------------------------\n";
    echo $c . "\n";
    echo "--------------------------------------------------\n";
}

echo "\n\n=======================================================================\n\n";

echo "--- NEW RECURSIVE CHUNKING (Size: 1200, Overlap: 200) ---\n";
$newChunks = newChunkText($sampleText, 1200, 200);
echo "Total Chunks: " . count($newChunks) . "\n";
foreach ($newChunks as $idx => $c) {
    echo "\n[Chunk " . ($idx + 1) . " - Length: " . mb_strlen($c) . " chars]\n";
    echo "--------------------------------------------------\n";
    echo $c . "\n";
    echo "--------------------------------------------------\n";
}
