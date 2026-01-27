<?php

namespace App\Services\AI;

use App\Services\AI\Tools\AITool;
// Finance Tools
use App\Services\AI\Tools\GetCategoriesTool;
use App\Services\AI\Tools\CreateTransactionTool;
use App\Services\AI\Tools\GetTransactionsTool;
use App\Services\AI\Tools\UpdateTransactionTool;
use App\Services\AI\Tools\DeleteTransactionTool;
use App\Services\AI\Tools\GetWalletsTool;
use App\Services\AI\Tools\CreateWalletTool;
use App\Services\AI\Tools\GetFinancialSummaryTool;
use App\Services\AI\Tools\GetSavingsGoalsTool;
use App\Services\AI\Tools\CreateSavingsGoalTool;
use App\Services\AI\Tools\UpdateSavingsGoalTool;
// Nutrition Tools
use App\Services\AI\Tools\GetIngredientsTool;
use App\Services\AI\Tools\GetRecipesTool;
use App\Services\AI\Tools\GetMealPlansTool;
use App\Services\AI\Tools\CreateMealPlanTool;
use App\Services\AI\Tools\LogNutritionTool;
use App\Services\AI\Tools\GetNutritionSummaryTool;
use App\Services\AI\Tools\GetShoppingListTool;
use App\Services\AI\Tools\GenerateShoppingListTool;
// Workout Tools
use App\Services\AI\Tools\GetExercisesTool;
use App\Services\AI\Tools\GetRoutinesTool;
use App\Services\AI\Tools\GetWorkoutScheduleTool;
use App\Services\AI\Tools\LogWorkoutTool;
use App\Services\AI\Tools\GetWorkoutSummaryTool;
// User Tools
use App\Services\AI\Tools\GetDailySummaryTool;
use App\Services\AI\Tools\GetUserGoalsTool;
use App\Services\AI\Tools\GetUserStatsTool;
use App\Services\AI\Tools\UpdateUserStatsTool;
use App\Services\AI\Tools\GetWeeklyReportTool;
// Services
use App\Services\Finance\TransactionService;
use Illuminate\Support\Facades\Log;

/**
 * Executor for AI function calling (tools).
 * Acts as a registry for all available tools.
 */
class ToolExecutor
{
    /** @var array<string, AITool> */
    private array $tools = [];

    public function __construct(TransactionService $transactionService)
    {
        // Finance Tools
        $this->register(new GetCategoriesTool());
        $this->register(new CreateTransactionTool($transactionService));
        $this->register(new GetTransactionsTool());
        $this->register(new UpdateTransactionTool());
        $this->register(new DeleteTransactionTool($transactionService));
        $this->register(new GetWalletsTool());
        $this->register(new CreateWalletTool());
        $this->register(new GetFinancialSummaryTool());
        $this->register(new GetSavingsGoalsTool());
        $this->register(new CreateSavingsGoalTool());
        $this->register(new UpdateSavingsGoalTool());

        // Nutrition Tools
        $this->register(new GetIngredientsTool());
        $this->register(new GetRecipesTool());
        $this->register(new GetMealPlansTool());
        $this->register(new CreateMealPlanTool());
        $this->register(new LogNutritionTool());
        $this->register(new GetNutritionSummaryTool());
        $this->register(new GetShoppingListTool());
        $this->register(new GenerateShoppingListTool());

        // Workout Tools
        $this->register(new GetExercisesTool());
        $this->register(new GetRoutinesTool());
        $this->register(new GetWorkoutScheduleTool());
        $this->register(new LogWorkoutTool());
        $this->register(new GetWorkoutSummaryTool());

        // User & Summary Tools
        $this->register(new GetDailySummaryTool());
        $this->register(new GetUserGoalsTool());
        $this->register(new GetUserStatsTool());
        $this->register(new UpdateUserStatsTool());
        $this->register(new GetWeeklyReportTool());
    }

    /**
     * Register a tool.
     */
    public function register(AITool $tool): void
    {
        $this->tools[$tool->name()] = $tool;
    }

    /**
     * Get available tools definition for OpenAI API.
     *
     * @return array
     */
    public function getToolsDefinition(): array
    {
        $definitions = [];
        foreach ($this->tools as $tool) {
            $def = $tool->getDefinition();
            $dependencies = $tool->dependsOn();

            if (!empty($dependencies)) {
                $depString = implode(', ', $dependencies);
                $def['function']['description'] .= " (Relies on tools/functions: {$depString})";
            }

            $definitions[] = $def;
        }
        return $definitions;
    }

    /**
     * Execute a tool call.
     *
     * @param string $toolName
     * @param array $arguments
     * @param int $userId
     * @return array{success: bool, result: string, data?: mixed}
     */
    public function execute(string $toolName, array $arguments, int $userId): array
    {
        Log::info('Executing tool', [
            'tool' => $toolName,
            'arguments' => $arguments,
            'user_id' => $userId,
        ]);

        if (!isset($this->tools[$toolName])) {
            return [
                'success' => false,
                'result' => "Unknown tool: {$toolName}",
            ];
        }

        return $this->tools[$toolName]->execute($arguments, $userId);
    }

    /**
     * Parse tool calls from OpenAI response.
     *
     * @param array $toolCalls
     * @return array<array{id: string, name: string, arguments: array}>
     */
    public function parseToolCalls(array $toolCalls): array
    {
        $parsed = [];

        foreach ($toolCalls as $toolCall) {
            $function = $toolCall['function'] ?? [];
            $arguments = $function['arguments'] ?? '{}';

            // Arguments come as JSON string
            if (is_string($arguments)) {
                $arguments = json_decode($arguments, true) ?? [];
            }

            $parsed[] = [
                'id' => $toolCall['id'] ?? uniqid('tool_'),
                'name' => $function['name'] ?? '',
                'arguments' => $arguments,
            ];
        }

        return $parsed;
    }
}
