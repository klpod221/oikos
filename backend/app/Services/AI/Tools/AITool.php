<?php

namespace App\Services\AI\Tools;

abstract class AITool
{
    /**
     * Get the unique name of the tool (e.g., 'create_transaction').
     */
    abstract public function name(): string;

    /**
     * Get the description of what the tool does.
     */
    abstract public function description(): string;

    /**
     * Get the JSON Schema for the tool's parameters.
     */
    abstract public function parameters(): array;

    /**
     * Execute the tool logic.
     *
     * @param array $arguments
     * @param int $userId
     * @return array{success: bool, result: string, data?: mixed}
     */
    abstract public function execute(array $arguments, int $userId): array;

    /**
     * Get list of other tools this tool may depend on / trigger.
     * Use this to inform the AI about tool relationships.
     *
     * @return array<string> List of tool names
     */
    public function dependsOn(): array
    {
        return [];
    }

    /**
     * Get the full tool definition for OpenAI.
     */
    public function getDefinition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => $this->description(),
                'parameters' => $this->parameters(),
            ],
        ];
    }
}
