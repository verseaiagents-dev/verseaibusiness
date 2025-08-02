#!/usr/bin/env python3
"""
OpenAI Model Fiyatlandırma Hesaplayıcısı
GPT-4o Mini ve text-embedding-ada-002 için maliyet hesaplama
"""

class OpenAICostCalculator:
    def __init__(self):
        # GPT-4o Mini fiyatları (1K tokens başına USD)
        self.gpt4o_mini_prices = {
            'input': 0.00015,   # $0.00015 per 1K tokens
            'output': 0.0006    # $0.0006 per 1K tokens
        }
        
        # text-embedding-ada-002 fiyatı
        self.embedding_price = 0.0001  # $0.0001 per 1K tokens
    
    def estimate_tokens(self, text, language='turkish'):
        """
        Metin için token sayısını tahmin eder
        """
        words = len(text.split())
        
        if language == 'turkish':
            # Türkçe için 1 token ≈ 0.6 kelime
            tokens = words / 0.6
        else:
            # İngilizce için 1 token ≈ 0.75 kelime
            tokens = words / 0.75
        
        return int(tokens)
    
    def calculate_chat_cost(self, input_text, output_text, language='turkish'):
        """
        GPT-4o Mini chat maliyetini hesaplar
        """
        input_tokens = self.estimate_tokens(input_text, language)
        output_tokens = self.estimate_tokens(output_text, language)
        
        input_cost = (input_tokens / 1000) * self.gpt4o_mini_prices['input']
        output_cost = (output_tokens / 1000) * self.gpt4o_mini_prices['output']
        
        total_cost = input_cost + output_cost
        
        return {
            'input_tokens': input_tokens,
            'output_tokens': output_tokens,
            'total_tokens': input_tokens + output_tokens,
            'input_cost': input_cost,
            'output_cost': output_cost,
            'total_cost': total_cost,
            'total_cost_usd': f"${total_cost:.6f}",
            'total_cost_try': f"₺{total_cost * 32:.4f}"  # 1 USD = 32 TRY (yaklaşık)
        }
    
    def calculate_embedding_cost(self, text, language='turkish'):
        """
        text-embedding-ada-002 embedding maliyetini hesaplar
        """
        tokens = self.estimate_tokens(text, language)
        cost = (tokens / 1000) * self.embedding_price
        
        return {
            'tokens': tokens,
            'cost': cost,
            'cost_usd': f"${cost:.6f}",
            'cost_try': f"₺{cost * 32:.4f}"
        }
    
    def calculate_bulk_embedding_cost(self, texts, language='turkish'):
        """
        Birden fazla metin için embedding maliyetini hesaplar
        """
        total_tokens = 0
        total_cost = 0
        
        for text in texts:
            tokens = self.estimate_tokens(text, language)
            total_tokens += tokens
            total_cost += (tokens / 1000) * self.embedding_price
        
        return {
            'total_texts': len(texts),
            'total_tokens': total_tokens,
            'total_cost': total_cost,
            'cost_usd': f"${total_cost:.6f}",
            'cost_try': f"₺{total_cost * 32:.4f}",
            'average_cost_per_text': total_cost / len(texts) if texts else 0
        }

def main():
    calculator = OpenAICostCalculator()
    
    print("=== OpenAI Model Fiyatlandırma Hesaplayıcısı ===\n")
    
    # Örnek kullanım
    print("1. GPT-4o Mini Chat Örneği:")
    input_text = "Merhaba, bugün hava nasıl?"
    output_text = "Merhaba! Bugün hava güzel görünüyor. Size nasıl yardımcı olabilirim?"
    
    chat_result = calculator.calculate_chat_cost(input_text, output_text)
    print(f"Giriş metni: {input_text}")
    print(f"Çıkış metni: {output_text}")
    print(f"Giriş tokens: {chat_result['input_tokens']}")
    print(f"Çıkış tokens: {chat_result['output_tokens']}")
    print(f"Toplam maliyet: {chat_result['total_cost_usd']} ({chat_result['total_cost_try']})")
    
    print("\n" + "="*50 + "\n")
    
    print("2. Embedding Örneği:")
    embedding_text = "Bu bir örnek metin. Embedding için kullanılacak."
    embedding_result = calculator.calculate_embedding_cost(embedding_text)
    print(f"Metin: {embedding_text}")
    print(f"Tokens: {embedding_result['tokens']}")
    print(f"Maliyet: {embedding_result['cost_usd']} ({embedding_result['cost_try']})")
    
    print("\n" + "="*50 + "\n")
    
    print("3. Toplu Embedding Örneği:")
    texts = [
        "İlk örnek metin",
        "İkinci örnek metin",
        "Üçüncü örnek metin",
        "Dördüncü örnek metin"
    ]
    bulk_result = calculator.calculate_bulk_embedding_cost(texts)
    print(f"Toplam metin sayısı: {bulk_result['total_texts']}")
    print(f"Toplam tokens: {bulk_result['total_tokens']}")
    print(f"Toplam maliyet: {bulk_result['cost_usd']} ({bulk_result['cost_try']})")
    print(f"Metin başına ortalama: ${bulk_result['average_cost_per_text']:.6f}")

if __name__ == "__main__":
    main() 