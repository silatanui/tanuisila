<?php

class BlogAIHelper {
    private $apiKey;
    private $apiEndpoint = 'https://api.openai.com/v1/chat/completions';
    private $model = 'gpt-4o-mini';

    public function __construct($apiKey) {
        $this->apiKey = trim((string)$apiKey);
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI($messages, $temperature = 0.7, $jsonMode = false) {
        if (empty($this->apiKey)) {
            throw new Exception('OpenAI API key is not configured. Please set OPENAI_API_KEY in your .env or config file.');
        }

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ];

        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => 1200,
        ];

        if ($jsonMode) {
            $data['response_format'] = ['type' => 'json_object'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiEndpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 35);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!empty($curlError)) {
            throw new Exception('OpenAI Connection Error: ' . $curlError);
        }

        if ($httpCode !== 200) {
            $errorDetail = '';
            $decoded = json_decode($response, true);
            if (isset($decoded['error']['message'])) {
                $errorDetail = ': ' . $decoded['error']['message'];
            }
            throw new Exception('OpenAI API error (HTTP ' . $httpCode . ')' . $errorDetail);
        }

        $result = json_decode($response, true);
        if (!isset($result['choices'][0]['message']['content'])) {
            throw new Exception('Unexpected OpenAI API response structure');
        }

