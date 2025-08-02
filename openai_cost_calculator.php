<?php
/**
 * OpenAI Model Fiyatlandırma Hesaplayıcısı
 * GPT-4o Mini ve text-embedding-ada-002 için maliyet hesaplama
 */

class OpenAICostCalculator {
    private $gpt4o_mini_prices;
    private $embedding_price;
    
    public function __construct() {
        // GPT-4o Mini fiyatları (1K tokens başına USD)
        $this->gpt4o_mini_prices = [
            'input' => 0.00015,   // $0.00015 per 1K tokens
            'output' => 0.0006    // $0.0006 per 1K tokens
        ];
        
        // text-embedding-ada-002 fiyatı
        $this->embedding_price = 0.0001;  // $0.0001 per 1K tokens
    }
    
    /**
     * Metin için token sayısını tahmin eder
     */
    public function estimateTokens($text, $language = 'turkish') {
        $words = count(explode(' ', $text));
        
        if ($language === 'turkish') {
            // Türkçe için 1 token ≈ 0.6 kelime
            $tokens = $words / 0.6;
        } else {
            // İngilizce için 1 token ≈ 0.75 kelime
            $tokens = $words / 0.75;
        }
        
        return (int) $tokens;
    }
    
    /**
     * GPT-4o Mini chat maliyetini hesaplar
     */
    public function calculateChatCost($inputText, $outputText, $language = 'turkish') {
        $inputTokens = $this->estimateTokens($inputText, $language);
        $outputTokens = $this->estimateTokens($outputText, $language);
        
        $inputCost = ($inputTokens / 1000) * $this->gpt4o_mini_prices['input'];
        $outputCost = ($outputTokens / 1000) * $this->gpt4o_mini_prices['output'];
        
        $totalCost = $inputCost + $outputCost;
        
        return [
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_tokens' => $inputTokens + $outputTokens,
            'input_cost' => $inputCost,
            'output_cost' => $outputCost,
            'total_cost' => $totalCost,
            'total_cost_usd' => '$' . number_format($totalCost, 6),
            'total_cost_try' => '₺' . number_format($totalCost * 32, 4)  // 1 USD = 32 TRY (yaklaşık)
        ];
    }
    
    /**
     * text-embedding-ada-002 embedding maliyetini hesaplar
     */
    public function calculateEmbeddingCost($text, $language = 'turkish') {
        $tokens = $this->estimateTokens($text, $language);
        $cost = ($tokens / 1000) * $this->embedding_price;
        
        return [
            'tokens' => $tokens,
            'cost' => $cost,
            'cost_usd' => '$' . number_format($cost, 6),
            'cost_try' => '₺' . number_format($cost * 32, 4)
        ];
    }
    
    /**
     * Birden fazla metin için embedding maliyetini hesaplar
     */
    public function calculateBulkEmbeddingCost($texts, $language = 'turkish') {
        $totalTokens = 0;
        $totalCost = 0;
        
        foreach ($texts as $text) {
            $tokens = $this->estimateTokens($text, $language);
            $totalTokens += $tokens;
            $totalCost += ($tokens / 1000) * $this->embedding_price;
        }
        
        return [
            'total_texts' => count($texts),
            'total_tokens' => $totalTokens,
            'total_cost' => $totalCost,
            'cost_usd' => '$' . number_format($totalCost, 6),
            'cost_try' => '₺' . number_format($totalCost * 32, 4),
            'average_cost_per_text' => count($texts) > 0 ? $totalCost / count($texts) : 0
        ];
    }
    
    /**
     * Aylık kullanım tahmini hesaplar
     */
    public function calculateMonthlyEstimate($dailyChats, $avgInputTokens, $avgOutputTokens, $dailyEmbeddings = 0, $avgEmbeddingTokens = 0) {
        $monthlyChats = $dailyChats * 30;
        $monthlyEmbeddings = $dailyEmbeddings * 30;
        
        // Chat maliyeti
        $chatInputCost = ($avgInputTokens / 1000) * $this->gpt4o_mini_prices['input'] * $monthlyChats;
        $chatOutputCost = ($avgOutputTokens / 1000) * $this->gpt4o_mini_prices['output'] * $monthlyChats;
        $totalChatCost = $chatInputCost + $chatOutputCost;
        
        // Embedding maliyeti
        $totalEmbeddingCost = ($avgEmbeddingTokens / 1000) * $this->embedding_price * $monthlyEmbeddings;
        
        $totalMonthlyCost = $totalChatCost + $totalEmbeddingCost;
        
        return [
            'monthly_chats' => $monthlyChats,
            'monthly_embeddings' => $monthlyEmbeddings,
            'chat_cost' => $totalChatCost,
            'embedding_cost' => $totalEmbeddingCost,
            'total_cost' => $totalMonthlyCost,
            'total_cost_usd' => '$' . number_format($totalMonthlyCost, 2),
            'total_cost_try' => '₺' . number_format($totalMonthlyCost * 32, 2)
        ];
    }
}

// Kullanım örneği
function demo() {
    $calculator = new OpenAICostCalculator();
    
    echo "=== OpenAI Model Fiyatlandırma Hesaplayıcısı ===\n\n";
    
    // 1. Chat örneği
    echo "1. GPT-4o Mini Chat Örneği:\n";
    $inputText = "Merhaba, bugün hava nasıl?";
    $outputText = "Merhaba! Bugün hava güzel görünüyor. Size nasıl yardımcı olabilirim?";
    
    $chatResult = $calculator->calculateChatCost($inputText, $outputText);
    echo "Giriş metni: {$inputText}\n";
    echo "Çıkış metni: {$outputText}\n";
    echo "Giriş tokens: {$chatResult['input_tokens']}\n";
    echo "Çıkış tokens: {$chatResult['output_tokens']}\n";
    echo "Toplam maliyet: {$chatResult['total_cost_usd']} ({$chatResult['total_cost_try']})\n";
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
    
    // 2. Embedding örneği
    echo "2. Embedding Örneği:\n";
    $embeddingText = "Bu bir örnek metin. Embedding için kullanılacak.";
    $embeddingResult = $calculator->calculateEmbeddingCost($embeddingText);
    echo "Metin: {$embeddingText}\n";
    echo "Tokens: {$embeddingResult['tokens']}\n";
    echo "Maliyet: {$embeddingResult['cost_usd']} ({$embeddingResult['cost_try']})\n";
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
    
    // 3. Aylık tahmin
    echo "3. Aylık Kullanım Tahmini:\n";
    $monthlyEstimate = $calculator->calculateMonthlyEstimate(
        dailyChats: 100,      // Günlük 100 chat
        avgInputTokens: 50,   // Ortalama 50 input token
        avgOutputTokens: 100, // Ortalama 100 output token
        dailyEmbeddings: 50,  // Günlük 50 embedding
        avgEmbeddingTokens: 200 // Ortalama 200 embedding token
    );
    
    echo "Aylık chat sayısı: {$monthlyEstimate['monthly_chats']}\n";
    echo "Aylık embedding sayısı: {$monthlyEstimate['monthly_embeddings']}\n";
    echo "Chat maliyeti: {$monthlyEstimate['chat_cost_usd']}\n";
    echo "Embedding maliyeti: {$monthlyEstimate['embedding_cost_usd']}\n";
    echo "Toplam aylık maliyet: {$monthlyEstimate['total_cost_usd']} ({$monthlyEstimate['total_cost_try']})\n";
}

// Demo çalıştır
if (php_sapi_name() === 'cli') {
    demo();
}
?> 