<?php

class BlogAIHelper {
    private $apiKey;
    private $apiEndpoint = 'https://api.openai.com/v1/chat/completions';
    private $model = 'gpt-4o-mini';

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI($messages, $temperature = 0.7) {
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ];

        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => 1000,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiEndpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('OpenAI API error (HTTP ' . $httpCode . '): ' . $response);
        }

        $result = json_decode($response, true);
        if (!isset($result['choices'][0]['message']['content'])) {
            throw new Exception('Unexpected OpenAI API response format');
        }

        return trim($result['choices'][0]['message']['content']);
    }

    /**
     * Generate excerpt from content
     */
    public function generateExcerpt($title, $content) {
        $plainText = strip_tags($content);
        $truncated = substr($plainText, 0, 500);

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a professional editor. Generate a compelling 2-3 sentence excerpt for a blog post. Be concise, engaging, and informative. Do not include quotes or special formatting.'
            ],
            [
                'role' => 'user',
                'content' => 'Title: ' . $title . "\n\nContent: " . $truncated
            ]
        ];

        return $this->callOpenAI($messages, 0.7);
    }

    /**
     * Generate SEO title
     */
    public function generateSEOTitle($title, $content) {
        $plainText = strip_tags($content);
        $truncated = substr($plainText, 0, 300);

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an SEO expert. Generate a compelling SEO title (50-60 characters) for a blog post. Include the main keyword naturally. Do not use quotes.'
            ],
            [
                'role' => 'user',
                'content' => 'Title: ' . $title . "\n\nContent: " . $truncated
            ]
        ];

        return $this->callOpenAI($messages, 0.5);
    }

    /**
     * Generate SEO description/meta description
     */
    public function generateSEODescription($title, $content) {
        $plainText = strip_tags($content);
        $truncated = substr($plainText, 0, 300);

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an SEO expert. Generate a concise meta description (150-160 characters) for a blog post. Be compelling and include relevant keywords naturally. Do not use quotes.'
            ],
            [
                'role' => 'user',
                'content' => 'Title: ' . $title . "\n\nContent: " . $truncated
            ]
        ];

        return $this->callOpenAI($messages, 0.5);
    }

    /**
     * Generate tags/keywords
     */
    public function generateTags($title, $content, $category = '') {
        $plainText = strip_tags($content);
        $truncated = substr($plainText, 0, 400);

        $categoryContext = !empty($category) ? "Category: " . $category . "\n\n" : '';

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a content strategist. Generate 5-8 relevant tags/keywords for a blog post. Return them as comma-separated values only, no numbering or quotes. Make them specific and searchable.'
            ],
            [
                'role' => 'user',
                'content' => 'Title: ' . $title . "\n\n" . $categoryContext . "Content: " . $truncated
            ]
        ];

        return $this->callOpenAI($messages, 0.6);
    }

    /**
     * Calculate reading time and analyze content
     */
    public function analyzeContent($content) {
        $plainText = strip_tags($content);
        $wordCount = str_word_count($plainText);
        $readingTimeMinutes = ceil($wordCount / 200); // Average reading speed: 200 words per minute

        return [
            'word_count' => $wordCount,
            'reading_time' => max(1, $readingTimeMinutes),
            'paragraph_count' => substr_count($plainText, "\n"),
            'character_count' => strlen($plainText),
        ];
    }
}

?>