        return trim($result['choices'][0]['message']['content']);
    }

    /**
     * Extract plain text from HTML or Markdown content
     */
    private function cleanContentText($content, $maxLen = 3500) {
        $text = html_entity_decode(strip_tags((string)$content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return substr(trim($text), 0, $maxLen);
    }

    /**
     * Generate complete blog metadata from content in ONE comprehensive AI call
     */
    public function generateAllFromContent($content, $existingTitle = '', $existingCategory = '') {
        $cleaned = $this->cleanContentText($content, 4000);
        if (empty($cleaned)) {
            throw new Exception('Blog content is empty. Please provide article text.');
        }

        $analysis = $this->analyzeContent($content);

        $systemPrompt = "You are an elite editorial, content strategy, and SEO assistant for a high-end tech portfolio and engineering blog.\n"
            . "Analyze the provided article content and generate a comprehensive, publication-ready metadata package in valid JSON.\n"
            . "Follow these guidelines strictly:\n"
            . "1. title: A compelling, authoritative, executive-level headline (no quotation marks). If the content has a leading title heading, refine and polish it.\n"
            . "2. slug: A URL-friendly lowercase slug matching the title (e.g. 'deep-learning-advancements-2026').\n"
            . "3. category: A concise, professional category (e.g. 'Artificial Intelligence', 'Software Engineering', 'Machine Learning', 'Cloud & DevOps', 'Web Development', 'Academic Research', 'Data Science', 'Technology & Innovation').\n"
            . "4. tags: 5 to 8 relevant, high-search-intent comma-separated tags (e.g. 'AI, Neural Networks, Python, Deep Learning').\n"
            . "5. excerpt: A polished, engaging 2-3 sentence executive abstract/summary capturing the core thesis and value.\n"
            . "6. seo_title: An SEO-optimized title under 60 characters with primary keywords.\n"
            . "7. seo_description: An engaging meta description between 140-160 characters for search engine previews.\n"
            . "Return ONLY a valid JSON object with keys: title, slug, category, tags, excerpt, seo_title, seo_description.";

        $userPrompt = "";
        if (!empty($existingTitle)) {
            $userPrompt .= "Existing Working Title: " . $existingTitle . "\n";
        }
        if (!empty($existingCategory)) {
            $userPrompt .= "Existing Category: " . $existingCategory . "\n";
        }
        $userPrompt .= "Article Content:\n\n" . $cleaned;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];

        $rawJson = $this->callOpenAI($messages, 0.6, true);
        
        // Clean possible markdown fences
        $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($rawJson));
        $data = json_decode($cleanJson, true);

        if (!is_array($data)) {
            throw new Exception('Failed to parse AI JSON response: ' . $rawJson);
        }

        // Ensure title is present
        $title = trim($data['title'] ?? '');
        if ($title === '' && !empty($existingTitle)) {
            $title = $existingTitle;
        }

        // Ensure slug is present
        $slug = trim($data['slug'] ?? '');
        if ($slug === '' && $title !== '') {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
            $slug = trim($slug, '-');
        }

        return [
            'title' => $title,
            'slug' => $slug,
            'category' => trim($data['category'] ?? ($existingCategory ?: 'Technology')),
            'tags' => trim($data['tags'] ?? ''),
            'excerpt' => trim($data['excerpt'] ?? ''),
            'seo_title' => trim($data['seo_title'] ?? $title),
            'seo_description' => trim($data['seo_description'] ?? ''),
            'reading_time' => $analysis['reading_time'],
            'word_count' => $analysis['word_count'],
            'analysis' => $analysis,
        ];
    }

    /**
     * Generate title from content
     */
    public function generateTitle($content) {
        $cleaned = $this->cleanContentText($content, 3000);
        if (empty($cleaned)) {
            throw new Exception('Content is empty.');
        }

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a professional editor. Generate a single, compelling, authoritative headline for this blog post. Return only the title text with no quotation marks, prefixes, or formatting.'
            ],
            [
                'role' => 'user',
                'content' => "Content:\n\n" . $cleaned
            ]
        ];

        $title = trim($this->callOpenAI($messages, 0.6));
        return trim($title, " \t\n\r\0\x0B\"'");
    }

    /**
     * Generate category from content
     */
    public function generateCategory($content, $title = '') {
        $cleaned = $this->cleanContentText($content, 2000);
        $context = !empty($title) ? "Title: {$title}\n\n" : '';

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a content strategist. Select a concise 1-3 word high-level category for this blog post (e.g., Artificial Intelligence, Software Engineering, Cloud & DevOps, Web Development, Academic Research, Data Science, Tutorials). Return only the category name, nothing else.'
            ],
            [
                'role' => 'user',
                'content' => $context . "Content:\n\n" . $cleaned
            ]
        ];

        $category = trim($this->callOpenAI($messages, 0.4));
        return trim($category, " \t\n\r\0\x0B\"'");
    }

    /**
     * Generate excerpt / abstract from content (and optional title)
     */
    public function generateExcerpt($title, $content) {
        $cleaned = $this->cleanContentText($content, 3000);
        $titleContext = !empty($title) ? "Title: {$title}\n\n" : '';

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a professional editor. Generate a compelling 2-3 sentence executive abstract/excerpt for a blog post. Be concise, engaging, and informative. Do not include quotation marks or special formatting.'
            ],
            [
                'role' => 'user',
                'content' => $titleContext . "Content:\n\n" . $cleaned
            ]
        ];

        return trim($this->callOpenAI($messages, 0.7));
    }

    /**
     * Generate SEO title
     */
    public function generateSEOTitle($title, $content) {
        $cleaned = $this->cleanContentText($content, 2000);
        $titleContext = !empty($title) ? "Title: {$title}\n\n" : '';

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an SEO expert. Generate a compelling SEO title (50-60 characters) for a blog post. Include primary search keywords naturally. Do not use quotes.'
            ],
            [
                'role' => 'user',
                'content' => $titleContext . "Content:\n\n" . $cleaned
            ]
        ];

        return trim($this->callOpenAI($messages, 0.5));
    }

    /**
     * Generate SEO description/meta description
     */
    public function generateSEODescription($title, $content) {
        $cleaned = $this->cleanContentText($content, 2000);
        $titleContext = !empty($title) ? "Title: {$title}\n\n" : '';

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an SEO expert. Generate a concise meta description (140-160 characters) for a blog post. Be compelling and include relevant search keywords naturally. Do not use quotes.'
            ],
            [
                'role' => 'user',
                'content' => $titleContext . "Content:\n\n" . $cleaned
            ]
        ];

        return trim($this->callOpenAI($messages, 0.5));
    }

    /**
     * Generate tags/keywords
     */
    public function generateTags($title, $content, $category = '') {
        $cleaned = $this->cleanContentText($content, 2500);
        $context = '';
        if (!empty($title)) $context .= "Title: {$title}\n";
        if (!empty($category)) $context .= "Category: {$category}\n";
        if (!empty($context)) $context .= "\n";

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a content strategist. Generate 5-8 relevant, high-impact tags/keywords for a blog post. Return them as comma-separated values only, with no numbering, bullets, or quotes.'
            ],
            [
                'role' => 'user',
                'content' => $context . "Content:\n\n" . $cleaned
            ]
        ];

        return trim($this->callOpenAI($messages, 0.6));
    }

    /**
     * Calculate reading time and analyze content
     */
    public function analyzeContent($content) {
        $plainText = strip_tags((string)$content);
        $wordCount = str_word_count($plainText);
        $readingTimeMinutes = ceil($wordCount / 200); // Average reading speed: 200 words per minute

        return [
            'word_count' => $wordCount,
            'reading_time' => max(1, $readingTimeMinutes),
            'paragraph_count' => max(1, substr_count($plainText, "\n") + 1),
            'character_count' => strlen($plainText),
        ];
    }
}

?>
