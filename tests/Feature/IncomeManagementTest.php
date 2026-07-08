<?php

namespace Tests\Feature;

use App\Events\IncomeCreated;
use App\Models\Income;
use App\Models\IncomeSource;
use App\Models\User;
use App\Services\FinanceCalculationService;
use Database\Seeders\IncomeSourceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IncomeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_income_sources(): void
    {
        $this->seed(IncomeSourceSeeder::class);
        $user = User::factory()->create();
        IncomeSource::query()->create([
            'user_id' => $user->id,
            'name' => 'Consulting',
            'is_default' => false,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/income-sources')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonFragment(['name' => 'Salary'])
            ->assertJsonFragment(['name' => 'Consulting']);
    }

    public function test_default_sources_exist(): void
    {
        $this->seed(IncomeSourceSeeder::class);

        $this->assertDatabaseHas('income_sources', ['name' => 'Salary', 'is_default' => true]);
        $this->assertDatabaseHas('income_sources', ['name' => 'Freelance', 'is_default' => true]);
        $this->assertDatabaseHas('income_sources', ['name' => 'Business', 'is_default' => true]);
        $this->assertDatabaseHas('income_sources', ['name' => 'Investment', 'is_default' => true]);
        $this->assertDatabaseHas('income_sources', ['name' => 'Gift', 'is_default' => true]);
        $this->assertDatabaseHas('income_sources', ['name' => 'Other', 'is_default' => true]);
    }

    public function test_user_can_create_income(): void
    {
        Event::fake([IncomeCreated::class]);

        $user = User::factory()->create();
        $source = $this->source();

        Sanctum::actingAs($user);

        $this->postJson('/api/incomes', [
            'income_source_id' => $source->id,
            'amount' => 10000,
            'currency' => 'ETB',
            'income_date' => '2026-07-08',
            'description' => 'Monthly salary',
        ])->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Income created successfully')
            ->assertJsonPath('data.amount', '10000.00')
            ->assertJsonPath('data.asrat', '1000.00')
            ->assertJsonPath('data.remaining_after_asrat', '9000.00');

        $this->assertDatabaseHas('incomes', [
            'user_id' => $user->id,
            'income_source_id' => $source->id,
            'amount' => 10000,
            'currency' => 'ETB',
        ]);

        Event::assertDispatched(IncomeCreated::class);
    }

    public function test_user_can_update_income(): void
    {
        $user = User::factory()->create();
        $source = $this->source();
        $income = $this->income($user, $source, 10000);

        Sanctum::actingAs($user);

        $this->putJson('/api/incomes/' . $income->id, [
            'amount' => 12000,
            'income_date' => '2026-07-09',
            'description' => 'Updated salary',
        ])->assertOk()
            ->assertJsonPath('message', 'Income updated successfully')
            ->assertJsonPath('data.amount', '12000.00')
            ->assertJsonPath('data.asrat', '1200.00')
            ->assertJsonPath('data.remaining_after_asrat', '10800.00');
    }

    public function test_user_can_delete_income(): void
    {
        $user = User::factory()->create();
        $income = $this->income($user, $this->source(), 10000);

        Sanctum::actingAs($user);

        $this->deleteJson('/api/incomes/' . $income->id)
            ->assertOk()
            ->assertJsonPath('message', 'Income deleted successfully');

        $this->assertSoftDeleted('incomes', ['id' => $income->id]);
    }

    public function test_user_only_accesses_own_income(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $source = $this->source();
        $income = $this->income($otherUser, $source, 50000);

        Sanctum::actingAs($user);

        $this->getJson('/api/incomes')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('summary.total_income', '0.00');

        $this->putJson('/api/incomes/' . $income->id, [
            'amount' => 100,
        ])->assertForbidden();

        $this->deleteJson('/api/incomes/' . $income->id)
            ->assertForbidden();
    }

    public function test_amount_required(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/incomes', [
            'income_source_id' => $this->source()->id,
            'income_date' => '2026-07-08',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_invalid_source_rejected(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/incomes', [
            'income_source_id' => (string) \Illuminate\Support\Str::uuid(),
            'amount' => 10000,
            'income_date' => '2026-07-08',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['income_source_id']);
    }

    public function test_invalid_date_rejected(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/incomes', [
            'income_source_id' => $this->source()->id,
            'amount' => 10000,
            'income_date' => 'not-a-date',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['income_date']);
    }

    public function test_income_history_filters_and_summary(): void
    {
        $user = User::factory()->create();
        $salary = $this->source('Salary');
        $gift = $this->source('Gift');

        $this->income($user, $salary, 10000, '2026-07-08');
        $this->income($user, $gift, 500, '2026-07-09');

        Sanctum::actingAs($user);

        $this->getJson('/api/incomes?source_id=' . $salary->id . '&start_date=2026-07-01&end_date=2026-07-31')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.income_source.name', 'Salary')
            ->assertJsonPath('summary.total_income', '10500.00')
            ->assertJsonPath('summary.asrat', '1050.00')
            ->assertJsonPath('summary.remaining_after_asrat', '9450.00');
    }

    public function test_asrat_calculation_is_correct(): void
    {
        $calculation = app(FinanceCalculationService::class)->calculateAsrat(10000);

        $this->assertSame('1000.00', $calculation['asrat']);
    }

    public function test_remaining_balance_calculated_correctly(): void
    {
        $calculation = app(FinanceCalculationService::class)->calculateAsrat(10000);

        $this->assertSame('9000.00', $calculation['remaining']);
    }

    private function source(string $name = 'Salary'): IncomeSource
    {
        return IncomeSource::query()->create([
            'user_id' => null,
            'name' => $name,
            'is_default' => true,
        ]);
    }

    private function income(User $user, IncomeSource $source, int $amount, string $date = '2026-07-08'): Income
    {
        return Income::query()->create([
            'user_id' => $user->id,
            'income_source_id' => $source->id,
            'amount' => $amount,
            'currency' => 'ETB',
            'income_date' => $date,
            'description' => null,
        ]);
    }
}
