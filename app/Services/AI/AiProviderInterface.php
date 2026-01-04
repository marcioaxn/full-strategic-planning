<?php

namespace App\Services\AI;

interface AiProviderInterface
{
    /**
     * Get suggestions from the AI provider.
     */
    public function suggest(string $prompt, string $context = ''): ?string;

    /**
     * Test if the connection with the provider is working.
     */
    public function testConnection(): array;

    /**
     * Analyze if a text follows SMART criteria and provide feedback.
     */
    public function analyzeSmart(string $type, string $title, string $description = ''): ?string;

    /**
     * Generate an executive summary based on dashboard statistics.
     */
    public function summarizeStrategy(array $stats, string $orgName): ?string;
}
