<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class ResourceApiController extends Controller
{
    public function bankAccounts(): JsonResponse
    {
        return response()->json(
            BankAccount::orderBy('name')->get()->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'type' => $b->type,
                'type_label' => $b->typeLabel(),
                'current_balance' => (float) $b->current_balance,
                'opening_balance' => (float) $b->opening_balance,
                'currency' => $b->currency,
                'account_number' => $b->account_number,
                'is_active' => (bool) $b->is_active,
            ])
        );
    }

    public function projects(): JsonResponse
    {
        return response()->json(
            Project::orderBy('name')->get()->map(fn ($p) => [
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'client_name' => $p->client_name,
                'contract_value' => (float) $p->contract_value,
                'status' => $p->status,
                'status_label' => $p->statusLabel(),
                'start_date' => $p->start_date?->toDateString(),
                'end_date' => $p->end_date?->toDateString(),
                'total_cost' => $p->totalCost(),
                'total_revenue' => $p->totalRevenue(),
                'profit' => $p->profit(),
                'profit_margin' => $p->profitMargin(),
            ])
        );
    }

    public function categories(): JsonResponse
    {
        return response()->json(
            Category::where('is_active', true)->orderBy('name')->get()->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => $c->type,
                'color' => $c->color,
            ])
        );
    }

    public function chartOfAccounts(): JsonResponse
    {
        return response()->json(
            ChartOfAccount::with('children')->whereNull('parent_id')->orderBy('code')->get()->map(function ($root) {
                return $this->transformAccount($root);
            })
        );
    }

    protected function transformAccount(ChartOfAccount $account): array
    {
        return [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'type' => $account->type,
            'type_label' => $account->typeLabel(),
            'level' => $account->level,
            'children' => $account->children->map(fn ($c) => $this->transformAccount($c)),
        ];
    }
}
