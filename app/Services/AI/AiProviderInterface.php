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
}
